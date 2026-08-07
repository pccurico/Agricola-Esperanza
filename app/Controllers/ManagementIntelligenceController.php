<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class ManagementIntelligenceController extends BaseController
{
    public function handle(): array
    {
        $companyId = (int) ($_SESSION['company_id'] ?? 0);
        $service = new \AgroPCC\Services\ReportService(database()->connection(), $companyId);
        $dateRange = (new \AgroPCC\Services\DashboardService(database()->connection(), $companyId))->defaultDateRange();
        $filters = array_merge($dateRange, [
            'farm_id' => (int) ($_GET['farm_id'] ?? 0),
            'block_id' => (int) ($_GET['block_id'] ?? 0),
            'season_id' => (int) ($_GET['season_id'] ?? 0),
            'cost_center_id' => (int) ($_GET['cost_center_id'] ?? 0),
            'process' => trim((string) ($_GET['process'] ?? '')),
        ]);

        return ['intelligence' => $service->summary($filters, 'executive')];
    }
}
