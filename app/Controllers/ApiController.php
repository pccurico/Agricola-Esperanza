<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class ApiController extends BaseController
{
    public function handle(array $identity): void
    {
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
            $this->json(['error' => 'Recurso API no encontrado'], 404);
            return;
        }
        $this->json(['data' => $data]);
    }
}
