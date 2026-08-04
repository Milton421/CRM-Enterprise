<?php


header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

// Métricas de clientes por estado
$statusStmt = $pdo->query("SELECT status, COUNT(*) as count, SUM(opportunity_value) as total_val FROM clients GROUP BY status");
$statusData = $statusStmt->fetchAll();

$statusCounts = [
    'lead' => 0,
    'prospect' => 0,
    'active' => 0,
    'inactive' => 0
];
$totalClients = 0;
$totalPipeline = 0;

foreach ($statusData as $row) {
    $st = $row['status'];
    $cnt = (int)$row['count'];
    $val = (float)$row['total_val'];
    
    if (isset($statusCounts[$st])) {
        $statusCounts[$st] = $cnt;
    }
    $totalClients += $cnt;
    $totalPipeline += $val;
}

// Clientes activos 
$activeClients = $statusCounts['active'];
$conversionRate = $totalClients > 0 ? round(($activeClients / $totalClients) * 100, 1) : 0;
$avgDealSize = $activeClients > 0 ? round($totalPipeline / $activeClients, 2) : 0;

// Distribucion por industria
$indStmt = $pdo->query("SELECT industry, COUNT(*) as count, SUM(opportunity_value) as total_val FROM clients WHERE industry IS NOT NULL AND industry != '' GROUP BY industry ORDER BY total_val DESC");
$industryData = $indStmt->fetchAll();

$industryCounts = [];
foreach ($industryData as $row) {
    $industryCounts[] = [
        'name' => $row['industry'],
        'count' => (int)$row['count'],
        'value' => (float)$row['total_val']
    ];
}

// Actividad colaborativa del equipo por tipo
$typeStmt = $pdo->query("SELECT type, COUNT(*) as count FROM interactions GROUP BY type");
$typeData = $typeStmt->fetchAll();

$typeCounts = [
    'call' => 0,
    'meeting' => 0,
    'email' => 0,
    'note' => 0,
    'task' => 0
];
foreach ($typeData as $row) {
    $tp = $row['type'];
    if (isset($typeCounts[$tp])) {
        $typeCounts[$tp] = (int)$row['count'];
    }
}

// Seguimientos
$followupStmt = $pdo->query("SELECT i.*, c.name as client_name, c.company as client_company 
                             FROM interactions i 
                             JOIN clients c ON i.client_id = c.id 
                             WHERE i.next_followup_date IS NOT NULL 
                               AND i.next_followup_date >= NOW() 
                             ORDER BY i.next_followup_date ASC 
                             LIMIT 5");
$upcomingFollowups = $followupStmt->fetchAll();

echo json_encode([
    'success' => true,
    'stats' => [
        'total_clients' => $totalClients,
        'active_leads' => $statusCounts['lead'] + $statusCounts['prospect'],
        'active_clients' => $activeClients,
        'total_pipeline' => $totalPipeline,
        'avg_deal_size' => $avgDealSize,
        'conversion_rate' => $conversionRate,
        'status_counts' => $statusCounts,
        'type_counts' => $typeCounts,
        'industry_counts' => $industryCounts,
        'upcoming_followups' => $upcomingFollowups
    ]
], JSON_UNESCAPED_UNICODE);
