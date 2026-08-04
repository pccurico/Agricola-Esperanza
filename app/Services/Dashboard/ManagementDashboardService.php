<?php

declare(strict_types=1);

namespace AgroPCC\Services\Dashboard;

final class ManagementDashboardService
{
    public function __construct(private readonly DashboardDataProviderInterface $dashboardService)
    {
    }

    public function data(array $filters, string $period, string $activeView, string $department, ?int $userId): array
    {
        $dashboard = $this->dashboardService->summary($period, null, $filters, $activeView, $department, $userId);

        return [
            'executive' => $dashboard['sections']['executive'] ?? [],
            'alerts' => $dashboard['alerts'] ?? [],
            'kpis' => $dashboard['kpis'] ?? [],
            'analyses' => $dashboard['analyses'] ?? [],
            'saved_views' => $dashboard['customization']['saved_views'] ?? [],
        ];
    }
}
