(function () {
    'use strict';

    const apiEndpoint = 'index.php?module=dashboard_data';
    const state = {
        charts: {
            productionBudget: null,
            trend: null,
            costProcess: null,
        },
    };

    const numberFormatter = new Intl.NumberFormat('es-CL', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });

    const currencyFormatter = new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });

    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 1200,
            easing: 'easeOutQuart',
        },
        interaction: {
            mode: 'index',
            intersect: false,
        },
        hover: {
            mode: 'nearest',
            intersect: true,
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 12,
                    boxHeight: 12,
                    padding: 14,
                    usePointStyle: true,
                },
            },
            tooltip: {
                enabled: true,
                backgroundColor: 'rgba(20, 27, 34, 0.92)',
                titleColor: '#ffffff',
                bodyColor: '#f5f7f9',
                borderColor: 'rgba(255,255,255,0.08)',
                borderWidth: 1,
                padding: 10,
                caretSize: 6,
                cornerRadius: 8,
                displayColors: true,
            },
        },
        layout: {
            padding: {
                top: 10,
                right: 10,
                bottom: 10,
                left: 10,
            },
        },
    };

    function normalizeSeries(series) {
        if (!Array.isArray(series)) {
            return [];
        }
        return series.map((row, index) => ({
            period: String(row?.period ?? row?.label ?? row?.name ?? `Periodo ${index + 1}`),
            value: Number(row?.value ?? row?.total ?? row?.amount ?? row?.y ?? 0),
        }));
    }

    function normalizeCostProcess(rows) {
        if (!Array.isArray(rows)) {
            return [];
        }
        return rows.map((row, index) => ({
            process: String(row?.process ?? row?.category ?? row?.label ?? `Proceso ${index + 1}`),
            total: Number(row?.total ?? row?.value ?? row?.amount ?? 0),
        }));
    }

    function safeParseJson(raw) {
        try {
            return JSON.parse(raw);
        } catch (error) {
            return null;
        }
    }

    function buildProductionBudgetData(data) {
        const productionSeries = Array.isArray(data.production_series) ? data.production_series : [];
        const budget = data.budget || {};

        const labels = productionSeries.map(row => row.period || '');
        const productionValues = productionSeries.map(row => Number(row.value || 0));
        const budgetValues = productionSeries.map(() => Number(budget.planned || 0));

        return {
            labels,
            datasets: [
                {
                    label: 'Producción real',
                    data: productionValues,
                    backgroundColor: 'rgba(43, 122, 75, 0.5)',
                    borderColor: '#2b7a4b',
                    borderWidth: 1,
                },
                {
                    label: 'Presupuesto planificado',
                    data: budgetValues,
                    backgroundColor: 'rgba(122, 139, 154, 0.4)',
                    borderColor: '#7a8b9a',
                    borderWidth: 1,
                },
            ],
        };
    }

    function buildTrendData(data) {
        const productionSeries = Array.isArray(data.production_series) ? data.production_series : [];
        const costSeries = Array.isArray(data.cost_series) ? data.cost_series : [];

        const labels = productionSeries.length >= costSeries.length ? productionSeries.map(row => row.period || '') : costSeries.map(row => row.period || '');
        const productionValues = productionSeries.map(row => Number(row.value || 0));
        const costValues = costSeries.map(row => Number(row.value || 0));

        return {
            labels,
            datasets: [
                {
                    label: 'Producción',
                    data: productionValues,
                    borderColor: '#1f7a4d',
                    backgroundColor: 'rgba(31, 122, 77, 0.12)',
                    fill: true,
                    tension: 0.32,
                    pointRadius: 3,
                },
                {
                    label: 'Costos',
                    data: costValues,
                    borderColor: '#b55a34',
                    backgroundColor: 'rgba(181, 90, 52, 0.16)',
                    fill: true,
                    tension: 0.32,
                    pointRadius: 3,
                },
            ],
        };
    }

    function buildCostProcessData(data) {
        const rows = Array.isArray(data.cost_by_process) ? data.cost_by_process : [];
        return {
            labels: rows.map(row => row.process || row.category || 'Sin dato'),
            datasets: [
                {
                    label: 'Costos por proceso',
                    data: rows.map(row => Number(row.total || row.value || 0)),
                    backgroundColor: rows.map(() => 'rgba(64, 121, 180, 0.72)'),
                    borderColor: rows.map(() => '#315e7c'),
                    borderWidth: 1,
                },
            ],
        };
    }

    function destroyChart(chart) {
        if (chart && typeof chart.destroy === 'function') {
            chart.destroy();
        }
    }

    function createChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        console.log('DOM CHECK', canvasId, canvas);
        if (!(canvas instanceof HTMLCanvasElement)) {
            console.warn(`Canvas not found: ${canvasId}`);
            return null;
        }

        console.log(`Creating chart on ${canvasId}`, config);
        const chart = new Chart(canvas, config);
        console.log('Chart instance', canvasId, chart);
        return chart;
    }

    function renderProductionBudgetChart(data) {
        console.log('PRODUCTION DATA', data);
        destroyChart(state.charts.productionBudget);
        const series = normalizeSeries(data.production_series);
        const budget = Number(data.budget?.planned || 0);
        const labels = series.map(row => row.period);
        const productionValues = series.map(row => row.value);
        const budgetValues = series.map(() => budget);

        const config = {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Producción real',
                        data: productionValues,
                        backgroundColor: 'rgba(40, 121, 72, 0.92)',
                        borderColor: 'rgba(40, 121, 72, 1)',
                        borderWidth: 1,
                        borderRadius: 10,
                        barPercentage: 0.55,
                        categoryPercentage: 0.75,
                        maxBarThickness: 42,
                    },
                    {
                        label: 'Presupuesto planificado',
                        data: budgetValues,
                        backgroundColor: 'rgba(99, 118, 138, 0.24)',
                        borderColor: 'rgba(99, 118, 138, 0.72)',
                        borderWidth: 1,
                        borderRadius: 10,
                        barPercentage: 0.55,
                        categoryPercentage: 0.75,
                        maxBarThickness: 42,
                    },
                ],
            },
            options: {
                ...chartDefaults,
                plugins: {
                    ...chartDefaults.plugins,
                    legend: {
                        ...chartDefaults.plugins.legend,
                        position: 'top',
                    },
                    tooltip: {
                        ...chartDefaults.plugins.tooltip,
                        callbacks: {
                            title: items => items.map(item => item.label).join(' / '),
                            label: context => `${context.dataset.label}: ${currencyFormatter.format(context.parsed.y ?? 0)}`,
                        },
                    },
                    afterDatasetsDraw: false,
                },
                scales: {
                    x: {
                        grid: {display: false},
                        ticks: {color: '#6b7280'},
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => numberFormatter.format(value),
                            color: '#6b7280',
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.16)',
                        },
                    },
                },
            },
            plugins: [
                {
                    id: 'barValueLabels',
                    afterDatasetsDraw(chart) {
                        const ctx = chart.ctx;
                        chart.data.datasets.forEach((dataset, datasetIndex) => {
                            const meta = chart.getDatasetMeta(datasetIndex);
                            meta.data.forEach((element, index) => {
                                const value = dataset.data[index] ?? 0;
                                const position = element.tooltipPosition();
                                ctx.save();
                                ctx.fillStyle = '#102a43';
                                ctx.font = '600 11px Inter, system-ui, sans-serif';
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'bottom';
                                ctx.fillText(currencyFormatter.format(value), position.x, position.y - 6);
                                ctx.restore();
                            });
                        });
                    },
                },
            ],
        };

        console.log('renderProductionBudgetChart data', { labels, productionValues, budgetValues, budget });
        state.charts.productionBudget = createChart('productionBudgetChart', config);
    }

    function renderTrendChart(data) {
        console.log('TREND DATA', data);
        destroyChart(state.charts.trend);
        const seriesProduction = normalizeSeries(data.production_series);
        const seriesCost = normalizeSeries(data.cost_series);
        const labels = seriesProduction.length >= seriesCost.length ? seriesProduction.map(row => row.period) : seriesCost.map(row => row.period);
        const productionValues = seriesProduction.map(row => row.value);
        const costValues = seriesCost.map(row => row.value);

        const config = {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Producción',
                        data: productionValues,
                        borderColor: 'rgba(22, 163, 74, 0.95)',
                        backgroundColor: 'rgba(22, 163, 74, 0.18)',
                        fill: true,
                        tension: 0.38,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: 'rgba(22, 163, 74, 1)',
                        borderWidth: 3,
                    },
                    {
                        label: 'Costos',
                        data: costValues,
                        borderColor: 'rgba(219, 68, 55, 0.95)',
                        backgroundColor: 'rgba(219, 68, 55, 0.16)',
                        fill: true,
                        tension: 0.38,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: 'rgba(219, 68, 55, 1)',
                        borderWidth: 3,
                    },
                ],
            },
            options: {
                ...chartDefaults,
                plugins: {
                    ...chartDefaults.plugins,
                    tooltip: {
                        ...chartDefaults.plugins.tooltip,
                        callbacks: {
                            title: items => items[0]?.label || '',
                            label: context => `${context.dataset.label}: ${numberFormatter.format(context.parsed.y ?? 0)} ${context.dataset.label === 'Costos' ? 'CLP' : 'kg'}`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {display: false},
                        ticks: {color: '#6b7280'},
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => numberFormatter.format(value),
                            color: '#6b7280',
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.16)',
                        },
                    },
                },
            },
        };

        console.log('renderTrendChart data', { labels, productionValues, costValues });
        state.charts.trend = createChart('trendChart', config);
    }

    function renderCostProcessChart(data) {
        console.log('COST DATA', data);
        destroyChart(state.charts.costProcess);
        const rows = normalizeCostProcess(data.cost_by_process);
        const labels = rows.map(row => row.process);
        const values = rows.map(row => row.total);
        const total = values.reduce((sum, value) => sum + value, 0);
        const colors = rows.map((_, index) => index % 2 === 0 ? 'rgba(30, 64, 175, 0.85)' : 'rgba(56, 189, 248, 0.82)');

        const config = {
            type: 'doughnut',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Costos por proceso',
                        data: values,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                ...chartDefaults,
                cutout: '60%',
                plugins: {
                    ...chartDefaults.plugins,
                    legend: {
                        ...chartDefaults.plugins.legend,
                        position: 'right',
                        labels: {
                            ...chartDefaults.plugins.legend.labels,
                            padding: 10,
                        },
                    },
                    tooltip: {
                        ...chartDefaults.plugins.tooltip,
                        callbacks: {
                            label: context => {
                                const value = context.parsed || 0;
                                const percentage = total > 0 ? `${((value / total) * 100).toFixed(1)}%` : '0%';
                                return `${context.label}: ${currencyFormatter.format(value)} · ${percentage}`;
                            },
                        },
                    },
                },
            },
            plugins: [
                {
                    id: 'doughnutCenterText',
                    afterDraw(chart) {
                        const ctx = chart.ctx;
                        const width = chart.width;
                        const height = chart.height;
                        ctx.save();
                        ctx.font = '600 18px Inter, system-ui, sans-serif';
                        ctx.fillStyle = '#0f172a';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText('Total', width / 2, height / 2 - 12);
                        ctx.font = '700 22px Inter, system-ui, sans-serif';
                        ctx.fillText(currencyFormatter.format(total), width / 2, height / 2 + 14);
                        ctx.restore();
                    },
                },
            ],
        };

        console.log('renderCostProcessChart data', { labels, values, total });
        state.charts.costProcess = createChart('costProcessChart', config);
    }

    function updateSummaryMetrics(data) {
        const productionTotal = document.getElementById('kpi-production-total');
        const totalCost = document.getElementById('kpi-total-cost');
        const budgetExecuted = document.getElementById('kpi-budget-executed');

        if (productionTotal) {
            const value = Number((data.metrics || {}).production || 0);
            productionTotal.textContent = `${numberFormatter.format(value)} kg`;
        }
        if (totalCost) {
            totalCost.textContent = currencyFormatter.format(Number((data.totals || {}).total_cost || 0));
        }
        if (budgetExecuted) {
            const value = Number((data.budget || {}).execution || 0);
            budgetExecuted.textContent = `${value.toFixed(1)}%`;
        }
    }

    function updateFilterSummary(data) {
        const selectedProcess = document.getElementById('selected-process');
        const selectedPeriod = document.getElementById('selected-period');

        const filters = data.filters || {};
        if (selectedProcess) {
            selectedProcess.textContent = filters.process || 'Todos';
        }
        if (selectedPeriod) {
            selectedPeriod.textContent = `${filters.date_from || ''} – ${filters.date_to || ''}`.trim();
        }
    }

    async function fetchDashboardData(queryString = '') {
        const response = await fetch(`${apiEndpoint}${queryString ? '&' + queryString : ''}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {'Accept': 'application/json'},
        });

        if (!response.ok) {
            throw new Error('No se pudieron cargar los datos del dashboard.');
        }

        return await response.json();
    }

    function serializeForm(formElement) {
        const formData = new FormData(formElement);
        return new URLSearchParams(formData).toString();
    }

    function setLoading(visible) {
        const loading = document.getElementById('dashboard-loading');
        if (loading) {
            loading.hidden = !visible;
        }
    }

    function getSelectedOptionText(name) {
        const element = document.querySelector(`#dashboard-filter-form [name="${name}"]`);
        if (element instanceof HTMLSelectElement) {
            return element.options[element.selectedIndex]?.text || 'Todos';
        }
        return element instanceof HTMLInputElement ? element.value : 'Todos';
    }

    async function refreshDashboard(event) {
        if (event) {
            event.preventDefault();
        }

        const form = document.getElementById('dashboard-filter-form');
        if (!form) {
            return;
        }

        setLoading(true);

        try {
            const query = serializeForm(form);
            const data = await fetchDashboardData(query);

            renderProductionBudgetChart(data);
            renderTrendChart(data);
            renderCostProcessChart(data);
            updateSummaryMetrics(data);
            updateFilterSummary(data);

            const selectedFarm = document.getElementById('selected-farm');
            const selectedBlock = document.getElementById('selected-block');
            if (selectedFarm) {
                selectedFarm.textContent = getSelectedOptionText('farm_id');
            }
            if (selectedBlock) {
                selectedBlock.textContent = getSelectedOptionText('block_id');
            }
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    }

    function init() {
        const initialData = window.dashboardData || {};
        console.info('dashboard.js initialized', {
            hasData: Object.keys(initialData).length > 0,
            productionSeries: Array.isArray(initialData.production_series) ? initialData.production_series.length : 0,
            costSeries: Array.isArray(initialData.cost_series) ? initialData.cost_series.length : 0,
            costByProcess: Array.isArray(initialData.cost_by_process) ? initialData.cost_by_process.length : 0,
        });
        renderProductionBudgetChart(initialData);
        renderTrendChart(initialData);
        renderCostProcessChart(initialData);
        updateSummaryMetrics(initialData);
        updateFilterSummary(initialData);

        const filterForm = document.getElementById('dashboard-filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', refreshDashboard);
            filterForm.querySelectorAll('input, select').forEach((input) => input.addEventListener('change', refreshDashboard));
        }
    }

    document.addEventListener('DOMContentLoaded', init);
})();