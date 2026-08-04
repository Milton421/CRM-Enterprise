<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$inputJSON = json_decode(file_get_contents('php://input'), true);
if (!is_array($inputJSON)) {
    $inputJSON = $_POST;
}

switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        $overrideMethod = strtoupper(trim($inputJSON['_method'] ?? ''));
        if ($overrideMethod === 'DELETE') {
            handleDelete($pdo, $inputJSON);
        } elseif ($overrideMethod === 'PUT' || (!empty($inputJSON['id']) && (int)$inputJSON['id'] > 0)) {
            handlePut($pdo, $inputJSON);
        } else {
            handlePost($pdo, $inputJSON);
        }
        break;
    case 'PUT':
        handlePut($pdo, $inputJSON);
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
    $search = trim($_GET['q'] ?? '');
    $status = trim($_GET['status'] ?? 'all');
    $industry = trim($_GET['industry'] ?? 'all');
    $sort = trim($_GET['sort'] ?? 'date_desc');

    $whereClauses = [];
    $params = [];

    if ($search !== '') {
        $whereClauses[] = "(name LIKE :q1 OR company LIKE :q2 OR email LIKE :q3 OR phone LIKE :q4 OR position LIKE :q5)";
        $searchVal = '%' . $search . '%';
        $params['q1'] = $searchVal;
        $params['q2'] = $searchVal;
        $params['q3'] = $searchVal;
        $params['q4'] = $searchVal;
        $params['q5'] = $searchVal;
    }

    if ($status !== 'all' && in_array($status, ['lead', 'prospect', 'active', 'inactive'])) {
        $whereClauses[] = "status = :status";
        $params['status'] = $status;
    }

    if ($industry !== 'all' && $industry !== '') {
        $whereClauses[] = "industry = :industry";
        $params['industry'] = $industry;
    }

    $whereSQL = '';
    if (count($whereClauses) > 0) {
        $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    $sortMap = [
        'date_desc' => 'c.created_at DESC',
        'date_asc'  => 'c.created_at ASC',
        'name_asc'  => 'c.name ASC',
        'name_desc' => 'c.name DESC',
        'value_desc'=> 'c.opportunity_value DESC',
        'value_asc' => 'c.opportunity_value ASC',
        'contact_desc' => 'c.last_contact_at DESC'
    ];
    $orderBy = $sortMap[$sort] ?? 'c.created_at DESC';

    $sql = "SELECT c.*, 
                   COUNT(i.id) as interaction_count
            FROM clients c
            LEFT JOIN interactions i ON c.id = i.client_id
            $whereSQL
            GROUP BY c.id
            ORDER BY $orderBy";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $clients = $stmt->fetchAll();

    $indStmt = $pdo->query("SELECT DISTINCT industry FROM clients WHERE industry IS NOT NULL AND industry != '' ORDER BY industry ASC");
    $availableIndustries = $indStmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'count' => count($clients),
        'data' => $clients,
        'available_industries' => $availableIndustries
    ], JSON_UNESCAPED_UNICODE);
}

function handlePost($pdo, $data) {
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $company = trim($data['company'] ?? '');
    $position = trim($data['position'] ?? '');
    $status = trim($data['status'] ?? 'lead');
    $value = (float)($data['opportunity_value'] ?? 0);
    $industry = trim($data['industry'] ?? '');
    $address = trim($data['address'] ?? '');
    $notes = trim($data['notes'] ?? '');

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El nombre del cliente es obligatorio']);
        return;
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Formato de correo electrónico no válido']);
        return;
    }
    if (!empty($phone)) {
        $digitsOnly = preg_replace('/\D/', '', $phone);
        if (strlen($digitsOnly) < 7 || strlen($digitsOnly) > 15) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Formato de número de teléfono/celular no válido']);
            return;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, company, position, status, opportunity_value, industry, address, notes, created_at) VALUES (:name, :email, :phone, :company, :position, :status, :value, :industry, :address, :notes, NOW())");
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'company' => $company,
        'position' => $position,
        'status' => $status,
        'value' => $value,
        'industry' => $industry,
        'address' => $address,
        'notes' => $notes
    ]);

    $newId = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Cliente registrado exitosamente']);
}

function handlePut($pdo, $data) {
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de cliente no válido']);
        return;
    }

    if (!empty($data['status_only']) || (isset($data['status']) && !isset($data['name']))) {
        $status = trim($data['status'] ?? 'lead');
        $stmt = $pdo->prepare("UPDATE clients SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Etapa de venta actualizada']);
        return;
    }

    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $company = trim($data['company'] ?? '');
    $position = trim($data['position'] ?? '');
    $status = trim($data['status'] ?? 'lead');
    $value = (float)($data['opportunity_value'] ?? 0);
    $industry = trim($data['industry'] ?? '');
    $address = trim($data['address'] ?? '');
    $notes = trim($data['notes'] ?? '');

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El nombre del cliente es obligatorio']);
        return;
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Formato de correo electrónico no válido']);
        return;
    }
    if (!empty($phone)) {
        $digitsOnly = preg_replace('/\D/', '', $phone);
        if (strlen($digitsOnly) < 7 || strlen($digitsOnly) > 15) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Formato de número de teléfono/celular no válido']);
            return;
        }
    }

    $stmt = $pdo->prepare("UPDATE clients SET name = :name, email = :email, phone = :phone, company = :company, position = :position, status = :status, opportunity_value = :value, industry = :industry, address = :address, notes = :notes WHERE id = :id");
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'company' => $company,
        'position' => $position,
        'status' => $status,
        'value' => $value,
        'industry' => $industry,
        'address' => $address,
        'notes' => $notes,
        'id' => $id
    ]);

    echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente']);
}

function handleDelete($pdo, $data) {
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de cliente no válido']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = :id");
    $stmt->execute(['id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Cliente eliminado exitosamente']);
}
