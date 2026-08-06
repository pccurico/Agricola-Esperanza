<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

use AgroPCC\Services\CostManagement;

final class CostsController extends BaseController
{
    public function handle(): array
    {
        $service = new \AgroPCC\Services\CostManagement(database()->connection(), (int) $_SESSION['company_id']);
        $category = in_array($_GET['category'] ?? null, ['INVERSION', 'SERVICIOS_GASTOS'], true) ? $_GET['category'] : null;
        $categoryLabel = $category === 'INVERSION' ? 'Inversiones' : ($category === 'SERVICIOS_GASTOS' ? 'Servicios y gastos' : 'Costos y gastos');
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_cash_transaction') {
                $service->createCashTransaction($_POST, (int) $_SESSION['user_id']);
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'cash_transaction', null, ['description' => $_POST['description'] ?? null]);
                $success = 'Movimiento de caja registrado correctamente.';
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $service->create($_POST, (int) $_SESSION['user_id']);
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'expense_entry', null, ['description' => $_POST['description'] ?? null]);
                $success = 'Costo registrado correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [...$service->options($category), 'entries' => $service->entries($category), 'cash_transactions' => $service->cashTransactions(), 'cash_summary' => $service->cashSummary(), 'category' => $category, 'category_label' => $categoryLabel, 'error' => $error, 'success' => $success];
    }
}
