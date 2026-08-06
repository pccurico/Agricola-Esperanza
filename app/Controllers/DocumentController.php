<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class DocumentController extends BaseController
{
    public function handle(): array
    {
        $service = new \AgroPCC\Services\DocumentManagement(database()->connection(), (int) $_SESSION['company_id'], dirname(__DIR__, 2));
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_document') {
                $service->create($_POST, $_FILES['attachment'] ?? [], (int) $_SESSION['user_id']);
                $success = 'Documento creado correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        $filters = [
            'supplier_id' => (int) ($_GET['supplier_id'] ?? 0),
            'client_id' => (int) ($_GET['client_id'] ?? 0),
            'document_type' => trim((string) ($_GET['document_type'] ?? '')),
        ];
        return [...$service->options(), 'documents' => $service->documents($filters), 'filters' => $filters, 'error' => $error, 'success' => $success];
    }
}
