<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class LaborController extends BaseController
{
    public function handle(): array
    {
        $service = new \AgroPCC\Services\LaborManagement(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        $workers = $service->workers();
        $viewName = (string) ($_GET['view'] ?? '');

        $selectedSeasonId = 0;
        if ($viewName === 'worker-form' && !isset($_GET['worker_id'])) {
            $selectedWorkerId = 0;
        } else {
            $selectedWorkerId = isset($_GET['worker_id']) ? (int) $_GET['worker_id'] : (int) ($workers[0]['id'] ?? 0);
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_worker') {
                $newWorkerId = $service->createWorker($_POST);
                $service->upsertWorkerProfile(array_merge($_POST, ['worker_id' => $newWorkerId]));
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'worker');
                $success = 'Trabajador creado correctamente.';
                $workers = $service->workers();
                $selectedWorkerId = (int) $newWorkerId;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_worker') {
                $workerId = (int) ($_POST['worker_id'] ?? 0);
                if ($workerId <= 0) {
                    throw new \RuntimeException('Debes seleccionar un trabajador para editar.');
                }
                $service->updateWorker($workerId, $_POST);
                $service->upsertWorkerProfile(array_merge($_POST, ['worker_id' => $workerId]));
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'UPDATE', 'worker');
                $success = 'Trabajador actualizado correctamente.';
                $selectedWorkerId = $workerId;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_worker') {
                $workerId = (int) ($_POST['worker_id'] ?? 0);
                if ($workerId <= 0) {
                    throw new \RuntimeException('Debes seleccionar un trabajador para cambiar su estado.');
                }
                $service->toggleWorker($workerId, (int) ($_POST['active'] ?? 0));
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'UPDATE', 'worker_status');
                $success = 'Estado del trabajador actualizado correctamente.';
                $selectedWorkerId = $workerId;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_labor') {
                $service->createEntry($_POST, (int) $_SESSION['user_id']);
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'labor_entry');
                $selectedWorkerId = (int) ($_POST['worker_id'] ?? $selectedWorkerId);
                $selectedSeasonId = (int) ($_POST['season_id'] ?? 0);
                $success = 'Labor registrada correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_assignment') {
                $service->createAssignment($_POST);
                $success = 'Asignación registrada correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_leave_request') {
                $service->createLeaveRequest($_POST);
                $success = 'Solicitud de ausencia registrada correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }

        $workers = $service->workers();
        if ($selectedWorkerId > 0 && $workers !== []) {
            $selectedWorkerId = (int) $selectedWorkerId;
            $profileData = $service->workerProfile($selectedWorkerId);
        } else {
            $profileData = ['worker' => null, 'profile' => null, 'contract' => null, 'benefits' => null, 'bank' => null];
        }

        $showMode = ($viewName === 'worker-form' && (string) ($_GET['show'] ?? '') === '1');

        return [...$service->options(), 'workers' => $workers, 'entries' => $service->entries(), 'assignments' => $service->assignments(), 'leave_requests' => $service->leaveRequests(), 'error' => $error, 'success' => $success, 'selected_worker_id' => $selectedWorkerId, 'selected_season_id' => $selectedSeasonId, 'profile_data' => $profileData, 'view_name' => $viewName, 'show_mode' => $showMode, 'worker_form' => $service->workerFormData($selectedWorkerId)];
    }
}
