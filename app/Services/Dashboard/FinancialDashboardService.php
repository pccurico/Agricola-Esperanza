<?php

declare(strict_types=1);

namespace CampoSur\Services\Dashboard;

final class FinancialDashboardService
{
    public function __construct(private readonly DashboardDataProviderInterface $dashboardService)
    {
    }

    public function data(array $filters, string $period, string $activeView, string $department, ?int $userId): array
    {
        $dashboard = $this->dashboardService->summary($period, null, $filters, $activeView, $department, $userId);

        return [
            'totals' => $dashboard['totals'] ?? [],
            'kpis' => $dashboard['kpis'] ?? [],
            'accounting' => $dashboard['sections']['accounting'] ?? [],
            'alerts' => $dashboard['alerts'] ?? [],
            'cost_series' => $dashboard['cost_series'] ?? [],
            'production_series' => $dashboard['production_series'] ?? [],
        ];
    }
}
