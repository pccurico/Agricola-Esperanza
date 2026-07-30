<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class TaskCalendarController extends BaseController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\TaskCalendarManagement(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = (string) ($_POST['action'] ?? '');
                if ($action === 'create_task') {
                    $service->createTask($_POST, (int) $_SESSION['user_id']);
                    $success = 'Tarea creada correctamente.';
                } elseif ($action === 'update_task') {
                    $service->updateTaskStatus((int) $_POST['task_id'], (string) $_POST['status'], (int) $_SESSION['user_id']);
                    $success = 'Estado de tarea actualizado.';
                } elseif ($action === 'create_event') {
                    $service->createEvent($_POST, (int) $_SESSION['user_id']);
                    $success = 'Evento creado correctamente.';
                }
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [...$service->options(), 'tasks' => $service->tasks(), 'events' => $service->events(), 'error' => $error, 'success' => $success];
    }
}
