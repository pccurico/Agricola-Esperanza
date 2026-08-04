<?php

declare(strict_types=1);

namespace CampoSur\Services\Dashboard;

final class InventoryDashboardService
{
    public function __construct(private readonly DashboardDataProviderInterface $dashboardService)
    {
    }

    public function data(array $filters, string $period, string $activeView, string $department, ?int $userId): array
    {
        $dashboard = $this->dashboardService->summary($period, null, $filters, $activeView, $department, $userId);

        return [
            'warehouse' => $dashboard['sections']['warehouse'] ?? [],
            'inventory_alerts' => $dashboard['inventory_alerts'] ?? [],
            'alerts' => $dashboard['alerts'] ?? [],
        ];
    }
}
