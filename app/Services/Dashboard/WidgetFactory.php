<?php

declare(strict_types=1);

namespace AgroPCC\Services\Dashboard;

final class WidgetFactory
{
    public function createKpi(string $id, string $label, mixed $value, string $unit = '', array $metadata = [], string $permission = '', string $module = ''): array
    {
        return [
            'id' => $id,
            'type' => 'kpi',
            'title' => $label,
            'value' => $value,
            'unit' => $unit,
            'metadata' => $metadata,
            'permission' => $permission,
            'module' => $module,
        ];
    }

    public function createTable(string $id, string $title, array $columns, array $rows, array $metadata = [], string $permission = '', string $module = ''): array
    {
        return [
            'id' => $id,
            'type' => 'table',
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'metadata' => $metadata,
            'permission' => $permission,
            'module' => $module,
        ];
    }

    public function createChart(string $id, string $title, array $chart, array $metadata = [], string $permission = '', string $module = ''): array
    {
        return [
            'id' => $id,
            'type' => 'chart',
            'title' => $title,
            'chart' => $chart,
            'metadata' => $metadata,
            'permission' => $permission,
            'module' => $module,
        ];
    }

    public function createAlert(string $id, string $title, string $severity, int $count = 0, array $metadata = [], string $permission = '', string $module = ''): array
    {
        return [
            'id' => $id,
            'type' => 'alert',
            'title' => $title,
            'severity' => $severity,
            'count' => $count,
            'metadata' => $metadata,
            'permission' => $permission,
            'module' => $module,
        ];
    }

    public function createQuickLink(string $id, string $title, string $route, string $icon = '', array $metadata = [], string $permission = '', string $module = ''): array
    {
        return [
            'id' => $id,
            'type' => 'quick_link',
            'title' => $title,
            'route' => $route,
            'icon' => $icon,
            'metadata' => $metadata,
            'permission' => $permission,
            'module' => $module,
        ];
    }

    public function fromSummary(array $summary, ChartProvider $chartProvider): array
    {
        $widgets = [];

        foreach (array_slice($summary['kpis'] ?? [], 0, 4) as $index => $kpi) {
            $widgets[] = $this->createKpi(
                'kpi-' . ($kpi['key'] ?? $index),
                (string) ($kpi['label'] ?? 'Indicador'),
                $kpi['value'] ?? 0,
                (string) ($kpi['unit'] ?? ''),
                ['note' => $kpi['note'] ?? ($kpi['detail'] ?? ''), 'trend' => $kpi['trend'] ?? '', 'detail' => $kpi['detail'] ?? ''],
                $kpi['permission'] ?? '',
                $kpi['module'] ?? ''
            );
        }

        if (!empty($summary['cost_series'])) {
            $labels = array_column($summary['cost_series'], 'period');
            $values = array_map(static fn (array $row) => (float) ($row['value'] ?? 0), $summary['cost_series']);
            $widgets[] = $this->createChart(
                'cost-trend',
                'Evolución de costos',
                $chartProvider->bar($labels, $values, ['label' => 'Costos']),
                [],
                'costs.view',
                'costs'
            );
        }

        if (!empty($summary['production_series'])) {
            $labels = array_column($summary['production_series'], 'period');
            $values = array_map(static fn (array $row) => (float) ($row['value'] ?? 0), $summary['production_series']);
            $widgets[] = $this->createChart(
                'production-trend',
                'Evolución de producción',
                $chartProvider->line($labels, $values, ['label' => 'Producción']),
                [],
                'production.view',
                'production'
            );
        }

        if (!empty($summary['alerts'])) {
            foreach (($summary['alerts'] ?? []) as $alertIndex => $alert) {
                $widgets[] = $this->createAlert(
                    'alert-' . $alertIndex,
                    (string) ($alert['title'] ?? 'Alerta'),
                    ($alert['severity'] ?? 'warning'),
                    (int) ($alert['count'] ?? 0),
                    ['link' => $alert['link'] ?? ''],
                    $alert['permission'] ?? '',
                    $alert['module'] ?? ''
                );
            }
        }

        return $widgets;
    }
}
