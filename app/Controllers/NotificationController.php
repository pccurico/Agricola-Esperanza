<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class NotificationController extends BaseController
{
    public function handle(): array
    {
        $service = new \AgroPCC\Services\NotificationManagement(database()->connection(), (int) $_SESSION['company_id'], (int) $_SESSION['user_id']);
        $error = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (($_POST['action'] ?? '') === 'read') {
                    $service->markRead((int) $_POST['notification_id']);
                } elseif (($_POST['action'] ?? '') === 'read_all') {
                    $service->markAllRead();
                }
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return ['notifications' => $service->recent(), 'unreadCount' => $service->unreadCount(), 'error' => $error];
    }
}
