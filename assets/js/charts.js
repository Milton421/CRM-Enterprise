let statusChartInstance = null;
let industryChartInstance = null;
let activityChartInstance = null;

function renderCRMCharts(stats) {
    if (typeof Chart === 'undefined') return;
    Chart.defaults.font.family = "'Nunito Sans', -apple-system, BlinkMacSystemFont, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#4b5563';

    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        if (statusChartInstance) statusChartInstance.destroy();

        const statusCounts = stats.status_counts || {};
        statusChartInstance = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Leads', 'Prospectos', 'Activos', 'Inactivos'],
                datasets: [{
                    data: [
                        statusCounts.lead || 0,
                        statusCounts.prospect || 0,
                        statusCounts.active || 0,
                        statusCounts.inactive || 0
                    ],
                    backgroundColor: ['#38bdf8', '#f59e0b', '#10b981', '#94a3b8'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#4b5563',
                            font: { family: "'Nunito Sans', sans-serif", size: 11, weight: '700' },
                            padding: 14,
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleColor: '#f9fafb',
                        bodyColor: '#e5e7eb',
                        borderColor: '#374151',
                        borderWidth: 1,
                        padding: 10,
                        titleFont: { family: "'Nunito Sans', sans-serif", weight: '700' },
                        bodyFont: { family: "'Nunito Sans', sans-serif" }
                    }
                },
                cutout: '72%'
            }
        });
    }

    const industryCtx = document.getElementById('industryChart');
    if (industryCtx) {
        if (industryChartInstance) industryChartInstance.destroy();

        const industries = stats.industry_counts || [];
        const labels = industries.map(i => i.name);
        const dataValues = industries.map(i => i.value);

        industryChartInstance = new Chart(industryCtx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['General'],
                datasets: [{
                    label: 'Pipeline (Q)',
                    data: dataValues.length ? dataValues : [0],
                    backgroundColor: 'rgba(124, 58, 237, 0.75)',
                    borderColor: '#7c3aed',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#4b5563',
                            font: { family: "'Nunito Sans', sans-serif", size: 10, weight: '600' },
                            maxRotation: 25,
                            minRotation: 0
                        }
                    },
                    y: {
                        grid: { color: '#f3f4f6' },
                        ticks: {
                            color: '#4b5563',
                            font: { family: "'Nunito Sans', sans-serif", size: 10, weight: '600' },
                            callback: function(val) {
                                if (val >= 1000) return 'Q' + (val / 1000) + 'k';
                                return 'Q' + val;
                            }
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleFont: { family: "'Nunito Sans', sans-serif", weight: '700' },
                        bodyFont: { family: "'Nunito Sans', sans-serif" },
                        callbacks: {
                            label: function(ctx) {
                                return 'Pipeline: Q' + ctx.parsed.y.toLocaleString('es-GT', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        });
    }

    const activityCtx = document.getElementById('activityChart');
    if (activityCtx) {
        if (activityChartInstance) activityChartInstance.destroy();

        const types = stats.type_counts || {};
        activityChartInstance = new Chart(activityCtx, {
            type: 'polarArea',
            data: {
                labels: ['Llamadas 📞', 'Reuniones 🤝', 'Correos ✉️', 'Notas 📝', 'Tareas 📌'],
                datasets: [{
                    data: [
                        types.call || 0,
                        types.meeting || 0,
                        types.email || 0,
                        types.note || 0,
                        types.task || 0
                    ],
                    backgroundColor: [
                        'rgba(56, 189, 248, 0.75)',
                        'rgba(124, 58, 237, 0.75)',
                        'rgba(245, 158, 11, 0.75)',
                        'rgba(168, 85, 247, 0.75)',
                        'rgba(239, 68, 68, 0.75)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#4b5563',
                            font: { family: "'Nunito Sans', sans-serif", size: 10, weight: '700' },
                            usePointStyle: true,
                            padding: 10
                        }
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleFont: { family: "'Nunito Sans', sans-serif", weight: '700' },
                        bodyFont: { family: "'Nunito Sans', sans-serif" }
                    }
                },
                scales: {
                    r: { grid: { color: '#e5e7eb' }, ticks: { display: false } }
                }
            }
        });
    }
}
