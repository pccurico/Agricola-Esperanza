<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class ApiController
{
    public function handle(array $identity): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $path = trim((string) ($_GET['api'] ?? ''), '/');
        $companyId = (int) $identity['company_id'];
        if ($path === 'v1/me') {
            $data = ['user' => ['id' => (int) $identity['user_id'], 'name' => $identity['full_name'], 'email' => $identity['email']], 'company_id' => $companyId, 'role_id' => (int) $identity['role_id']];
        } elseif ($path === 'v1/dashboard') {
            $data = (new \CampoSur\Services\DashboardService(database()->connection(), $companyId))->summary();
        } elseif ($path === 'v1/inventory/items') {
            $data = ['items' => (new \CampoSur\Services\InventoryManagement(database()->connection(), $companyId))->items()];
        } elseif ($path === 'v1/inventory/movements') {
            $data = ['movements' => (new \CampoSur\Services\InventoryManagement(database()->connection(), $companyId))->movements()];
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Recurso API no encontrado'], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
    }
}
