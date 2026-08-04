<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class DashboardController extends BaseController
{
    private function createBuilder(): \AgroPCC\Services\Dashboard\DashboardBuilder
    {
        $service = new \AgroPCC\Services\DashboardService(database()->connection(), (int) ($_SESSION['company_id'] ?? 0));
        $filterManager = new \AgroPCC\Services\Dashboard\FilterManager([
            'date_from' => '',
            'date_to' => '',
            'farm_id' => 0,
            'block_id' => 0,
            'process' => '',
        ], $service->filterOptions());
        $widgetFactory = new \AgroPCC\Services\Dashboard\WidgetFactory();
        $chartProvider = new \AgroPCC\Services\Dashboard\ChartProvider();
        $layoutManager = new \AgroPCC\Services\Dashboard\LayoutManager(database()->connection(), (int) ($_SESSION['company_id'] ?? 0), isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);
        $permissionResolver = new \AgroPCC\Services\Dashboard\PermissionResolver(
            new \AgroPCC\Services\Auth(database()->connection()),
            (int) ($_SESSION['role_id'] ?? 0),
            isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
            ['inventory', 'production', 'costs', 'labor', 'procurement', 'reports', 'audit', 'machinery', 'documents', 'masters', 'settings', 'tools'],
            (bool) ($_SESSION['role_is_system'] ?? false),
        );

        return new \AgroPCC\Services\Dashboard\DashboardBuilder(
            $filterManager,
            $widgetFactory,
            $chartProvider,
            $layoutManager,
            $permissionResolver,
            $service,
        );
    }

    private function buildDashboard(array $requestParams, string $department, ?int $userId = null): array
    {
        return $this->createBuilder()->build($requestParams, $department, $userId);
    }

    public function handle(): array
    {
        $error = null;
        $success = null;

        $requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        if ($requestMethod === 'POST') {
            verify_csrf();
            try {
                if (($_POST['action'] ?? '') === 'save_dashboard_view') {
                    $service = new \AgroPCC\Services\DashboardService(database()->connection(), (int) ($_SESSION['company_id'] ?? 0));
                    $name = trim((string) ($_POST['view_name'] ?? ''));
                    $layout = [
                        'filters' => [
                            'date_from' => (string) ($_POST['date_from'] ?? ''),
                            'date_to' => (string) ($_POST['date_to'] ?? ''),
                            'process' => (string) ($_POST['process'] ?? ''),
                            'farm_id' => (int) ($_POST['farm_id'] ?? 0),
                            'block_id' => (int) ($_POST['block_id'] ?? 0),
                        ],
                        'widgets' => array_values(array_filter(array_map('trim', (array) ($_POST['widgets'] ?? [])))),
                    ];
                    $service->saveView($name, $layout, (int) ($_SESSION['user_id'] ?? 0));
                    $success = 'Vista guardada correctamente.';
                }
            } catch (\Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        $requestParams = [
            'process' => (string) ($_GET['process'] ?? ''),
            'farm_id' => (int) ($_GET['farm_id'] ?? 0),
            'block_id' => (int) ($_GET['block_id'] ?? 0),
            'season_id' => (int) ($_GET['season_id'] ?? 0),
            'cost_center_id' => (int) ($_GET['cost_center_id'] ?? 0),
            'date_from' => (string) ($_GET['date_from'] ?? ''),
            'date_to' => (string) ($_GET['date_to'] ?? ''),
            'view' => (string) ($_GET['view'] ?? ''),
            'period' => (string) ($_GET['period'] ?? 'month'),
        ];
        $department = (string) ($_SESSION['role_department'] ?? 'general');
        $dashboard = $this->buildDashboard($requestParams, $department, isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);

        return ['dashboard' => $dashboard, 'error' => $error, 'success' => $success];
    }

    public function data(): array
    {
        $requestParams = [
            'process' => (string) ($_GET['process'] ?? ''),
            'farm_id' => (int) ($_GET['farm_id'] ?? 0),
            'block_id' => (int) ($_GET['block_id'] ?? 0),
            'season_id' => (int) ($_GET['season_id'] ?? 0),
            'cost_center_id' => (int) ($_GET['cost_center_id'] ?? 0),
            'date_from' => (string) ($_GET['date_from'] ?? ''),
            'date_to' => (string) ($_GET['date_to'] ?? ''),
            'view' => (string) ($_GET['view'] ?? ''),
            'period' => (string) ($_GET['period'] ?? 'month'),
        ];
        $department = (string) ($_SESSION['role_department'] ?? 'general');
        $dashboard = $this->buildDashboard($requestParams, $department, isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);

        return [
            'kpis' => $dashboard['kpis'] ?? [],
            'production_series' => $dashboard['production_series'] ?? [],
            'cost_series' => $dashboard['cost_series'] ?? [],
            'cost_by_process' => $dashboard['sections']['costs']['by_process'] ?? [],
            'alerts' => $dashboard['alerts'] ?? [],
            'recent' => $dashboard['recent'] ?? [],
            'metrics' => $dashboard['metrics'] ?? [],
            'totals' => $dashboard['totals'] ?? [],
            'budget' => $dashboard['budget'] ?? [],
            'filters' => $dashboard['filters'] ?? [],
        ];
    }
}
