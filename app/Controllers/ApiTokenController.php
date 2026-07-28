<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class ApiTokenController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\ApiTokenManagement(database()->connection(), (int) $_SESSION['company_id'], (int) $_SESSION['user_id']);
        $error = null;
        $newToken = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (($_POST['action'] ?? '') === 'create') {
                    $newToken = $service->create((string) $_POST['name'], $_POST['expires_at'] ?: null);
                } elseif (($_POST['action'] ?? '') === 'revoke') {
                    $service->revoke((int) $_POST['token_id']);
                }
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return ['tokens' => $service->tokens(), 'newToken' => $newToken, 'error' => $error];
    }
}
