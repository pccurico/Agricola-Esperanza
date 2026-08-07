(() => {
    'use strict';

    const data = window.managementIntelligenceData || {};
    const colors = {
        green: '#166534',
        teal: '#0f766e',
        blue: '#2563eb',
        red: '#dc2626',
        grid: 'rgba(148, 163, 184, 0.16)',
        text: '#475569',
    };

    const rows = (items, labelKey, valueKey) => Array.isArray(items) ? items.map(item => ({
        label: String(item[labelKey] ?? item.label ?? '—'),
        value: Number(item[valueKey] ?? item.total ?? item.value ?? 0),
    })) : [];

    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {legend: {labels: {color: colors.text}}, tooltip: {enabled: true}},
        scales: {x: {ticks: {color: colors.text}, grid: {display: false}}, y: {beginAtZero: true, ticks: {color: colors.text}, grid: {color: colors.grid}}},
    };

    function render() {
        if (typeof Chart === 'undefined') return;
        const processRows = rows(data.processes, 'process', 'total').slice(0, 10);
        const productionRows = rows(data.production_series, 'period', 'value');
        const costRows = rows(data.cost_series, 'period', 'value');
        const trendCanvas = document.getElementById('managementTrendChart');
        const processCanvas = document.getElementById('managementProcessChart');

        if (trendCanvas && (productionRows.length > 0 || costRows.length > 0)) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {labels: costRows.map(row => row.label), datasets: [
                    {label: 'Costos', data: costRows.map(row => row.value), borderColor: colors.red, backgroundColor: 'rgba(220, 38, 38, .12)', fill: true, tension: .35},
                    {label: 'Producción', data: productionRows.map(row => row.value), borderColor: colors.teal, backgroundColor: 'rgba(15, 118, 110, .12)', fill: true, tension: .35},
                ]},
                options: baseOptions,
            });
        } else if (trendCanvas) {
            trendCanvas.closest('.management-chart-card').hidden = true;
        }

        if (processCanvas && processRows.length > 0) {
            new Chart(processCanvas, {
                type: 'bar',
                data: {labels: processRows.map(row => row.label), datasets: [{label: 'Costo total', data: processRows.map(row => row.value), backgroundColor: colors.green, borderRadius: 6}]},
                options: {...baseOptions, indexAxis: 'y'},
            });
        } else if (processCanvas) {
            processCanvas.closest('.management-process-card').hidden = true;
        }

        const categoryRows = rows(data.categories, 'category', 'total').slice(0, 8);
        const farmRows = rows(data.farms, 'name', 'total').slice(0, 8);
        const workerRows = rows(data.workers, 'full_name', 'quantity').slice(0, 8);
        const comparisons = Array.isArray(data.comparisons) ? data.comparisons : [];
        const renderOptional = (id, hasData, config) => {
            const canvas = document.getElementById(id);
            const card = canvas?.closest('.management-chart-card');
            if (!canvas || !card) return;
            card.hidden = !hasData;
            if (hasData) new Chart(canvas, config);
        };

        renderOptional('managementCategoryChart', categoryRows.length > 0, {
            type: 'doughnut',
            data: {labels: categoryRows.map(row => row.label), datasets: [{data: categoryRows.map(row => row.value), backgroundColor: ['#166534', '#15803d', '#16a34a', '#22c55e', '#65a30d', '#84cc16', '#a3e635', '#bef264'], borderColor: '#fff', borderWidth: 2}]},
            options: {...baseOptions, cutout: '58%', plugins: {...baseOptions.plugins, legend: {position: 'right', labels: {color: colors.text}}}},
        });
        renderOptional('managementFarmChart', farmRows.length > 0, {
            type: 'bar',
            data: {labels: farmRows.map(row => row.label), datasets: [{label: 'Costo total', data: farmRows.map(row => row.value), backgroundColor: colors.teal, borderRadius: 6}]},
            options: baseOptions,
        });
        renderOptional('managementComparisonChart', comparisons.length > 0, {
            type: 'bar',
            data: {labels: comparisons.map(row => row.label), datasets: [
                {label: 'Costos', data: comparisons.map(row => Number(row.metrics?.cost ?? 0)), backgroundColor: colors.red},
                {label: 'Producción', data: comparisons.map(row => Number(row.metrics?.production ?? 0)), backgroundColor: colors.blue},
            ]},
            options: baseOptions,
        });
    }

    function enableFilters() {
        const form = document.querySelector('[data-management-filter]');
        if (!form) return;
        form.addEventListener('change', event => {
            if (event.target instanceof HTMLSelectElement) {
                form.requestSubmit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        render();
        enableFilters();
    });
})();
