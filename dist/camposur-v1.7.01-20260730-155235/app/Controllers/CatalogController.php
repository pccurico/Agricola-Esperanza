<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class CatalogController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\CatalogManagement(
            database()->connection(),
            (int) $_SESSION['company_id'],
            new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id'])
        );
        $error = null;
        $success = null;
        $catalogCode = (string) ($_GET['catalog'] ?? '');
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (($_POST['action'] ?? '') === 'create') {
                    $service->createCompanyValue(
                        (int) $_SESSION['user_id'],
                        (string) $_POST['catalog_code'],
                        (string) $_POST['code'],
                        (string) $_POST['label'],
                        (int) ($_POST['sort_order'] ?? 0)
                    );
                    $catalogCode = (string) $_POST['catalog_code'];
                    $success = 'Valor de catálogo creado correctamente.';
                }
                if (($_POST['action'] ?? '') === 'deactivate') {
                    $service->deactivateValue((int) $_SESSION['user_id'], (int) $_POST['value_id']);
                    $catalogCode = (string) $_POST['catalog_code'];
                    $success = 'Valor de catálogo desactivado correctamente.';
                }
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [
            'catalogs' => $service->catalogs(),
            'catalogCode' => $catalogCode,
            'values' => $catalogCode !== '' ? $service->values($catalogCode) : [],
            'error' => $error,
            'success' => $success,
        ];
    }
}
