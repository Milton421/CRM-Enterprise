<?php

require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();

$q        = trim($_GET['q']        ?? '');
$status   = trim($_GET['status']   ?? 'all');
$industry = trim($_GET['industry'] ?? 'all');

// Filtros
$conditions = [];
$params     = [];

if (!empty($q)) {
    $conditions[] = "(name LIKE :q1 OR email LIKE :q2 OR company LIKE :q3 OR phone LIKE :q4 OR position LIKE :q5)";
    $searchVal = "%$q%";
    $params[':q1'] = $searchVal;
    $params[':q2'] = $searchVal;
    $params[':q3'] = $searchVal;
    $params[':q4'] = $searchVal;
    $params[':q5'] = $searchVal;
}
if ($status !== 'all' && !empty($status)) {
    $conditions[] = "status = :status";
    $params[':status'] = $status;
}
if ($industry !== 'all' && !empty($industry)) {
    $conditions[] = "industry = :industry";
    $params[':industry'] = $industry;
}

$whereSQL = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Clientes 
$sql = "SELECT c.*,
            (SELECT COUNT(*) FROM interactions i WHERE i.client_id = c.id) AS total_interacciones
        FROM clients c {$whereSQL}
        ORDER BY c.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll();

// Estadísticas 
$sqlStats = "SELECT
    COUNT(*)                                                      AS total,
    COALESCE(SUM(opportunity_value), 0)                           AS pipeline,
    SUM(CASE WHEN status = 'lead'     THEN 1 ELSE 0 END)          AS leads,
    SUM(CASE WHEN status = 'prospect' THEN 1 ELSE 0 END)          AS prospectos,
    SUM(CASE WHEN status = 'active'   THEN 1 ELSE 0 END)          AS activos,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END)          AS inactivos
FROM clients {$whereSQL}";

$stmtS = $pdo->prepare($sqlStats);
$stmtS->execute($params);
$stats = $stmtS->fetch();

$statusNames = [
    'lead'     => 'Lead',
    'prospect' => 'Prospecto',
    'active'   => 'Cliente Activo',
    'inactive' => 'Inactivo',
];

$fmtDate = function (?string $d): string {
    if (!$d) return 'Sin contacto';
    return date('d/m/Y H:i', strtotime($d));
};

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://fonts.gstatic.com data: blob:;">
    <title>Informe Ejecutivo - CRM Enterprise</title>
    <link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,400;0,6..12,500;0,6..12,600;0,6..12,700;0,6..12,800;0,6..12,900;1,6..12,400&display=swap" rel="stylesheet">
    <style>
        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Nunito Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; -webkit-font-smoothing: antialiased; }
        
        body { 
            background: #f8fafc; 
            color: #0f172a; 
            padding: 24px 16px; 
            font-size: 0.875rem; 
            line-height: 1.5; 
            display: flex;
            justify-content: center;
        }
        
        .report-card { 
            background: #ffffff; 
            border: 1px solid #e2e8f0; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06); 
            padding: 32px; 
            width: 100%; 
            max-width: 1140px; 
            margin: 0 auto; 
            box-sizing: border-box;
        }

        .header { 
            border-bottom: 1px solid #e2e8f0; 
            padding-bottom: 20px; 
            margin-bottom: 24px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            flex-wrap: wrap;
            gap: 16px;
        }

        .brand-box { display: flex; align-items: center; gap: 12px; }
        .brand-logo { width: 38px; height: 38px; background: linear-gradient(135deg, #6366f1, #7c3aed); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; color: #ffffff; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35); }
        .title h1 { font-size: 1.45rem; font-weight: 700; color: #0f172a; letter-spacing: -0.4px; }
        .title p { color: #64748b; font-size: 0.83rem; margin-top: 2px; }

        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; width: 100%; }
        
        .kpi-box { padding: 18px 20px; border-radius: 16px; min-width: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; background: #ffffff; }
        .kpi-box:nth-child(1) { background: linear-gradient(135deg, #ffffff, #fffbeb); border-color: #fef3c7; }
        .kpi-box:nth-child(2) { background: linear-gradient(135deg, #ffffff, #f0fdf4); border-color: #d1fae5; }
        .kpi-box:nth-child(3) { background: linear-gradient(135deg, #ffffff, #fff1f2); border-color: #ffe4e6; }
        .kpi-box:nth-child(4) { background: linear-gradient(135deg, #ffffff, #f5f3ff); border-color: #ede9fe; }

        .kpi-lbl { font-size: 0.73rem; text-transform: uppercase; color: #64748b; font-weight: 600; letter-spacing: 0.06em; margin-bottom: 6px; }
        .kpi-val { font-size: 1.65rem; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums; }

        .section-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; }
        
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
        }

        .custom-table { width: 100%; min-width: 800px; border-collapse: collapse; text-align: left; font-size: 0.84rem; }
        .custom-table th { background: #f8fafc; color: #475569; padding: 12px 16px; font-weight: 600; text-transform: uppercase; font-size: 0.68rem; letter-spacing: 0.06em; border-bottom: 1px solid #e2e8f0; }
        .custom-table td { padding: 13px 16px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .custom-table tbody tr { transition: background 0.1s ease; }
        .custom-table tbody tr:hover { background: #f8fafc; }
        .custom-table tr:last-child td { border-bottom: none; }

        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 9999px; font-size: 0.73rem; font-weight: 600; white-space: nowrap; }
        .badge-lead { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
        .badge-prospect { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .badge-active { background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-inactive { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

        .tfoot-row { background: #f8fafc; font-weight: 700; }
        .tfoot-row td { padding: 15px 16px; color: #0f172a; font-size: 0.9rem; }

        .btn-print { background: linear-gradient(135deg, #6366f1, #7c3aed); color: #ffffff; border: 1px solid #6366f1; padding: 9px 18px; border-radius: 12px; cursor: pointer; font-weight: 600; font-size: 0.83rem; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35); }
        .btn-print:hover { background: linear-gradient(135deg, #4f46e5, #6d28d9); }

        @media print {
            @page {
                size: landscape;
                margin: 6mm;
            }
            html, body {
                background: #ffffff !important;
                color: #0f172a !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .report-card {
                background: #ffffff !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .no-print { display: none !important; }

            .header {
                border-bottom: 2px solid #0f172a !important;
                padding-bottom: 12px !important;
                margin-bottom: 14px !important;
            }
            .title h1 { color: #0f172a !important; font-size: 1.3rem !important; }
            .title p { color: #475569 !important; font-size: 0.75rem !important; }

            .kpi-grid {
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 10px !important;
                margin-bottom: 14px !important;
            }
            .kpi-box {
                background: #f8fafc !important;
                border: 1px solid #cbd5e1 !important;
                padding: 8px 12px !important;
                border-radius: 8px !important;
            }
            .kpi-lbl { color: #475569 !important; font-size: 0.65rem !important; }
            .kpi-val { color: #0f172a !important; font-size: 1.25rem !important; }

            .table-responsive {
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                overflow: visible !important;
                width: 100% !important;
            }

            .custom-table {
                width: 100% !important;
                min-width: 100% !important;
                table-layout: fixed !important;
                font-size: 0.75rem !important;
            }

            .custom-table th {
                background: #0f172a !important;
                color: #ffffff !important;
                padding: 6px 8px !important;
                font-size: 0.65rem !important;
            }
            .custom-table td {
                padding: 6px 8px !important;
                border-bottom: 1px solid #e2e8f0 !important;
                color: #0f172a !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
            }

            .badge { padding: 1px 6px !important; font-size: 0.68rem !important; border-radius: 4px !important; }
            .badge-lead { background: #e0f2fe !important; color: #0284c7 !important; border: 1px solid #bae6fd !important; }
            .badge-prospect { background: #fef3c7 !important; color: #d97706 !important; border: 1px solid #fde68a !important; }
            .badge-active { background: #d1fae5 !important; color: #059669 !important; border: 1px solid #a7f3d0 !important; }
            .badge-inactive { background: #f1f5f9 !important; color: #64748b !important; border: 1px solid #e2e8f0 !important; }

            .tfoot-row { background: #f1f5f9 !important; }
            .tfoot-row td { color: #0f172a !important; padding: 8px !important; font-size: 0.8rem !important; }
        }

        @media (max-width: 640px) {
            body { padding: 12px 8px; }
            .report-card { padding: 16px; border-radius: 12px; }
            .header { flex-direction: column; align-items: flex-start; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<div class="report-card">
    <div class="header">
        <div class="brand-box">
            <div class="brand-logo">C</div>
            <div class="title">
                <h1>Informe Ejecutivo de Clientes &amp; Pipeline</h1>
                <p>CRM Enterprise &nbsp;·&nbsp; Generado el <?= date('d/m/Y H:i') ?></p>
            </div>
        </div>
        <div class="no-print">
            <button onclick="window.print()" class="btn-print">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimir / Guardar PDF
            </button>
        </div>
    </div>
}
    <div class="kpi-grid">
        <div class="kpi-box">
            <div class="kpi-lbl">Total Clientes</div>
            <div class="kpi-val"><?= (int)$stats['total'] ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-lbl">Valor Pipeline</div>
            <div class="kpi-val" style="color:#059669;">Q<?= number_format((float)$stats['pipeline'], 2) ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-lbl">Leads &amp; Prospectos</div>
            <div class="kpi-val" style="color:#e11d48;"><?= (int)$stats['leads'] + (int)$stats['prospectos'] ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-lbl">Clientes Activos</div>
            <div class="kpi-val" style="color:#7c3aed;"><?= (int)$stats['activos'] ?></div>
        </div>
    </div>

    <div class="section-title">
        <span>Detalle de Clientes (<?= count($clients) ?>)</span>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <colgroup>
                <col style="width:4%;">
                <col style="width:16%;">
                <col style="width:20%;">
                <col style="width:13%;">
                <col style="width:17%;">
                <col style="width:10%;">
                <col style="width:10%;">
                <col style="width:10%;">
            </colgroup>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Correo Electrónico</th>
                    <th>Teléfono</th>
                    <th>Empresa / Puesto</th>
                    <th>Estado</th>
                    <th style="text-align:right;">Valor</th>
                    <th style="text-align:center;">Contacto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;padding:30px;color:#64748b;">No hay clientes registrados con los filtros seleccionados.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($clients as $i => $c): ?>
                <tr>
                    <td style="color:#64748b;"><?= $i + 1 ?></td>
                    <td>
                        <strong style="color:#0f172a;display:block;"><?= htmlspecialchars($c['name']) ?></strong>
                        <span style="font-size:0.75rem;color:#64748b;"><?= htmlspecialchars($c['industry'] ?: 'General') ?></span>
                    </td>
                    <td style="word-break:break-all;"><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= htmlspecialchars($c['phone'] ?: '—') ?></td>
                    <td>
                        <div><?= htmlspecialchars($c['company'] ?: 'Particular') ?></div>
                        <div style="font-size:0.75rem;color:#64748b;"><?= htmlspecialchars($c['position'] ?: '—') ?></div>
                    </td>
                    <td><span class="badge badge-<?= $c['status'] ?>"><?= $statusNames[$c['status']] ?? $c['status'] ?></span></td>
                    <td style="text-align:right;font-weight:700;color:#0f172a;">Q<?= number_format((float)$c['opportunity_value'], 2) ?></td>
                    <td style="text-align:center;"><?= $fmtDate($c['last_contact_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="tfoot-row">
                    <td colspan="6" style="text-align:right;">TOTAL PIPELINE:</td>
                    <td style="text-align:right;color:#059669;">Q<?= number_format((float)$stats['pipeline'], 2) ?></td>
                    <td style="text-align:center;color:#64748b;font-size:0.75rem;font-weight:400;"><?= count($clients) ?> registros exportados</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
