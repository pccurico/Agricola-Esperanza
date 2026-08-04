<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class InternalRequestController extends BaseController
{
    public function handle(): array
    {
        $service = new \AgroPCC\Services\InternalRequestManagement(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = (string) ($_POST['action'] ?? '');
                if ($action === 'create_request') {
                    $_POST['items'] = [(int) $_POST['item_id'] => $_POST['quantity']];
                    $service->create($_POST, (int) $_SESSION['user_id']);
                    $success = 'Solicitud creada correctamente.';
                } elseif ($action === 'approve_request') {
                    $service->approve((int) $_POST['request_id'], (int) $_SESSION['user_id']);
                    $success = 'Solicitud aprobada correctamente.';
                } elseif ($action === 'fulfill_request') {
                    $service->fulfill($_POST, (int) $_SESSION['user_id']);
                    $success = 'Solicitud atendida y stock actualizado.';
                }
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [...$service->options(), 'requests' => $service->requests(), 'fulfillment_lines' => $service->fulfillmentOptions(), 'error' => $error, 'success' => $success];
    }
}
