<?php

declare(strict_types=1);

namespace AgroPCC\Services\Dashboard;

final class ProductionDashboardService
{
    public function __construct(private readonly DashboardDataProviderInterface $dashboardService)
    {
    }

    public function data(array $filters, string $period, string $activeView, string $department, ?int $userId): array
    {
        $dashboard = $this->dashboardService->summary($period, null, $filters, $activeView, $department, $userId);

        return [
            'production' => $dashboard['sections']['production'] ?? [],
            'costs' => $dashboard['sections']['costs'] ?? [],
            'metrics' => $dashboard['metrics'] ?? [],
            'production_series' => $dashboard['production_series'] ?? [],
        ];
    }
}
