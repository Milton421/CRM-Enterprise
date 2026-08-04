

document.addEventListener('DOMContentLoaded', () => {
    const state = {
        clients: [],
        filteredCount: 0,
        totalCount: 0,
        currentPage: 1,
        pageSize: 10,
        currentFilterStatus: 'all',
        currentFilterIndustry: 'all',
        currentSort: 'date_desc',
        searchQuery: '',
        activeClientId: null,
        isLoading: false
    };

    function apiUrl(endpoint) {
        return 'api/' + endpoint;
    }

    const sidebarMenu      = document.getElementById('sidebarMenu');
    const clientsTableBody = document.getElementById('clientsTableBody');
    const searchInput      = document.getElementById('searchInput');
    const searchClear      = document.getElementById('searchClear');
    const statusFilter     = document.getElementById('statusFilter');
    const industryFilter   = document.getElementById('industryFilter');
    const sortSelect       = document.getElementById('sortSelect');
    const btnNewClient     = document.getElementById('btnNewClient');
    const dbBannerAlert    = document.getElementById('dbBannerAlert');
    const resultCount      = document.getElementById('resultCount');
    const paginationEl     = document.getElementById('pagination');
    const tableHeaders     = document.querySelectorAll('[data-sort]');

    const clientModal      = document.getElementById('clientModal');
    const clientForm       = document.getElementById('clientForm');
    const clientModalTitle = document.getElementById('clientModalTitle');
    const saveClientBtn    = document.getElementById('saveClientBtn');

    const detailModal          = document.getElementById('detailModal');
    const interactionForm      = document.getElementById('interactionForm');
    const interactionTimeline  = document.getElementById('interactionTimeline');
    const addInteractionBtn    = document.getElementById('addInteractionBtn');

    const globalFollowupsModal = document.getElementById('globalFollowupsModal');
    const globalTimeline       = document.getElementById('globalTimeline');

    const deleteModal      = document.getElementById('deleteModal');
    const deleteClientName = document.getElementById('deleteClientName');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    // Métricas
    const metricTotalClients   = document.getElementById('metricTotalClients');
    const metricActiveLeads    = document.getElementById('metricActiveLeads');
    const metricTotalPipeline  = document.getElementById('metricTotalPipeline');
    const metricAvgDeal        = document.getElementById('metricAvgDeal');
    const metricConversionRate = document.getElementById('metricConversionRate');

    initApp();

    function initApp() {
        fetchStats();
        fetchClients();
        initNavigation();
        initSearch();
        initFilters();
        initTableHeaders();
        initFormValidation();
        initHistoryToggle();

        btnNewClient?.addEventListener('click', () => openClientModal());
        document.getElementById('btnOpenGlobalFollowups')?.addEventListener('click', openGlobalFollowupsModal);
        document.getElementById('btnOpenGlobalHistory')?.addEventListener('click', openGlobalFollowupsModal);
        clientForm?.addEventListener('submit', handleClientSubmit);
        interactionForm?.addEventListener('submit', handleInteractionSubmit);
        confirmDeleteBtn?.addEventListener('click', handleClientDelete);

        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', closeAllModals);
        });

        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) closeAllModals();
            });
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeAllModals();
        });

        initSidebarToggle();
        initTableDragScroll();

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                fetchStats();
            }, 300);
        });
    }

    function initTableDragScroll() {
        document.querySelectorAll('.table-container').forEach(container => {
            let isDown = false;
            let startX;
            let scrollLeft;

            container.addEventListener('mousedown', (e) => {
                if (e.target.closest('button, a, input, select')) return;
                isDown = true;
                container.classList.add('is-dragging');
                startX = e.pageX - container.offsetLeft;
                scrollLeft = container.scrollLeft;
            });

            container.addEventListener('mouseleave', () => {
                isDown = false;
                container.classList.remove('is-dragging');
            });

            container.addEventListener('mouseup', () => {
                isDown = false;
                container.classList.remove('is-dragging');
            });

            container.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - container.offsetLeft;
                const walk = (x - startX) * 1.8;
                container.scrollLeft = scrollLeft - walk;
            });
        });
    }

    function initSidebarToggle() {
        const appContainer     = document.querySelector('.app-container');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const brandLogoBtn     = document.getElementById('brandLogoBtn');
        const mobileMenuBtn    = document.getElementById('mobileMenuBtn');
        const sidebarOverlay   = document.getElementById('sidebarOverlay');

        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            appContainer?.classList.add('sidebar-collapsed');
        }

        function toggleSidebar() {
            appContainer?.classList.toggle('sidebar-collapsed');
            const isCollapsed = appContainer?.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
            setTimeout(() => fetchStats(), 250); 
        }

        sidebarToggleBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });

        brandLogoBtn?.addEventListener('click', () => {
            if (appContainer?.classList.contains('sidebar-collapsed')) {
                toggleSidebar();
            }
        });

        mobileMenuBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            document.body.classList.toggle('mobile-sidebar-open');
        });

        sidebarOverlay?.addEventListener('click', () => {
            document.body.classList.remove('mobile-sidebar-open');
        });
    }

    function initHistoryToggle() {
        const btnToggleHistory = document.getElementById('btnToggleHistory');
        const btnToggleHistoryText = document.getElementById('btnToggleHistoryText');
        const btnShowHistoryDirect = document.getElementById('btnShowHistoryDirect');
        const historyContainer = document.getElementById('historyContainer');
        const historyPlaceholder = document.getElementById('historyPlaceholder');

        function toggleHistory() {
            if (!historyContainer) return;
            const isHidden = historyContainer.style.display === 'none';
            if (isHidden) {
                historyContainer.style.display = 'block';
                if (historyPlaceholder) historyPlaceholder.style.display = 'none';
                if (btnToggleHistoryText) btnToggleHistoryText.textContent = 'Ocultar Historial';
                if (btnToggleHistory) {
                    btnToggleHistory.classList.remove('btn-secondary');
                    btnToggleHistory.classList.add('btn-primary');
                }
                loadSectionGlobalTimeline();
            } else {
                historyContainer.style.display = 'none';
                if (historyPlaceholder) historyPlaceholder.style.display = 'block';
                if (btnToggleHistoryText) btnToggleHistoryText.textContent = 'Ver Historial';
                if (btnToggleHistory) {
                    btnToggleHistory.classList.remove('btn-primary');
                    btnToggleHistory.classList.add('btn-secondary');
                }
            }
        }

        btnToggleHistory?.addEventListener('click', toggleHistory);
        btnShowHistoryDirect?.addEventListener('click', toggleHistory);
    }

    function initNavigation() {
        function switchTab(targetId) {
            const targetEl = document.getElementById(targetId);
            if (!targetEl) return;

            document.querySelectorAll('.view-section').forEach(sec => {
                sec.classList.remove('active');
            });

            if (sidebarMenu) {
                sidebarMenu.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                const activeItem = sidebarMenu.querySelector(`[data-target="${targetId}"]`);
                if (activeItem) activeItem.classList.add('active');
            }

            const mobileBottomNav = document.getElementById('mobileBottomNav');
            if (mobileBottomNav) {
                mobileBottomNav.querySelectorAll('.bottom-nav-item').forEach(i => i.classList.remove('active'));
                const activeBottomItem = mobileBottomNav.querySelector(`[data-target="${targetId}"]`);
                if (activeBottomItem) activeBottomItem.classList.add('active');
            }

            targetEl.classList.add('active');
            document.body.classList.remove('mobile-sidebar-open');

            const hashMap = {
                'view-dashboard': '#dashboard',
                'view-clientes': '#clientes',
                'view-seguimientos': '#seguimientos'
            };
            if (hashMap[targetId] && window.location.hash !== hashMap[targetId]) {
                history.pushState(null, '', hashMap[targetId]);
            }

            if (targetId === 'view-dashboard') {
                setTimeout(() => fetchStats(), 100);
            } else if (targetId === 'view-clientes') {
                setTimeout(() => searchInput?.focus(), 150);
            } else if (targetId === 'view-seguimientos') {
                if (document.getElementById('historyContainer')?.style.display !== 'none') {
                    loadSectionGlobalTimeline();
                }
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        if (sidebarMenu) {
            sidebarMenu.querySelectorAll('.nav-item').forEach(item => {
                const link = item.querySelector('a');
                if (!link) return;
                link.addEventListener('click', e => {
                    e.preventDefault();
                    const target = item.dataset.target;
                    if (target) switchTab(target);
                });
            });
        }

        const mobileBottomNav = document.getElementById('mobileBottomNav');
        if (mobileBottomNav) {
            mobileBottomNav.querySelectorAll('.bottom-nav-item').forEach(item => {
                item.addEventListener('click', e => {
                    e.preventDefault();
                    const target = item.dataset.target;
                    if (target) switchTab(target);
                });
            });
        }

        document.querySelectorAll('.btnOpenNewClient').forEach(btn => {
            btn.addEventListener('click', () => openClientModal());
        });

        const currentHash = window.location.hash;
        if (currentHash === '#clientes') {
            switchTab('view-clientes');
        } else if (currentHash === '#seguimientos') {
            switchTab('view-seguimientos');
        } else {
            switchTab('view-dashboard');
        }

        window.addEventListener('popstate', () => {
            const hash = window.location.hash;
            if (hash === '#clientes') switchTab('view-clientes');
            else if (hash === '#seguimientos') switchTab('view-seguimientos');
            else switchTab('view-dashboard');
        });
    }

    // BÚSQUEDA 
    function initSearch() {
        let debounce;
        searchInput?.addEventListener('input', e => {
            state.searchQuery = e.target.value.trim();
            state.currentPage = 1;
            if (searchClear) searchClear.classList.toggle('visible', state.searchQuery.length > 0);
            clearTimeout(debounce);
            debounce = setTimeout(fetchClients, 280);
        });

        if (searchClear) {
            searchClear.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                state.searchQuery = '';
                state.currentPage = 1;
                searchClear.classList.remove('visible');
                searchInput?.focus();
                fetchClients();
            });
        }
    }

    // FILTROS
    function initFilters() {
        statusFilter?.addEventListener('change', e => {
            state.currentFilterStatus = e.target.value;
            state.currentPage = 1;
            fetchClients();
        });

        industryFilter?.addEventListener('change', e => {
            state.currentFilterIndustry = e.target.value;
            state.currentPage = 1;
            fetchClients();
        });

        sortSelect?.addEventListener('change', e => {
            state.currentSort = e.target.value;
            state.currentPage = 1;
            fetchClients();
        });
    }

    // ORDENAMIENTO POR CABECERA
    function initTableHeaders() {
        tableHeaders.forEach(th => {
            th.addEventListener('click', () => {
                const sortKey = th.dataset.sort;
                if (!sortKey) return;

                let newSort = sortKey;
                if (state.currentSort === sortKey + '_asc') {
                    newSort = sortKey + '_desc';
                } else {
                    newSort = sortKey + '_asc';
                }
                state.currentSort = newSort;

                tableHeaders.forEach(header => {
                    const arrow = header.querySelector('.sort-arrow');
                    if (!arrow) return;
                    if (header.dataset.sort === sortKey) {
                        header.classList.add('sorted');
                        arrow.textContent = newSort.endsWith('_asc') ? '↑' : '↓';
                    } else {
                        header.classList.remove('sorted');
                        arrow.textContent = '↕';
                    }
                });

                fetchClients();
            });
        });
    }

    // PETICIONES API
    async function fetchStats() {
        try {
            const res = await fetch(apiUrl('stats.php'));
            const data = await res.json();

            if (data.success && data.stats) {
                const s = data.stats;
                if (metricTotalClients) metricTotalClients.textContent = s.total_clients || 0;
                if (metricActiveLeads) metricActiveLeads.textContent = s.active_leads || 0;
                if (metricTotalPipeline) {
                    metricTotalPipeline.textContent = 'Q' + numberFormat(s.total_pipeline || 0);
                }
                if (metricAvgDeal) {
                    const avgDeal = s.active_clients > 0 ? (s.total_pipeline / s.active_clients) : 0;
                    metricAvgDeal.textContent = 'Ticket Prom: Q' + numberFormat(avgDeal);
                }
                if (metricConversionRate) metricConversionRate.textContent = (s.conversion_rate || 0) + '%';

                if (typeof renderCRMCharts === 'function') renderCRMCharts(s);
                renderUpcomingFollowups(s.upcoming_followups || []);

                if (dbBannerAlert) dbBannerAlert.style.display = 'none';
            } else if (data.setup_url && dbBannerAlert) {
                dbBannerAlert.style.display = 'flex';
            }
        } catch (err) {
            console.error('Error al cargar métricas:', err);
        }
    }

    async function fetchClients() {
        if (state.isLoading) return;
        state.isLoading = true;
        showTableSkeleton();

        try {
            const params = new URLSearchParams({
                q: state.searchQuery,
                status: state.currentFilterStatus,
                industry: state.currentFilterIndustry,
                sort: state.currentSort
            });

            const res = await fetch(apiUrl(`clients.php?${params.toString()}`));
            const result = await res.json();

            if (!result.success) {
                if (dbBannerAlert) dbBannerAlert.style.display = 'flex';
                showEmptyState('Base de Datos no disponible', 'Ejecute setup.php para inicializar la estructura.');
                return;
            }

            if (dbBannerAlert) dbBannerAlert.style.display = 'none';
            state.clients = result.data || [];
            state.filteredCount = state.clients.length;

            updateIndustryDropdown(result.available_industries || []);
            renderClientsTable();
            renderPagination();
            updateResultCount();
        } catch (err) {
            showEmptyState('Error de Conexión', 'Comprueba que Apache y MySQL están activos en XAMPP.');
        } finally {
            state.isLoading = false;
        }
    }

    // RENDERIZADO 
    function getPagedClients() {
        const start = (state.currentPage - 1) * state.pageSize;
        return state.clients.slice(start, start + state.pageSize);
    }

    function renderClientsTable() {
        const clients = getPagedClients();

        if (!clients.length) {
            showEmptyState(
                state.searchQuery || state.currentFilterStatus !== 'all' || state.currentFilterIndustry !== 'all'
                    ? 'Sin resultados de búsqueda'
                    : 'Sin clientes registrados',
                state.searchQuery || state.currentFilterStatus !== 'all' || state.currentFilterIndustry !== 'all'
                    ? 'Intenta ajustar los criterios de filtro.'
                    : 'Haz clic en "Nuevo Cliente" para agregar el primero.'
            );
            return;
        }

        const icoEye   = svgIcon('M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0-6 0');
        const icoEdit  = svgIcon('M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7 M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4z');
        const icoTrash = svgIcon('M3 6h18 M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6 M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2');
        const icoWa    = svgIcon('M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z');
        const icoMail  = svgIcon('M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z M22 6l-10 7L2 6');

        clientsTableBody.innerHTML = clients.map(c => {
            const initials = getInitials(c.name);
            const statusBadge = getStatusBadgeHTML(c.status);
            const cleanPhone = (c.phone || '').replace(/[^0-9]/g, '');
            const valNum = parseFloat(c.opportunity_value) || 0;

            const waButton = cleanPhone
                ? `<a href="https://wa.me/${cleanPhone}" target="_blank" class="action-btn action-wa" title="WhatsApp (${h(c.phone)})">${icoWa}</a>`
                : '';

            const mailButton = c.email
                ? `<a href="mailto:${h(c.email)}" class="action-btn action-mail" title="Correo (${h(c.email)})">${icoMail}</a>`
                : '';

            return `
            <tr>
                <td>
                    <div class="user-cell">
                        <div class="avatar">${initials}</div>
                        <div>
                            <div class="user-cell-name" title="${h(c.name)}">${h(c.name)}</div>
                            <div class="subtext-line" title="${h(c.email)}">${h(c.email)}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="company-cell" title="${h(c.company || 'Particular')}">${h(c.company || 'Particular')}</div>
                    <div class="position-text" title="${h(c.position || 'Contacto')} · ${h(c.industry || 'General')}">${h(c.position || 'Contacto')} · ${h(c.industry || 'General')}</div>
                </td>
                <td>
                    <div class="action-btn-group">
                        ${waButton}
                        ${mailButton}
                    </div>
                </td>
                <td>
                    <select class="select-custom stage-select-inline" data-client-id="${c.id}" style="font-size:0.76rem;padding:3px 6px;">
                        <option value="lead" ${c.status === 'lead' ? 'selected' : ''}>🔹 Lead</option>
                        <option value="prospect" ${c.status === 'prospect' ? 'selected' : ''}>📙 Prospecto</option>
                        <option value="active" ${c.status === 'active' ? 'selected' : ''}>🟢 Activo</option>
                        <option value="inactive" ${c.status === 'inactive' ? 'selected' : ''}>⚪ Inactivo</option>
                    </select>
                </td>
                <td>
                    <strong style="color:var(--text-main);font-size:0.86rem;">Q${numberFormat(valNum)}</strong>
                </td>
                <td style="color:var(--text-sub);font-size:0.78rem;">
                    ${c.last_contact_at ? formatDate(c.last_contact_at) : '<span style="color:var(--text-muted);">Sin registro</span>'}
                </td>
                <td>
                    <div class="action-btn-group" style="justify-content:flex-end;">
                        <button class="action-btn view-btn" data-id="${c.id}" title="Ver Bitácora (${c.interaction_count || 0})">
                            ${icoEye}
                            <span class="badge-count">${c.interaction_count || 0}</span>
                        </button>
                        <button class="action-btn edit-btn" data-id="${c.id}" title="Editar Cliente">${icoEdit}</button>
                        <button class="action-btn delete-btn" data-id="${c.id}" data-name="${h(c.name)}" title="Eliminar Cliente">${icoTrash}</button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        // Eventos de botones
        clientsTableBody.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', () => openDetailModal(btn.dataset.id));
        });
        clientsTableBody.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => openClientModal(btn.dataset.id));
        });
        clientsTableBody.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => openDeleteModal(btn.dataset.id, btn.dataset.name));
        });

        clientsTableBody.querySelectorAll('.stage-select-inline').forEach(select => {
            select.addEventListener('change', async e => {
                const clientId = select.dataset.clientId;
                const newStatus = select.value;
                try {
                    const res = await fetch(apiUrl('clients.php'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: parseInt(clientId), status: newStatus, status_only: true, _method: 'PUT' })
                    });
                    const result = await res.json();
                    if (result.success) {
                        showToast('Etapa de venta actualizada.', 'success');
                        fetchStats();
                    } else {
                        showToast(result.error || 'Error al actualizar etapa.', 'error');
                    }
                } catch (err) {
                    showToast('Error al actualizar etapa.', 'error');
                }
            });
        });
    }

    function showTableSkeleton() {
        clientsTableBody.innerHTML = Array(5).fill(0).map(() => `
            <tr>
                <td><div class="skeleton skeleton-text" style="width:160px;"></div></td>
                <td><div class="skeleton skeleton-text" style="width:120px;"></div></td>
                <td><div class="skeleton skeleton-text" style="width:140px;"></div></td>
                <td><div class="skeleton skeleton-text" style="width:90px;"></div></td>
                <td><div class="skeleton skeleton-text" style="width:80px;"></div></td>
                <td><div class="skeleton skeleton-text" style="width:100px;"></div></td>
                <td></td>
            </tr>
        `).join('');
    }

    function showEmptyState(title, subtitle) {
        clientsTableBody.innerHTML = `
            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </div>
                        <h3>${h(title)}</h3>
                        <p>${h(subtitle)}</p>
                    </div>
                </td>
            </tr>`;
    }

    function renderUpcomingFollowups(list) {
        const container = document.getElementById('upcomingFollowupsContainer');
        if (!container) return;

        if (!list.length) {
            container.innerHTML = `
                <div style="grid-column: 1 / -1;text-align:center;padding:16px;color:var(--text-sub);font-size:0.85rem;">
                    No hay tareas o seguimientos próximos agendados.
                </div>`;
            return;
        }

        const icoCal = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`;

        container.innerHTML = list.map(item => {
            const iconMap = {
                call: '📞 Llamada',
                meeting: '🤝 Reunión',
                email: '✉️ Correo',
                note: '📝 Nota',
                task: '📌 Tarea'
            };
            const labelType = iconMap[item.type] || '📌 Seguimiento';

            return `
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;box-shadow:0 1px 4px rgba(0,0,0,0.03);transition:all 0.15s ease;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px;">
                        <span class="badge badge-active" style="font-size:0.71rem;padding:3px 8px;font-weight:700;">${labelType}</span>
                        <span style="font-size:0.73rem;color:var(--brand-primary);font-weight:700;display:inline-flex;align-items:center;">${icoCal}${formatDate(item.next_followup_date)}</span>
                    </div>
                    <div style="font-weight:800;font-size:0.84rem;color:#0f172a;margin-bottom:4px;line-height:1.3;">${h(item.subject)}</div>
                    <div style="font-size:0.77rem;color:#475569;margin-top:3px;">
                        Cliente: <strong style="color:#0f172a;">${h(item.client_name)}</strong> <span style="color:#64748b;">(${h(item.client_company || 'Particular')})</span>
                    </div>
                    <div style="font-size:0.73rem;color:#64748b;font-weight:600;margin-top:6px;">Asesor: ${h(item.user_name || 'Agente BI')}</div>
                </div>`;
        }).join('');
    }

    function updateIndustryDropdown(industries) {
        if (!industryFilter) return;
        const currentSelected = state.currentFilterIndustry;
        const optionsHtml = ['<option value="all">Todas las industrias</option>'];

        industries.forEach(ind => {
            if (!ind) return;
            const sel = ind === currentSelected ? 'selected' : '';
            optionsHtml.push(`<option value="${h(ind)}" ${sel}>${h(ind)}</option>`);
        });

        industryFilter.innerHTML = optionsHtml.join('');
    }

    function updateResultCount() {
        if (!resultCount) return;
        const total = state.clients.length;
        if (state.searchQuery || state.currentFilterStatus !== 'all' || state.currentFilterIndustry !== 'all') {
            resultCount.textContent = `Mostrando ${total} resultado(s) filtrado(s)`;
        } else {
            resultCount.textContent = `Total: ${total} cliente(s) registrados`;
        }
    }

    function renderPagination() {
        if (!paginationEl) return;
        const totalPages = Math.ceil(state.clients.length / state.pageSize) || 1;

        if (totalPages <= 1) {
            paginationEl.innerHTML = '';
            return;
        }

        let html = `
            <button class="page-btn" ${state.currentPage === 1 ? 'disabled' : ''} data-page="${state.currentPage - 1}">‹ Ant</button>
        `;

        for (let i = 1; i <= totalPages; i++) {
            html += `
                <button class="page-btn ${i === state.currentPage ? 'active' : ''}" data-page="${i}">${i}</button>
            `;
        }

        html += `
            <button class="page-btn" ${state.currentPage === totalPages ? 'disabled' : ''} data-page="${state.currentPage + 1}">Sig ›</button>
        `;

        paginationEl.innerHTML = html;

        paginationEl.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.disabled) return;
                state.currentPage = parseInt(btn.dataset.page);
                renderClientsTable();
                renderPagination();
                document.querySelector('.table-container')?.scrollIntoView({ behavior: 'smooth' });
            });
        });
    }

    // MODALES Y FORMULARIOS 
    function openClientModal(clientArg = null) {
        resetClientForm();
        const clientId = (typeof clientArg === 'string' || typeof clientArg === 'number') ? clientArg : null;
        if (clientId) {
            const c = state.clients.find(x => x.id == clientId);
            if (!c) return;
            clientModalTitle.textContent = 'Editar Cliente';
            document.getElementById('clientId').value = c.id;
            document.getElementById('clientName').value = c.name || '';
            document.getElementById('clientEmail').value = c.email || '';
            const phoneVal = c.phone || '';
            const prefixSelect = document.getElementById('clientPhonePrefix');
            const phoneInput = document.getElementById('clientPhone');
            if (prefixSelect && phoneInput) {
                let matchedPrefix = null;
                for (let option of prefixSelect.options) {
                    if (phoneVal.startsWith(option.value)) {
                        matchedPrefix = option.value;
                        break;
                    }
                }
                if (matchedPrefix) {
                    prefixSelect.value = matchedPrefix;
                    phoneInput.value = phoneVal.substring(matchedPrefix.length).trim();
                } else {
                    prefixSelect.value = '+502';
                    phoneInput.value = phoneVal;
                }
            } else {
                document.getElementById('clientPhone').value = phoneVal;
            }
            document.getElementById('clientCompany').value = c.company || '';
            document.getElementById('clientPosition').value = c.position || '';
            document.getElementById('clientStatus').value = c.status || 'lead';
            document.getElementById('clientValue').value = c.opportunity_value || 0;
            document.getElementById('clientIndustry').value = c.industry || '';
            document.getElementById('clientAddress').value = c.address || '';
            document.getElementById('clientNotes').value = c.notes || '';
        } else {
            clientModalTitle.textContent = 'Nuevo Cliente';
        }
        clientModal.classList.add('active');
        document.getElementById('clientName')?.focus();
    }

    async function handleClientSubmit(e) {
        e.preventDefault();
        if (!validateClientForm()) return;

        setLoadingBtn(saveClientBtn, true);

        const prefixSelect = document.getElementById('clientPhonePrefix');
        const phoneInput = document.getElementById('clientPhone');
        const rawPhone = phoneInput ? phoneInput.value.trim() : '';
        let fullPhone = '';
        if (rawPhone) {
            if (rawPhone.startsWith('+')) {
                fullPhone = rawPhone;
            } else {
                fullPhone = `${prefixSelect ? prefixSelect.value : '+502'} ${rawPhone}`;
            }
        }

        const clientId = document.getElementById('clientId').value;
        const payload = {
            name: document.getElementById('clientName').value.trim(),
            email: document.getElementById('clientEmail').value.trim(),
            phone: fullPhone,
            company: document.getElementById('clientCompany').value.trim(),
            position: document.getElementById('clientPosition').value.trim(),
            status: document.getElementById('clientStatus').value,
            opportunity_value: parseFloat(document.getElementById('clientValue').value) || 0,
            industry: document.getElementById('clientIndustry').value.trim(),
            address: document.getElementById('clientAddress').value.trim(),
            notes: document.getElementById('clientNotes').value.trim()
        };

        try {
            let res;
            if (clientId) {
                payload.id = parseInt(clientId);
                payload._method = 'PUT';
                res = await fetch(apiUrl('clients.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
            } else {
                res = await fetch(apiUrl('clients.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
            }

            const result = await res.json();
            if (result.success) {
                showToast(clientId ? 'Cliente actualizado correctamente.' : 'Cliente registrado exitosamente.', 'success');
                closeAllModals();
                fetchStats();
                fetchClients();
            } else {
                showToast(result.error || 'Error al procesar cliente.', 'error');
            }
        } catch (err) {
            showToast('Error de conexión con la API.', 'error');
        } finally {
            setLoadingBtn(saveClientBtn, false);
        }
    }

    async function openDetailModal(clientId) {
        state.activeClientId = clientId;
        const c = state.clients.find(x => x.id == clientId);
        if (!c) return;

        document.getElementById('detailClientName').textContent = c.name;
        document.getElementById('detailClientMeta').textContent = `${c.company || 'Particular'} · ${c.industry || 'General'} · Valor: Q${numberFormat(c.opportunity_value)}`;

        interactionTimeline.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-sub);">Cargando bitácora...</div>';
        detailModal.classList.add('active');

        await loadInteractions(clientId);
    }

    async function loadInteractions(clientId) {
        try {
            const res = await fetch(apiUrl(`interactions.php?client_id=${clientId}`));
            const data = await res.json();

            if (data.success && data.data && data.data.length > 0) {
                renderTimeline(interactionTimeline, data.data);
            } else {
                interactionTimeline.innerHTML = `
                    <div class="empty-state" style="padding:20px 0;">
                        <p style="color:var(--text-sub);">No hay interacciones registradas para este cliente.</p>
                    </div>`;
            }
        } catch (err) {
            interactionTimeline.innerHTML = '<div style="color:#ef4444;text-align:center;padding:15px;">Error al cargar bitácora.</div>';
        }
    }

    async function handleInteractionSubmit(e) {
        e.preventDefault();
        if (!state.activeClientId) {
            showToast('Selecciona un cliente válido para guardar la interacción.', 'error');
            return;
        }

        let subject = document.getElementById('interactionSubject').value.trim();
        const type = document.getElementById('interactionType').value;
        if (!subject) {
            const typeNames = {
                call: 'Llamada comercial realizada',
                meeting: 'Reunión de seguimiento',
                email: 'Envío de correo',
                note: 'Nota de seguimiento',
                task: 'Tarea agendada'
            };
            subject = typeNames[type] || 'Seguimiento comercial';
        }

        setLoadingBtn(addInteractionBtn, true);

        const payload = {
            client_id: state.activeClientId,
            type: type,
            subject: subject,
            description: document.getElementById('interactionDesc').value.trim(),
            next_followup_date: document.getElementById('interactionNextDate').value || null,
            user_name: 'Carlos Castillo'
        };

        try {
            const res = await fetch(apiUrl('interactions.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await res.json();
            if (result.success) {
                showToast('Interacción registrada en la bitácora.', 'success');
                document.getElementById('interactionSubject').value = '';
                document.getElementById('interactionDesc').value = '';
                document.getElementById('interactionNextDate').value = '';
                await loadInteractions(state.activeClientId);
                fetchStats();
                fetchClients();
            } else {
                showToast(result.error || 'Error al guardar registro.', 'error');
            }
        } catch (err) {
            showToast('Error de conexión.', 'error');
        } finally {
            setLoadingBtn(addInteractionBtn, false);
        }
    }

    async function loadSectionGlobalTimeline() {
        const timelineEl = document.getElementById('sectionGlobalTimeline');
        if (!timelineEl) return;
        timelineEl.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-sub);">Cargando bitácora del equipo...</div>';

        try {
            const res = await fetch(apiUrl('interactions.php'));
            const data = await res.json();

            if (data.success && data.data && data.data.length > 0) {
                renderTimeline(timelineEl, data.data, true);
            } else {
                timelineEl.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-sub);">No hay interacciones registradas.</div>';
            }
        } catch (err) {
            timelineEl.innerHTML = '<div style="text-align:center;padding:20px;color:#ef4444;">Error al cargar bitácora.</div>';
        }
    }

    async function openGlobalFollowupsModal() {
        if (!globalTimeline) return;
        globalTimeline.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-sub);">Cargando historial colaborativo...</div>';
        if (globalFollowupsModal) globalFollowupsModal.classList.add('active');

        try {
            const res = await fetch(apiUrl('interactions.php'));
            const data = await res.json();

            if (data.success && data.data && data.data.length > 0) {
                renderTimeline(globalTimeline, data.data, true);
            } else {
                globalTimeline.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-sub);">No hay interacciones registradas.</div>';
            }
        } catch (err) {
            globalTimeline.innerHTML = '<div style="text-align:center;padding:20px;color:#ef4444;">Error al cargar interacciones.</div>';
        }
    }

    function renderTimeline(containerEl, items, showClientName = false) {
        const typeIcons = {
            call: '📞',
            meeting: '🤝',
            email: '✉️',
            note: '📝',
            task: '📌'
        };

        containerEl.innerHTML = items.map(item => {
            const icon = typeIcons[item.type] || '📌';
            const clientHeader = showClientName && item.client_name
                ? `<div style="font-weight:700;color:var(--brand-primary);font-size:0.83rem;margin-bottom:2px;">Cliente: ${h(item.client_name)} (${h(item.client_company || 'Particular')})</div>`
                : '';

            const followupBadge = item.next_followup_date
                ? `<div style="margin-top:6px;font-size:0.74rem;color:var(--status-active-text);background:rgba(16,185,129,0.1);padding:3px 8px;border-radius:var(--radius-sm);display:inline-block;">
                    📅 Próximo contacto: ${formatDate(item.next_followup_date)}
                   </div>`
                : '';

            return `
            <div class="timeline-item">
                <div class="timeline-icon icon-${h(item.type)}">${icon}</div>
                <div class="timeline-content">
                    ${clientHeader}
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:4px;">
                        <span class="timeline-title">${h(item.subject)}</span>
                        <span class="timeline-date">${formatDate(item.interaction_date)}</span>
                    </div>
                    ${item.description ? `<p style="font-size:0.8rem;color:var(--text-sub);margin:4px 0 0;">${h(item.description)}</p>` : ''}
                    ${followupBadge}
                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:6px;">Por: ${h(item.user_name || 'Asesor Comercial')}</div>
                </div>
            </div>`;
        }).join('');
    }

    function openDeleteModal(id, name) {
        state.activeClientId = id;
        deleteClientName.textContent = name;
        deleteModal.classList.add('active');
    }

    async function handleClientDelete() {
        if (!state.activeClientId) return;
        setLoadingBtn(confirmDeleteBtn, true);

        try {
            const res = await fetch(apiUrl('clients.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: parseInt(state.activeClientId), _method: 'DELETE' })
            });

            const result = await res.json();
            if (result.success) {
                showToast('Cliente eliminado exitosamente.', 'success');
                closeAllModals();
                fetchStats();
                fetchClients();
            } else {
                showToast(result.error || 'Error al eliminar cliente.', 'error');
            }
        } catch (err) {
            showToast('Error de conexión.', 'error');
        } finally {
            setLoadingBtn(confirmDeleteBtn, false);
        }
    }

    function closeAllModals() {
        document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
        state.activeClientId = null;
    }

    // VALIDACIONES Y UTILIDADES
    function initFormValidation() {
        ['clientName', 'clientEmail', 'clientPhone'].forEach(id => {
            const input = document.getElementById(id);
            if (!input) return;
            input.addEventListener('input', () => clearFieldError(id));
        });
        const prefixSelect = document.getElementById('clientPhonePrefix');
        if (prefixSelect) {
            prefixSelect.addEventListener('change', () => clearFieldError('clientPhone'));
        }
    }

    function validateClientForm() {
        let valid = true;
        let firstInvalidInput = null;

        const nameInput = document.getElementById('clientName');
        const emailInput = document.getElementById('clientEmail');
        const phoneInput = document.getElementById('clientPhone');

        if (!nameInput.value.trim()) {
            showFieldError('clientName', 'El nombre es obligatorio.');
            valid = false;
            if (!firstInvalidInput) firstInvalidInput = nameInput;
        }

        const emailVal = emailInput.value.trim();
        if (!emailVal) {
            showFieldError('clientEmail', 'El correo electrónico es obligatorio.');
            valid = false;
            if (!firstInvalidInput) firstInvalidInput = emailInput;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
            showFieldError('clientEmail', 'Ingresa un correo válido (ej. usuario@dominio.com).');
            valid = false;
            if (!firstInvalidInput) firstInvalidInput = emailInput;
        }

        const phoneVal = phoneInput.value.trim();
        if (phoneVal) {
            const digitsOnly = phoneVal.replace(/\D/g, '');
            const validPhonePattern = /^\+?[0-9\s\-\(\)\.]{7,20}$/;
            if (!validPhonePattern.test(phoneVal) || digitsOnly.length < 7 || digitsOnly.length > 15) {
                showFieldError('clientPhone', 'Ingresa un teléfono válido (ej. +502 2345 6789).');
                valid = false;
                if (!firstInvalidInput) firstInvalidInput = phoneInput;
            }
        }

        if (!valid) {
            showToast('Por favor corrige los campos marcados en rojo.', 'error');
            if (firstInvalidInput) {
                firstInvalidInput.focus();
                firstInvalidInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        return valid;
    }

    function showFieldError(id, msg) {
        const input = document.getElementById(id);
        const errEl = document.getElementById(id + 'Error');
        if (input) {
            input.classList.add('is-invalid');
            input.classList.add('error');
        }
        if (errEl) {
            errEl.textContent = msg;
            errEl.classList.add('visible');
            errEl.style.display = 'block';
        }
    }

    function clearFieldError(id) {
        const input = document.getElementById(id);
        const errEl = document.getElementById(id + 'Error');
        if (input) {
            input.classList.remove('is-invalid');
            input.classList.remove('error');
        }
        if (errEl) {
            errEl.textContent = '';
            errEl.classList.remove('visible');
            errEl.style.display = 'none';
        }
    }

    function resetClientForm() {
        clientForm.reset();
        document.getElementById('clientId').value = '';
        const prefixSelect = document.getElementById('clientPhonePrefix');
        if (prefixSelect) prefixSelect.value = '+502';
        ['clientName', 'clientEmail', 'clientPhone'].forEach(clearFieldError);
    }

    function showToast(msg, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span>${h(msg)}</span>
            <button class="toast-close">&times;</button>
        `;

        container.appendChild(toast);

        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.remove();
        });

        setTimeout(() => {
            if (toast.parentNode) toast.remove();
        }, 4000);
    }

    function getStatusBadgeHTML(status) {
        const map = {
            lead: '<span class="badge badge-lead">🔹 Lead</span>',
            prospect: '<span class="badge badge-prospect">📙 Prospecto</span>',
            active: '<span class="badge badge-active">🟢 Activo</span>',
            inactive: '<span class="badge badge-inactive">⚪ Inactivo</span>'
        };
        return map[status] || '<span class="badge badge-lead">Lead</span>';
    }

    function getInitials(name) {
        if (!name) return 'CL';
        const parts = name.trim().split(' ');
        return parts.length >= 2
            ? (parts[0][0] + parts[1][0]).toUpperCase()
            : name.substring(0, 2).toUpperCase();
    }

    function numberFormat(num) {
        return new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr.replace(/-/g, '/'));
        if (isNaN(d.getTime())) return dateStr;
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = String(d.getFullYear()).slice(-2);
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }

    function svgIcon(pathData) {
        return `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="${pathData}"/></svg>`;
    }

    function setLoadingBtn(btn, loading) {
        if (!btn) return;
        if (loading) {
            if (!btn.dataset.origHtml) {
                btn.dataset.origHtml = btn.innerHTML;
            }
            btn.disabled = true;
            btn.style.opacity = '0.75';
            btn.style.cursor = 'wait';
            btn.innerHTML = `<span style="display:inline-block;width:13px;height:13px;border:2px solid currentColor;border-right-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite;vertical-align:-2px;margin-right:6px;"></span>Guardando...`;
        } else {
            btn.disabled = false;
            btn.style.opacity = '';
            btn.style.cursor = '';
            if (btn.dataset.origHtml) {
                btn.innerHTML = btn.dataset.origHtml;
            }
        }
    }

    function h(str) {
        if (str == null) return '';
        return String(str).replace(/[&<>'"]/g,
            t => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[t] || t));
    }

});
