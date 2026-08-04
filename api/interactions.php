<?php


header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$inputJSON = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        handlePost($pdo, $inputJSON);
        break;
    case 'DELETE':
        handleDelete($pdo, $inputJSON);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        break;
}

function handleGet($pdo) {
    $clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;

    if ($clientId > 0) {
        $stmt = $pdo->prepare("SELECT i.*, c.name as client_name, c.company as client_company 
                               FROM interactions i 
                               JOIN clients c ON i.client_id = c.id 
                               WHERE i.client_id = :client_id 
                               ORDER BY i.interaction_date DESC");
        $stmt->execute(['client_id' => $clientId]);
    } else {
        $stmt = $pdo->query("SELECT i.*, c.name as client_name, c.company as client_company 
                             FROM interactions i 
                             JOIN clients c ON i.client_id = c.id 
                             ORDER BY i.interaction_date DESC 
                             LIMIT 50");
    }

    $interactions = $stmt->fetchAll();
    echo json_encode(['success' => true, 'count' => count($interactions), 'data' => $interactions], JSON_UNESCAPED_UNICODE);
}

function handlePost($pdo, $data) {
    $clientId = (int)($data['client_id'] ?? 0);
    $type = trim($data['type'] ?? 'note');
    $subject = trim($data['subject'] ?? '');
    $description = trim($data['description'] ?? '');
    $userName = trim($data['user_name'] ?? 'Carlos Castillo');

    if ($clientId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de cliente requerido']);
        return;
    }

    $rawDate = trim($data['next_followup_date'] ?? '');
    $nextFollowup = null;
    if (!empty($rawDate)) {
        $nextFollowup = str_replace('T', ' ', $rawDate);
        if (strlen($nextFollowup) == 16) {
            $nextFollowup .= ':00';
        }
    }

    if (empty($subject)) {
        $typeLabels = [
            'call' => 'Llamada comercial realizada',
            'meeting' => 'Reunión de seguimiento',
            'email' => 'Envío de información / correo',
            'note' => 'Nota de seguimiento',
            'task' => 'Tarea de seguimiento agendada'
        ];
        $subject = $typeLabels[$type] ?? 'Seguimiento comercial';
    }

    $validTypes = ['call', 'meeting', 'email', 'note', 'task'];
    if (!in_array($type, $validTypes)) {
        $type = 'note';
    }

    $stmt = $pdo->prepare("INSERT INTO interactions (client_id, type, subject, description, interaction_date, next_followup_date, user_name, created_at) VALUES (:client_id, :type, :subject, :description, NOW(), :next_followup, :user_name, NOW())");
    $stmt->execute([
        'client_id' => $clientId,
        'type' => $type,
        'subject' => $subject,
        'description' => $description,
        'next_followup' => $nextFollowup,
        'user_name' => $userName
    ]);

    $updStmt = $pdo->prepare("UPDATE clients SET last_contact_at = NOW() WHERE id = :id");
    $updStmt->execute(['id' => $clientId]);

    $newId = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Interacción registrada en la bitácora']);
}

function handleDelete($pdo, $data) {
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de interacción no válido']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM interactions WHERE id = :id");
    $stmt->execute(['id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Interacción eliminada']);
}
