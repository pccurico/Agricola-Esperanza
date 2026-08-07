<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class CatalogController extends BaseController
{
    public function handle(): array
    {
        $service = new \AgroPCC\Services\CatalogManagement(
            database()->connection(),
            (int) $_SESSION['company_id'],
            new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id'])
        );
        $error = null;
        $success = null;
        $catalogCode = (string) ($_GET['catalog'] ?? '');
        $editValueId = (int) ($_GET['edit_value'] ?? 0);
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (($_POST['action'] ?? '') === 'create') {
                    $catalogCode = (string) $_POST['catalog_code'];
                    $metadataJson = null;
                    $code = (string) ($_POST['code'] ?? '');
                    if ($catalogCode === 'INVENTORY_SUBCATEGORY') {
                        $categoryCode = strtoupper(trim((string) ($_POST['parent_category'] ?? '')));
                        $categoryCodes = array_column($service->values('INVENTORY_CATEGORY'), 'code');
                        if ($categoryCode === '' || !in_array($categoryCode, $categoryCodes, true)) {
                            throw new \RuntimeException('Selecciona una categoría válida para la subcategoría.');
                        }
                        $metadataJson = json_encode(['category' => $categoryCode], JSON_THROW_ON_ERROR);
                        $code = $service->automaticCode($catalogCode, (string) $_POST['label']);
                    }
                    $service->createCompanyValue(
                        (int) $_SESSION['user_id'],
                        $catalogCode,
                        $code,
                        (string) $_POST['label'],
                        (int) ($_POST['sort_order'] ?? 0),
                        $metadataJson
                    );
                    $success = $catalogCode === 'INVENTORY_SUBCATEGORY' ? 'Subcategoría creada correctamente.' : 'Opción creada correctamente.';
                }
                if (($_POST['action'] ?? '') === 'update' && (string) $_POST['catalog_code'] === 'INVENTORY_SUBCATEGORY') {
                    $catalogCode = (string) $_POST['catalog_code'];
                    $categoryCode = strtoupper(trim((string) ($_POST['parent_category'] ?? '')));
                    $categoryCodes = array_column($service->values('INVENTORY_CATEGORY'), 'code');
                    if ($categoryCode === '' || !in_array($categoryCode, $categoryCodes, true)) {
                        throw new \RuntimeException('Selecciona una categoría válida para la subcategoría.');
                    }
                    $metadataJson = json_encode(['category' => $categoryCode], JSON_THROW_ON_ERROR);
                    $service->updateCompanyValue((int) $_SESSION['user_id'], (int) $_POST['value_id'], $catalogCode, (string) $_POST['label'], (int) ($_POST['sort_order'] ?? 0), $metadataJson);
                    $editValueId = 0;
                    $success = 'Subcategoría actualizada correctamente.';
                }
                if (($_POST['action'] ?? '') === 'deactivate') {
                    $service->deactivateValue((int) $_SESSION['user_id'], (int) $_POST['value_id']);
                    $catalogCode = (string) $_POST['catalog_code'];
                    $success = $catalogCode === 'INVENTORY_SUBCATEGORY' ? 'Subcategoría eliminada correctamente.' : 'Opción eliminada correctamente.';
                }
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [
            'catalogs' => $service->catalogs(),
            'categories' => $service->values('INVENTORY_CATEGORY'),
            'catalogCode' => $catalogCode,
            'values' => $catalogCode !== '' ? $service->values($catalogCode) : [],
            'editValueId' => $editValueId,
            'error' => $error,
            'success' => $success,
        ];
    }
}
