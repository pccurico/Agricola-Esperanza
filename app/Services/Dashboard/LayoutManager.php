<?php

declare(strict_types=1);

namespace CampoSur\Services\Dashboard;

final class LayoutManager
{
    private const GLOBAL_VIEW_PREFIX = 'dashboard.view.';
    private const GLOBAL_ACTIVE_VIEW = 'dashboard.active_view';
    private const USER_VIEW_PREFIX = 'dashboard.user.%d.view.';
    private const USER_ACTIVE_VIEW = 'dashboard.user.%d.active_view';

    public function __construct(private readonly \PDO $connection, private readonly int $companyId, private readonly ?int $userId = null)
    {
    }

    public function saveLayout(string $name, array $layout): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new \RuntimeException('El nombre de la vista es obligatorio.');
        }

        $key = $this->layoutKey($name);
        $payload = json_encode(array_merge($layout, ['label' => $name]), JSON_UNESCAPED_UNICODE);

        $this->connection->prepare('INSERT INTO company_settings (company_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
            ->execute([$this->companyId, $key, $payload]);

        $activeKey = $this->activeViewKey();
        $this->connection->prepare('INSERT INTO company_settings (company_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
            ->execute([$this->companyId, $activeKey, $this->slug($name)]);
    }

    public function loadSavedViews(): array
    {
        $prefix = $this->viewPrefix();
        $query = $this->connection->prepare('SELECT setting_key, setting_value FROM company_settings WHERE company_id = ? AND setting_key LIKE ? ORDER BY setting_key');
        $query->execute([$this->companyId, $prefix . '%']);

        $views = [];
        while ($row = $query->fetch()) {
            $key = (string) ($row['setting_key'] ?? '');
            $value = json_decode((string) ($row['setting_value'] ?? ''), true) ?: [];
            $name = str_replace($prefix, '', $key);
            $views[] = [
                'name' => $name,
                'label' => $value['label'] ?? $name,
                'layout' => $value,
            ];
        }

        return array_values($views);
    }

    public function resolveActiveView(?string $requestView, array $savedViews): string
    {
        $activeView = trim((string) $requestView);
        if ($activeView !== '') {
            foreach ($savedViews as $view) {
                if ($view['name'] === $activeView) {
                    return $activeView;
                }
            }
        }

        $activeView = $this->loadActiveView();
        if ($activeView !== '') {
            return $activeView;
        }

        return $savedViews[0]['name'] ?? '';
    }

    private function loadActiveView(): string
    {
        $query = $this->connection->prepare('SELECT setting_value FROM company_settings WHERE company_id = ? AND setting_key = ? LIMIT 1');
        $query->execute([$this->companyId, $this->activeViewKey()]);
        return trim((string) $query->fetchColumn());
    }

    private function layoutKey(string $name): string
    {
        return $this->viewPrefix() . $this->slug($name);
    }

    private function viewPrefix(): string
    {
        if ($this->userId === null) {
            return self::GLOBAL_VIEW_PREFIX;
        }

        return sprintf(self::USER_VIEW_PREFIX, $this->userId);
    }

    private function activeViewKey(): string
    {
        if ($this->userId === null) {
            return self::GLOBAL_ACTIVE_VIEW;
        }

        return sprintf(self::USER_ACTIVE_VIEW, $this->userId);
    }

    private function slug(string $value): string
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($value)));
        return trim((string) $slug, '-');
    }
}
