<?php
require_once __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com data: blob:;">
    <meta name="description" content="Plataforma de Gestión de Clientes, Analítica Comercial y Colaboración de Equipo.">
    <title>CRM Enterprise</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,400;0,6..12,500;0,6..12,600;0,6..12,700;0,6..12,800;0,6..12,900;1,6..12,400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>

<div class="app-container">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-left" id="brandLogoBtn" title="CRM Enterprise">
                <div class="brand-logo">C</div>
                <span class="brand-title">CRM Enterprise</span>
            </div>
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Contraer / Expandir Menú Lateral">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
        </div>

        <ul class="nav-menu" id="sidebarMenu">
            <li class="nav-item active" data-target="view-dashboard">
                <a href="#dashboard">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard BI</span>
                </a>
            </li>
            <li class="nav-item" data-target="view-clientes">
                <a href="#clientes">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 1 0 7.75"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Gestión de Clientes</span>
                </a>
            </li>
            <li class="nav-item" data-target="view-seguimientos">
                <a href="#seguimientos">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span>Seguimientos &amp; Equipo</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-user">
            <div class="user-avatar">CC</div>
            <div class="user-info">
                <span class="user-name">Carlos Castillo</span>
                <span class="user-role">Administrador BI</span>
            </div>
        </div>
    </aside>

    <main class="main-content">

        <div id="dbBannerAlert" style="display:none;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#ef4444;padding:12px 18px;border-radius:var(--radius-lg);align-items:center;justify-content:space-between;gap:16px;">
            <div style="display:flex;align-items:center;gap:10px;font-size:0.85rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                La base de datos no está inicializada. Ejecute setup.php primero.
            </div>
            <a href="setup.php" target="_blank" class="btn btn-danger" style="text-decoration:none;flex-shrink:0;">Ir a Setup</a>
        </div>

        <!-- DASHBOARD BI-->
        <section class="view-section active" id="view-dashboard">
            <header class="header">
                <div class="header-title">
                    <h1>Dashboard de Ventas &amp; Clientes BI</h1>
                    <p>Control de prospectos, métricas comerciales y analítica ejecutiva del negocio.</p>
                </div>
                <div class="header-actions">
                    <a href="api/export.php" target="_blank" class="btn btn-secondary" title="Ver e imprimir informe ejecutivo formateado">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Informe HTML / PDF
                    </a>
                    <button class="btn btn-primary btnOpenNewClient">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Nuevo Cliente
                    </button>
                </div>
            </header>

            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-header">
                        <span class="metric-label">Total Clientes</span>
                        <div class="metric-icon-box"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
                    </div>
                    <div class="metric-value" id="metricTotalClients">0</div>
                    <div class="metric-sub-bar">
                        <div class="metric-sub-text">Directorio general</div>
                        <span class="trend-pill trend-up">↑ +12.5%</span>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-header">
                        <span class="metric-label">Pipeline Activo</span>
                        <div class="metric-icon-box"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
                    </div>
                    <div class="metric-value" id="metricTotalPipeline">Q0.00</div>
                    <div class="metric-sub-bar">
                        <div class="metric-sub-text" style="color:var(--brand-primary);" id="metricAvgDeal">Ticket Prom: Q0.00</div>
                        <span class="trend-pill trend-up">↑ +8.4%</span>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-header">
                        <span class="metric-label">Tasa de Conversión</span>
                        <div class="metric-icon-box"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
                    </div>
                    <div class="metric-value" id="metricConversionRate">0%</div>
                    <div class="metric-sub-bar">
                        <div class="metric-sub-text" style="color:var(--status-active-text);">Clientes cerrados activos</div>
                        <span class="trend-pill trend-up">↑ +3.2%</span>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-header">
                        <span class="metric-label">Leads &amp; Prospectos</span>
                        <div class="metric-icon-box"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div>
                    </div>
                    <div class="metric-value" id="metricActiveLeads">0</div>
                    <div class="metric-sub-bar">
                        <div class="metric-sub-text" style="color:var(--status-prospect-text);">En embudo de venta</div>
                        <span class="trend-pill trend-neutral">⚡ 5 nuevos</span>
                    </div>
                </div>
            </div>

            <!-- Graficos  -->
            <div class="dashboard-grid-3">
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
                            Embudo de Ventas
                        </div>
                    </div>
                    <div style="height:190px;position:relative;"><canvas id="statusChart"></canvas></div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                            Pipeline por Sector
                        </div>
                    </div>
                    <div style="height:190px;position:relative;"><canvas id="industryChart"></canvas></div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                            Canales de Contacto
                        </div>
                    </div>
                    <div style="height:190px;position:relative;"><canvas id="activityChart"></canvas></div>
                </div>
            </div>
        </section>

        <!--GESTION DE CLIENTES-->
        <section class="view-section" id="view-clientes">
            <header class="header">
                <div class="header-title">
                    <h1>Gestión de Clientes &amp; Directorio Operativo</h1>
                    <p>Búsqueda en tiempo real, filtros por etapa o industria, y edición rápida.</p>
                </div>
                <div class="header-actions">
                    <a href="api/export.php" target="_blank" class="btn btn-secondary" title="Ver e imprimir informe ejecutivo formateado">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Informe HTML / PDF
                    </a>
                    <button class="btn btn-primary btnOpenNewClient">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Nuevo Cliente
                    </button>
                </div>
            </header>

            <div class="controls-bar">
                <div class="search-box">
                    <svg class="search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="searchInput" placeholder="Buscar cliente por nombre, empresa, correo o teléfono..." autocomplete="off">
                    <button class="search-clear" id="searchClear" title="Limpiar búsqueda">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="filter-group">
                    <select class="select-custom" id="statusFilter">
                        <option value="all">Todos los estados</option>
                        <option value="lead">Leads</option>
                        <option value="prospect">Prospectos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                    <select class="select-custom" id="industryFilter">
                        <option value="all">Todas las industrias</option>
                    </select>
                    <select class="select-custom" id="sortSelect">
                        <option value="date_desc">Más recientes</option>
                        <option value="date_asc">Más antiguos</option>
                        <option value="name_asc">Nombre (A–Z)</option>
                        <option value="name_desc">Nombre (Z–A)</option>
                        <option value="value_desc">Mayor valor</option>
                        <option value="value_asc">Menor valor</option>
                        <option value="contact_desc">Último contacto</option>
                    </select>
                </div>
            </div>

            <div class="table-toolbar">
                <span class="result-count" id="resultCount">Cargando...</span>
                <div class="pagination-controls" id="pagination"></div>
            </div>

            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width:20%;" data-sort="name"><div class="th-inner">Cliente <span class="sort-arrow">↕</span></div></th>
                            <th style="width:18%;">Empresa / Sector</th>
                            <th style="width:9%;">Contacto</th>
                            <th style="width:13%;" data-sort="status"><div class="th-inner">Etapa Embudo <span class="sort-arrow">↕</span></div></th>
                            <th style="width:11%;" data-sort="value"><div class="th-inner">Valor (Q) <span class="sort-arrow">↕</span></div></th>
                            <th style="width:13%;" data-sort="contact"><div class="th-inner">Último Contacto <span class="sort-arrow">↕</span></div></th>
                            <th style="width:16%;text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="clientsTableBody"></tbody>
                </table>
            </div>
        </section>

        <!--SEGUIMIENTOS & EQUIPO-->
        <section class="view-section" id="view-seguimientos">
            <header class="header">
                <div class="header-title">
                    <h1>Seguimientos &amp; Bitácora Colaborativa de Equipo</h1>
                    <p>Agenda de contactos agendados, compromisos con clientes y actividad de asesores.</p>
                    <button class="btn btn-secondary" id="btnOpenGlobalHistory" style="margin-top:10px;font-size:0.78rem;padding:5px 14px;border-radius:20px;display:inline-flex;align-items:center;gap:6px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Ver Historial de Bitácora
                    </button>
                </div>
                <div class="header-actions">
                    <a href="api/export.php" target="_blank" class="btn btn-secondary" title="Ver e imprimir informe ejecutivo formateado">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Informe HTML / PDF
                    </a>
                    <button class="btn btn-primary btnOpenNewClient">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Nuevo Cliente
                    </button>
                </div>
            </header>

            <div class="panel" style="margin-bottom:24px;">
                <div class="panel-header">
                    <div class="panel-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Próximos Seguimientos Agendados
                    </div>
                </div>
                <div id="upcomingFollowupsContainer" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:12px;"></div>
            </div>
        </section>

    </main>
</div>

<!-- Registro / Edicion de Cliente -->
<div class="modal-overlay" id="clientModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="clientModalTitle">Nuevo Cliente</h3>
            <button class="btn-icon close-modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="clientForm" novalidate>
            <input type="hidden" id="clientId">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="clientName">Nombre Completo *</label>
                        <input type="text" class="form-control" id="clientName" placeholder="Carlos Castillo" autocomplete="off">
                        <span class="form-error" id="clientNameError"></span>
                    </div>
                    <div class="form-group">
                        <label for="clientEmail">Correo Electrónico *</label>
                        <input type="email" class="form-control" id="clientEmail" placeholder="carlos@empresa.com.gt" autocomplete="off">
                        <span class="form-error" id="clientEmailError"></span>
                    </div>
                    <div class="form-group">
                        <label for="clientPhone">Teléfono / WhatsApp</label>
                        <div class="phone-input-group" style="display:flex;gap:6px;">
                            <select class="form-control" id="clientPhonePrefix" style="width:115px;flex-shrink:0;padding:8px 6px;">
                                <option value="+502">🇬🇹 +502</option>
                                <option value="+503">🇸🇻 +503</option>
                                <option value="+504">🇭🇳 +504</option>
                                <option value="+505">🇳🇮 +505</option>
                                <option value="+506">🇨🇷 +506</option>
                                <option value="+507">🇵🇦 +507</option>
                                <option value="+52">🇲🇽 +52</option>
                                <option value="+1">🇺🇸 +1</option>
                                <option value="+34">🇪🇸 +34</option>
                                <option value="+57">🇨🇴 +57</option>
                            </select>
                            <input type="text" class="form-control" id="clientPhone" placeholder="2345 6789" autocomplete="off" style="flex:1;">
                        </div>
                        <span class="form-error" id="clientPhoneError"></span>
                    </div>
                    <div class="form-group">
                        <label for="clientCompany">Empresa</label>
                        <input type="text" class="form-control" id="clientCompany" placeholder="Corporación Progreso S.A.">
                    </div>
                    <div class="form-group">
                        <label for="clientPosition">Cargo / Puesto</label>
                        <input type="text" class="form-control" id="clientPosition" placeholder="Director Comercial">
                    </div>
                    <div class="form-group">
                        <label for="clientStatus">Etapa del Embudo</label>
                        <select class="form-control" id="clientStatus">
                            <option value="lead">Lead (Prospecto Inicial)</option>
                            <option value="prospect">Prospecto (En Negociación)</option>
                            <option value="active">Cliente Activo (Cerrado)</option>
                            <option value="inactive">Inactivo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="clientValue">Valor Oportunidad (Q)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="clientValue" placeholder="0.00" value="0">
                    </div>
                    <div class="form-group">
                        <label for="clientIndustry">Sector / Industria</label>
                        <input type="text" class="form-control" id="clientIndustry" placeholder="Ej. Alimentos, Tecnología, Construcción">
                    </div>
                    <div class="form-group full-width">
                        <label for="clientAddress">Dirección Física</label>
                        <input type="text" class="form-control" id="clientAddress" placeholder="Diagonal 6, Zona 10, Ciudad de Guatemala">
                    </div>
                    <div class="form-group full-width">
                        <label for="clientNotes">Notas Adicionales</label>
                        <textarea class="form-control" id="clientNotes" placeholder="Detalles de intereses, presupuesto estimado..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="saveClientBtn">Guardar Cliente</button>
            </div>
        </form>
    </div>
</div>

<!-- Detalle de Cliente  -->
<div class="modal-overlay" id="detailModal">
    <div class="modal-card" style="max-width:700px;">
        <div class="modal-header">
            <div>
                <h3 id="detailClientName">Nombre del Cliente</h3>
                <p style="font-size:0.8rem;color:var(--text-sub);" id="detailClientMeta">Empresa · Sector</p>
            </div>
            <button class="btn-icon close-modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <!-- Formulario agregar nueva interacción -->
            <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px;margin-bottom:20px;">
                <h4 style="font-size:0.85rem;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--brand-primary)" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Registrar Nuevo Seguimiento / Interacción
                </h4>
                <form id="interactionForm" novalidate>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="interactionType">Tipo de Contacto</label>
                            <select class="form-control" id="interactionType">
                                <option value="call">📞 Llamada Telefónica</option>
                                <option value="meeting">🤝 Reunión Presencial / Virtual</option>
                                <option value="email">✉️ Correo Electrónico</option>
                                <option value="note">📝 Nota de Seguimiento</option>
                                <option value="task">📌 Tarea Pendiente</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="interactionSubject">Asunto / Resumen *</label>
                            <input type="text" class="form-control" id="interactionSubject" placeholder="Demostración de producto realizada">
                        </div>
                        <div class="form-group full-width">
                            <label for="interactionDesc">Detalle &amp; Compromisos Acordados</label>
                            <textarea class="form-control" id="interactionDesc" placeholder="Detalla los puntos tratados y próximos pasos..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="interactionNextDate">Fecha de Próximo Contacto</label>
                            <input type="datetime-local" class="form-control" id="interactionNextDate">
                        </div>
                        <div class="form-group" style="justify-content:flex-end;">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary" id="addInteractionBtn" style="width:100%;">Guardar Registro</button>
                        </div>
                    </div>
                </form>
            </div>
            <h4 style="font-size:0.88rem;font-weight:600;margin-bottom:4px;">Bitácora de Colaboración de Equipo</h4>
            <div class="timeline" id="interactionTimeline"></div>
        </div>
    </div>
</div>

<!-- Seguimientos Globales  -->
<div class="modal-overlay" id="globalFollowupsModal">
    <div class="modal-card" style="max-width:780px;">
        <div class="modal-header">
            <div>
                <h3>Historial Colaborativo de Equipo</h3>
                <p style="font-size:0.8rem;color:var(--text-sub);margin-top:2px;">Consolidado de todas las actividades e interacciones comerciales</p>
            </div>
            <button class="btn-icon close-modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="timeline" id="globalTimeline"></div>
        </div>
    </div>
</div>

<!-- Confirmar Eliminacion -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-card" style="max-width:400px;">
        <div class="modal-header">
            <h3>Confirmar Eliminación</h3>
            <button class="btn-icon close-modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="text-align:center;padding:24px 20px;">
            <div style="width:48px;height:48px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </div>
            <p style="font-weight:700;font-size:1rem;color:var(--text-main);margin-bottom:6px;">¿Eliminar cliente?</p>
            <p style="font-size:0.84rem;color:var(--text-sub);margin-bottom:4px;">Se eliminará a <strong id="deleteClientName"></strong> y todo su historial de interacciones.</p>
            <p style="font-size:0.8rem;color:var(--text-muted);">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer" style="justify-content:center;">
            <button class="btn btn-secondary close-modal">Cancelar</button>
            <button class="btn btn-danger" id="confirmDeleteBtn">Eliminar Cliente</button>
        </div>
    </div>
</div>

<nav class="mobile-bottom-nav" id="mobileBottomNav">
    <a href="#dashboard" class="bottom-nav-item active" data-target="view-dashboard">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <span>Dashboard</span>
    </a>
    <a href="#clientes" class="bottom-nav-item" data-target="view-clientes">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 1 0 7.75"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Clientes</span>
    </a>
    <a href="#seguimientos" class="bottom-nav-item" data-target="view-seguimientos">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        <span>Seguimiento</span>
    </a>
</nav>

<div class="toast-container" id="toastContainer"></div>

<script src="assets/js/charts.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
