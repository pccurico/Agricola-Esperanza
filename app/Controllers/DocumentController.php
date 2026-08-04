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
        return [...$service->options(), 'documents' => $service->documents(), 'error' => $error, 'success' => $success];
    }
}
