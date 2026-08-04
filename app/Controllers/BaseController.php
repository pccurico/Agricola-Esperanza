<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

abstract class BaseController
{
    protected function json(array $payload, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($statusCode !== 200) {
            http_response_code($statusCode);
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    protected function redirect(string $location): never
    {
        header('Location: ' . $location);
        exit;
    }

    protected function handleAction(callable $action, string $successMessage, string $entity, array $context = []): array
    {
        $error = null;
        $success = null;

        try {
            $action();
            $success = $successMessage;
            if (($context['audit'] ?? false) && !empty($context['userId'])) {
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) ($_SESSION['company_id'] ?? 0)))->record((int) $context['userId'], $context['auditAction'] ?? 'UPDATE', $entity);
            }
        } catch (\Throwable $exception) {
            $error = $exception instanceof \PDOException
                ? 'No fue posible completar la operación. Verifica los datos e inténtalo nuevamente.'
                : $exception->getMessage();
        }

        return ['error' => $error, 'success' => $success];
    }
}
