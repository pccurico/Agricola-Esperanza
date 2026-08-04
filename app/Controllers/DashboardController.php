<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class DashboardController extends BaseController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\DashboardService(database()->connection(), (int) ($_SESSION['company_id'] ?? 0));
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

        $selectedPeriod = (string) ($_GET['period'] ?? 'month');
        $selectedFilters = [
            'process' => (string) ($_GET['process'] ?? ''),
            'farm_id' => (int) ($_GET['farm_id'] ?? 0),
            'block_id' => (int) ($_GET['block_id'] ?? 0),
            'date_from' => (string) ($_GET['date_from'] ?? ''),
            'date_to' => (string) ($_GET['date_to'] ?? ''),
        ];
        $defaultDates = $service->defaultDateRange();
        if ($selectedFilters['date_from'] === '') {
            $selectedFilters['date_from'] = $defaultDates['date_from'];
        }
        if ($selectedFilters['date_to'] === '') {
            $selectedFilters['date_to'] = $defaultDates['date_to'];
        }
        $activeView = (string) ($_GET['view'] ?? '');
        $department = (string) ($_SESSION['role_department'] ?? 'general');

        $dashboard = $service->summary($selectedPeriod, null, $selectedFilters, $activeView, $department, (int) ($_SESSION['user_id'] ?? 0));

        return ['dashboard' => $dashboard, 'error' => $error, 'success' => $success];
    }
}
