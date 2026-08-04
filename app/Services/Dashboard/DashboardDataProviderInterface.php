<?php

declare(strict_types=1);

namespace CampoSur\Services\Dashboard;

interface DashboardDataProviderInterface
{
    public function summary(string $period = 'month', ?string $referenceDate = null, array $filters = [], ?string $activeView = null, string $department = 'general', ?int $userId = null): array;
}
