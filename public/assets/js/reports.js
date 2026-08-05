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
        handler(data, canvas);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js no está disponible en esta página.');
            return;
        }
        renderChart();
    });
})();