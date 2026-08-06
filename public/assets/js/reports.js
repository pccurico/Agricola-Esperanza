(function () {
    'use strict';

    const chartDefaults = {
        type: 'bar',
        data: {labels: [], datasets: []},
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {color: '#334155', padding: 16},
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#ffffff',
                    bodyColor: '#f8fafc',
                    borderColor: 'rgba(148, 163, 184, 0.24)',
                    borderWidth: 1,
                },
            },
            scales: {
                x: {
                    ticks: {color: '#475569'},
                    grid: {display: false},
                },
                y: {
                    beginAtZero: true,
                    ticks: {color: '#475569'},
                    grid: {color: 'rgba(148, 163, 184, 0.16)'},
                },
            },
        },
    };

    const currencyFormatter = new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        maximumFractionDigits: 0,
    });

    const numberFormatter = new Intl.NumberFormat('es-CL', {
        maximumFractionDigits: 2,
    });

    function getCanvas() {
        return document.getElementById('reportOverviewChart');
    }

    function createChart(canvas, config) {
        if (!canvas || typeof Chart === 'undefined') {
            return null;
        }
        return new Chart(canvas, config);
    }

    function normalizeRows(rows, labelKey, valueKey) {
        return Array.isArray(rows) ? rows.map(row => ({
            label: String(row[labelKey] ?? row.label ?? '—'),
            value: Number(row[valueKey] ?? row.total ?? row.value ?? 0),
        })) : [];
    }

    function buildSeries(series, labelKey = 'period', valueKey = 'value') {
        const rows = normalizeRows(series, labelKey, valueKey);
        return {
            labels: rows.map(row => row.label),
            values: rows.map(row => row.value),
        };
    }

    function renderEmptyChart(canvas) {
        return createChart(canvas, {
            ...chartDefaults,
            data: {labels: ['Sin datos'], datasets: [{label: 'Sin datos', data: [0], backgroundColor: ['rgba(148, 163, 184, 0.32)'], borderColor: ['rgba(148, 163, 184, 0.72)'], borderWidth: 1}]},
            options: {
                ...chartDefaults.options,
                plugins: {
                    ...chartDefaults.options.plugins,
                    legend: {display: false},
                    tooltip: {enabled: false},
                },
            },
        });
    }

    function renderExecutiveChart(data, canvas) {
        const rows = Array.isArray(data.comparisons?.periods) ? data.comparisons.periods : [];
        if (rows.length === 0) {
            return renderEmptyChart(canvas);
        }

        const labels = rows.map(row => row.label);
        const production = rows.map(row => Number(row.metrics?.production ?? 0));
        const costs = rows.map(row => Number(row.metrics?.cost ?? 0));

        return createChart(canvas, {
            ...chartDefaults,
            data: {
                labels,
                datasets: [
                    {
                        label: 'Producción',
                        data: production,
                        borderColor: 'rgba(34, 197, 94, 0.9)',
                        backgroundColor: 'rgba(34, 197, 94, 0.2)',
                        type: 'line',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 4,
                    },
                    {
                        label: 'Costos',
                        data: costs,
                        backgroundColor: 'rgba(239, 68, 68, 0.72)',
                        borderColor: 'rgba(220, 38, 38, 0.9)',
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                ...chartDefaults.options,
                scales: {
                    x: chartDefaults.options.scales.x,
                    y: {
                        ...chartDefaults.options.scales.y,
                        ticks: {
                            callback: value => currencyFormatter.format(Number(value)),
                            color: '#475569',
                        },
                    },
                },
                plugins: {
                    ...chartDefaults.options.plugins,
                    tooltip: {
                        ...chartDefaults.options.plugins.tooltip,
                        callbacks: {
                            label: context => {
                                const label = context.dataset.label || '';
                                const value = Number(context.parsed.y ?? 0);
                                return `${label}: ${label === 'Costos' ? currencyFormatter.format(value) : numberFormatter.format(value)}`;
                            },
                        },
                    },
                },
            },
        });
    }

    function renderProductionChart(data, canvas) {
        const series = buildSeries(data.trends?.production ?? [], 'period', 'value');
        if (series.labels.length === 0) {
            const blocks = normalizeRows(data.blocks ?? [], 'block_name', 'quantity').slice(0, 6);
            if (blocks.length === 0) {
                return renderEmptyChart(canvas);
            }
            return createChart(canvas, {
                ...chartDefaults,
                data: {labels: blocks.map(row => row.label), datasets: [{label: 'Producción por cuartel', data: blocks.map(row => row.value), backgroundColor: 'rgba(34, 197, 94, 0.8)', borderColor: 'rgba(22, 163, 74, 1)', borderWidth: 1}]},
            });
        }
        return createChart(canvas, {
            ...chartDefaults,
            data: {labels: series.labels, datasets: [{label: 'Producción', data: series.values, borderColor: 'rgba(22, 163, 74, 0.95)', backgroundColor: 'rgba(34, 197, 94, 0.28)', type: 'line', tension: 0.35, fill: true, pointRadius: 4}]},
            options: {
                ...chartDefaults.options,
                scales: chartDefaults.options.scales,
                plugins: chartDefaults.options.plugins,
            },
        });
    }

    function renderCostsChart(data, canvas) {
        const categories = normalizeRows(data.categories ?? [], 'category', 'total');
        if (categories.length === 0) {
            return renderEmptyChart(canvas);
        }
        const top = categories.slice(0, 8);
        const colors = top.map((_, index) => index % 2 === 0 ? 'rgba(59, 130, 246, 0.8)' : 'rgba(37, 99, 235, 0.8)');
        return createChart(canvas, {
            type: 'doughnut',
            data: {labels: top.map(row => row.label), datasets: [{data: top.map(row => row.value), backgroundColor: colors, borderColor: '#ffffff', borderWidth: 2}]},
            options: {
                ...chartDefaults.options,
                cutout: '60%',
                plugins: {
                    ...chartDefaults.options.plugins,
                    legend: {position: 'right', labels: {color: '#475569', boxWidth: 14, padding: 12}},
                    tooltip: {
                        ...chartDefaults.options.plugins.tooltip,
                        callbacks: {
                            label: context => `${context.label}: ${currencyFormatter.format(context.parsed || 0)}`,
                        },
                    },
                },
            },
        });
    }

    function renderLaborChart(data, canvas) {
        const workers = normalizeRows(data.workers ?? [], 'full_name', 'quantity').slice(0, 8);
        if (workers.length === 0) {
            return renderEmptyChart(canvas);
        }
        return createChart(canvas, {
            ...chartDefaults,
            data: {labels: workers.map(row => row.label), datasets: [{label: 'Jornadas', data: workers.map(row => row.value), backgroundColor: 'rgba(14, 165, 233, 0.8)', borderColor: 'rgba(14, 165, 233, 1)', borderWidth: 1}]},
            options: {
                ...chartDefaults.options,
                indexAxis: 'y',
                scales: {
                    x: {ticks: {color: '#475569'}, grid: {color: 'rgba(148, 163, 184, 0.16)'}},
                    y: {ticks: {color: '#475569'}, grid: {display: false}},
                },
            },
        });
    }

    function renderInventoryChart(data, canvas) {
        const alerts = normalizeRows(data.alerts ?? [], 'name', 'stock');
        if (alerts.length === 0) {
            return renderEmptyChart(canvas);
        }
        const top = alerts.slice(0, 8);
        return createChart(canvas, {
            ...chartDefaults,
            data: {labels: top.map(row => row.label), datasets: [{label: 'Stock', data: top.map(row => row.value), backgroundColor: 'rgba(251, 146, 60, 0.88)', borderColor: 'rgba(249, 115, 22, 1)', borderWidth: 1}]},
            options: chartDefaults.options,
        });
    }

    function renderProcurementChart(data, canvas) {
        const processes = normalizeRows(data.processes ?? [], 'process', 'total');
        if (processes.length === 0) {
            return renderEmptyChart(canvas);
        }
        return createChart(canvas, {
            ...chartDefaults,
            data: {labels: processes.map(row => row.label), datasets: [{label: 'Costo por proceso', data: processes.map(row => row.value), backgroundColor: 'rgba(107, 33, 168, 0.8)', borderColor: 'rgba(79, 70, 229, 1)', borderWidth: 1}]},
            options: chartDefaults.options,
        });
    }

    function renderFinanceChart(data, canvas) {
        const planned = Number(data.budget?.planned ?? 0);
        const actual = Number(data.budget?.actual ?? 0);
        if (planned === 0 && actual === 0) {
            return renderEmptyChart(canvas);
        }
        return createChart(canvas, {
            ...chartDefaults,
            data: {
                labels: ['Presupuesto plan.', 'Ejecutado'],
                datasets: [{label: 'CLP', data: [planned, actual], backgroundColor: ['rgba(14, 165, 233, 0.82)', 'rgba(34, 197, 94, 0.82)'], borderColor: ['rgba(14, 165, 233, 1)', 'rgba(22, 163, 74, 1)'], borderWidth: 1}],
            },
            options: {
                ...chartDefaults.options,
                scales: chartDefaults.options.scales,
                plugins: {
                    ...chartDefaults.options.plugins,
                    tooltip: {
                        ...chartDefaults.options.plugins.tooltip,
                        callbacks: {
                            label: context => `${context.label}: ${currencyFormatter.format(context.parsed.y ?? 0)}`,
                        },
                    },
                },
            },
        });
    }

    function renderBudgetsChart(data, canvas) {
        return renderFinanceChart(data, canvas);
    }

    function renderMachineryChart(data, canvas) {
        const blocks = normalizeRows(data.blocks ?? [], 'block_name', 'quantity').slice(0, 8);
        if (blocks.length === 0) {
            return renderEmptyChart(canvas);
        }
        return createChart(canvas, {
            ...chartDefaults,
            data: {labels: blocks.map(row => row.label), datasets: [{label: 'Horas estimadas', data: blocks.map(row => row.value), backgroundColor: 'rgba(239, 68, 68, 0.78)', borderColor: 'rgba(185, 28, 28, 1)', borderWidth: 1}]},
            options: chartDefaults.options,
        });
    }

    function renderProductivityChart(data, canvas) {
        const series = buildSeries(data.trends?.production ?? [], 'period', 'value');
        if (series.labels.length === 0) {
            return renderEmptyChart(canvas);
        }
        return createChart(canvas, {
            ...chartDefaults,
            data: {labels: series.labels, datasets: [{label: 'Producción', data: series.values, borderColor: 'rgba(20, 184, 166, 0.95)', backgroundColor: 'rgba(20, 184, 166, 0.2)', type: 'line', tension: 0.35, fill: true, pointRadius: 4}]},
            options: chartDefaults.options,
        });
    }

    function renderComparativesChart(data, canvas) {
        const rows = Array.isArray(data.comparisons?.periods) ? data.comparisons.periods : [];
        if (rows.length === 0) {
            return renderEmptyChart(canvas);
        }
        const labels = rows.map(row => row.label);
        const costs = rows.map(row => Number(row.metrics?.cost ?? 0));
        const production = rows.map(row => Number(row.metrics?.production ?? 0));
        return createChart(canvas, {
            ...chartDefaults,
            data: {labels, datasets: [{label: 'Costos', data: costs, backgroundColor: 'rgba(59, 130, 246, 0.8)', borderColor: 'rgba(37, 99, 235, 1)', borderWidth: 1}, {label: 'Producción', data: production, backgroundColor: 'rgba(52, 211, 153, 0.8)', borderColor: 'rgba(16, 185, 129, 1)', borderWidth: 1}]},
            options: chartDefaults.options,
        });
    }

    function renderTrendsChart(data, canvas) {
        const costSeries = buildSeries(data.trends?.costs ?? [], 'period', 'value');
        const productionSeries = buildSeries(data.trends?.production ?? [], 'period', 'value');
        const laborSeries = buildSeries(data.trends?.labor ?? [], 'period', 'value');
        if (costSeries.labels.length === 0 && productionSeries.labels.length === 0) {
            return renderEmptyChart(canvas);
        }
        const labels = costSeries.labels.length >= productionSeries.labels.length ? costSeries.labels : productionSeries.labels;
        return createChart(canvas, {
            ...chartDefaults,
            type: 'line',
            data: {
                labels,
                datasets: [
                    {label: 'Costos', data: costSeries.values, borderColor: 'rgba(239, 68, 68, 0.95)', backgroundColor: 'rgba(239, 68, 68, 0.18)', fill: true, tension: 0.35, pointRadius: 3},
                    {label: 'Producción', data: productionSeries.values, borderColor: 'rgba(34, 197, 94, 0.95)', backgroundColor: 'rgba(34, 197, 94, 0.18)', fill: true, tension: 0.35, pointRadius: 3},
                    {label: 'Labor', data: laborSeries.values, borderColor: 'rgba(37, 99, 235, 0.95)', backgroundColor: 'rgba(59, 130, 246, 0.18)', fill: true, tension: 0.35, pointRadius: 3},
                ],
            },
            options: chartDefaults.options,
        });
    }

    function renderKpisChart(data, canvas) {
        const rows = [
            {label: 'Alertas', value: data.alerts?.length ?? 0},
            {label: 'Centros', value: data.centers?.length ?? 0},
            {label: 'Trabajadores', value: data.workers?.length ?? 0},
            {label: 'Bloques', value: data.blocks?.length ?? 0},
        ].filter(row => row.value > 0);
        if (rows.length === 0) {
            return renderEmptyChart(canvas);
        }
        return createChart(canvas, {
            type: 'doughnut',
            data: {labels: rows.map(row => row.label), datasets: [{data: rows.map(row => row.value), backgroundColor: ['rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)', 'rgba(234, 179, 8, 0.8)', 'rgba(244, 63, 94, 0.8)'], borderColor: '#ffffff', borderWidth: 2}]},
            options: {
                ...chartDefaults.options,
                cutout: '60%',
                plugins: {
                    ...chartDefaults.options.plugins,
                    legend: {position: 'right', labels: {color: '#475569'}},
                },
            },
        });
    }

    function initializeReportVisuals() {
        document.querySelectorAll('.report-visual-bar[data-bar-height]').forEach(function (bar) {
            var height = Number(bar.dataset.barHeight || 0);
            if (!Number.isFinite(height)) {
                return;
            }
            bar.style.setProperty('--bar-height', Math.min(100, Math.max(0, height)) + '%');
        });

        document.querySelectorAll('.report-trend-row i[data-trend]').forEach(function (bar) {
            var value = Number(bar.dataset.trend || 0);
            if (!Number.isFinite(value)) {
                return;
            }
            bar.style.setProperty('--trend', Math.min(100, Math.max(0, value)) + '%');
        });
    }

    function destroyChart(chart) {
        if (chart && typeof chart.destroy === 'function') {
            chart.destroy();
        }
    }

    function renderChart() {
        const canvas = getCanvas();
        if (!canvas) {
            return;
        }
        const data = window.reportData || {};
        const reportType = String(data.report_type ?? 'executive');
        const handlers = {
            executive: renderExecutiveChart,
            production: renderProductionChart,
            costs: renderCostsChart,
            profitability: renderExecutiveChart,
            labor: renderLaborChart,
            inventory: renderInventoryChart,
            procurement: renderProcurementChart,
            finance: renderFinanceChart,
            budgets: renderBudgetsChart,
            machinery: renderMachineryChart,
            productivity: renderProductivityChart,
            comparatives: renderComparativesChart,
            trends: renderTrendsChart,
            kpis: renderKpisChart,
        };

        const handler = handlers[reportType] || renderEmptyChart;
        destroyChart(window.currentReportChart);
        window.currentReportChart = handler(data, canvas);
    }

    function formatCurrency(value) {
        return currencyFormatter.format(Number(value ?? 0));
    }

    function formatNumber(value, digits = 2) {
        return numberFormatter.format(Number(value ?? 0));
    }

    function updateSummaryCards() {
        const data = window.reportData || {};
        const summary = data.summary || {};
        const labor = data.labor_summary || {};
        const budget = data.budget || {};
        const alerts = Array.isArray(data.alerts) ? data.alerts : [];
        const processes = Array.isArray(data.processes) ? data.processes : [];
        const centers = Array.isArray(data.centers) ? data.centers : [];

        document.querySelectorAll('[data-summary-key]').forEach(node => {
            const key = node.getAttribute('data-summary-key');
            let value = '';
            switch (key) {
                case 'production':
                    value = formatNumber(summary.production, 2);
                    break;
                case 'cost_per_unit':
                    value = formatCurrency(summary.cost_per_unit);
                    break;
                case 'production_per_hectare':
                    value = formatNumber(summary.production_per_hectare, 2);
                    break;
                case 'jornadas':
                    value = formatNumber(labor.quantity, 2);
                    break;
                case 'total_cost':
                    value = formatCurrency(summary.total);
                    break;
                case 'centers':
                    value = centers.length;
                    break;
                case 'labor_cost':
                    value = formatCurrency(labor.total);
                    break;
                case 'workers':
                    value = Number(labor.workers ?? 0);
                    break;
                case 'productivity':
                    value = formatNumber(summary.labor_productivity, 2);
                    break;
                case 'alerts_critical':
                    value = Number(summary.alert_count ?? 0);
                    break;
                case 'stock_minimum':
                    value = alerts.length;
                    break;
                case 'total_value':
                    value = formatCurrency(summary.total);
                    break;
                case 'orders_open':
                    value = Number(summary.orders_open ?? 0);
                    break;
                case 'suppliers':
                    value = processes.length;
                    break;
                case 'budget':
                    value = formatCurrency(budget.planned ?? summary.total ?? 0);
                    break;
                case 'execution':
                    value = `${formatNumber(budget.execution, 1)}%`;
                    break;
                case 'profitability':
                    value = formatNumber(summary.profitability, 2);
                    break;
                default:
                    value = formatCurrency(summary[key] ?? 0);
                    break;
            }
            const valueEl = node.querySelector('strong');
            if (valueEl) {
                valueEl.textContent = value;
            }
        });
    }

    function updateActiveFilters(form) {
        const pillsContainer = document.querySelector('[data-active-filters]');
        if (!pillsContainer) {
            return;
        }
        const active = [];
        const fields = Array.from(form.querySelectorAll('select, input[type="date"]'));
        fields.forEach(field => {
            const value = String(field.value ?? '').trim();
            if (value === '' || value === '0') {
                return;
            }
            const label = field.closest('label')?.querySelector('span')?.textContent || field.name.replace(/_id$/, '').replace(/_/g, ' ');
            active.push(`${label}: ${value}`);
        });
        pillsContainer.innerHTML = active.map(text => `<span class="report-filter-pill">${text}</span>`).join('');
    }

    function queryFromForm(form) {
        const params = [];
        const formData = new FormData(form);
        formData.forEach((value, key) => {
            if (key === 'ajax') {
                return;
            }
            const normalized = String(value ?? '').trim();
            if (normalized === '' || normalized === '0') {
                return;
            }
            params.push([key, normalized]);
        });
        params.push(['ajax', '1']);
        return new URLSearchParams(params).toString();
    }

    function fetchReportData(form) {
        const url = `${window.location.pathname}?${queryFromForm(form)}`;
        return fetch(url, {headers: {'Accept': 'application/json'}}).then(response => {
            if (!response.ok) {
                throw new Error('No fue posible cargar los datos del informe');
            }
            return response.json();
        });
    }

    function applyDateShortcut(shortcut, form) {
        const now = new Date();
        let from = new Date();
        let to = new Date();
        switch (shortcut) {
            case 'last_7':
                from.setDate(now.getDate() - 6);
                break;
            case 'this_month':
                from = new Date(now.getFullYear(), now.getMonth(), 1);
                to = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                break;
            case 'last_month':
                from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                to = new Date(now.getFullYear(), now.getMonth(), 0);
                break;
            case 'ytd':
                from = new Date(now.getFullYear(), 0, 1);
                break;
            default:
                return;
        }
        const pad = n => String(n).padStart(2, '0');
        const fromInput = form.querySelector('input[name="from"]');
        const toInput = form.querySelector('input[name="to"]');
        if (fromInput) {
            fromInput.value = `${from.getFullYear()}-${pad(from.getMonth() + 1)}-${pad(from.getDate())}`;
        }
        if (toInput) {
            toInput.value = `${to.getFullYear()}-${pad(to.getMonth() + 1)}-${pad(to.getDate())}`;
        }
    }

    function enableFilterBar() {
        const form = document.querySelector('[data-report-form]');
        if (!form) {
            return;
        }
        const shortcuts = form.querySelectorAll('[data-shortcut]');
        const resetButton = form.querySelector('[data-filter-reset]');

        const refresh = () => {
            updateActiveFilters(form);
            fetchReportData(form).then(data => {
                window.reportData = data;
                updateSummaryCards();
                renderChart();
                const query = queryFromForm(form).replace(/&?ajax=1/, '');
                window.history.replaceState({}, '', `${window.location.pathname}?${query}`);
            }).catch(() => {
                // fail silently to keep UX smooth
            });
        };

        const debouncedRefresh = (() => {
            let timer = null;
            return () => {
                window.clearTimeout(timer);
                timer = window.setTimeout(refresh, 400);
            };
        })();

        form.addEventListener('change', event => {
            const target = event.target;
            if (target && (target.tagName === 'SELECT' || target.type === 'date')) {
                debouncedRefresh();
            }
        });

        form.addEventListener('submit', event => {
            event.preventDefault();
            refresh();
        });

        shortcuts.forEach(button => {
            button.addEventListener('click', () => {
                shortcuts.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                applyDateShortcut(button.getAttribute('data-shortcut'), form);
                debouncedRefresh();
            });
        });

        if (resetButton) {
            resetButton.addEventListener('click', () => {
                form.querySelectorAll('select').forEach(select => {
                    select.value = select.querySelector('option[value="0"]') ? '0' : '';
                });
                form.querySelectorAll('input[type="date"]').forEach(input => {
                    input.value = '';
                });
                shortcuts.forEach(btn => btn.classList.remove('active'));
                refresh();
            });
        }

        updateActiveFilters(form);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js no está disponible en esta página.');
            initializeReportVisuals();
            return;
        }
        initializeReportVisuals();
        renderChart();
        enableFilterBar();
    });
})();