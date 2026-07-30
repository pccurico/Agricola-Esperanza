<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class AuditController extends BaseController
{
    public function handle(): array
    {
        return ['logs' => (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->recent()];
    }
}
