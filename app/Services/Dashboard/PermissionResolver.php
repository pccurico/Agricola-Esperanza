<?php

declare(strict_types=1);

namespace CampoSur\Services\Dashboard;

use CampoSur\Services\Auth;

final class PermissionResolver
{
    public function __construct(private readonly Auth $auth, private readonly int $roleId, private readonly ?int $userId = null, private readonly array $activeModules = [])
    {
    }

    public function filterWidgets(array $widgets): array
    {
        return array_values(array_filter($widgets, [$this, 'canViewWidget']));
    }

    public function canViewWidget(array $widget): bool
    {
        $permission = (string) ($widget['permission'] ?? '');
        $module = (string) ($widget['module'] ?? '');

        if ($permission !== '') {
            return $this->auth->can($this->roleId, $permission, $this->userId);
        }

        if ($module !== '' && $this->activeModules !== [] && !in_array($module, $this->activeModules, true)) {
            return false;
        }

        return true;
    }
}
