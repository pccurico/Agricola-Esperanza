<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class DashboardController extends BaseController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\DashboardService(database()->connection(), (int) ($_SESSION['company_id'] ?? 0));
        $filterManager = new \CampoSur\Services\Dashboard\FilterManager([
            'date_from' => '',
            'date_to' => '',
            'farm_id' => 0,
            'block_id' => 0,
            'process' => '',
        ], $service->filterOptions());
        $widgetFactory = new \CampoSur\Services\Dashboard\WidgetFactory();
        $chartProvider = new \CampoSur\Services\Dashboard\ChartProvider();
        $layoutManager = new \CampoSur\Services\Dashboard\LayoutManager(database()->connection(), (int) ($_SESSION['company_id'] ?? 0), isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);
        $permissionResolver = new \CampoSur\Services\Dashboard\PermissionResolver(
            new \CampoSur\Services\Auth(database()->connection()),
            (int) ($_SESSION['role_id'] ?? 0),
            isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
            ['inventory', 'production', 'costs', 'labor', 'procurement', 'reports', 'audit', 'machinery', 'documents', 'masters', 'settings', 'tools'],
        );

        $builder = new \CampoSur\Services\Dashboard\DashboardBuilder(
            $filterManager,
            $widgetFactory,
            $chartProvider,
            $layoutManager,
            $permissionResolver,
            $service,
        );

        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            try {
                if (($_POST['action'] ?? '') === 'save_dashboard_view') {
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
            'date_from' => (string) ($_GET['date_from'] ?? ''),
            'date_to' => (string) ($_GET['date_to'] ?? ''),
            'view' => (string) ($_GET['view'] ?? ''),
            'period' => (string) ($_GET['period'] ?? 'month'),
        ];
        $department = (string) ($_SESSION['role_department'] ?? 'general');
        $dashboard = $builder->build($requestParams, $department, isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);

        return ['dashboard' => $dashboard, 'error' => $error, 'success' => $success];
    }
}
