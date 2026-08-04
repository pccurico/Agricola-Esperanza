<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;

final class MigrationRunner extends BaseService
{
    public function __construct(protected readonly PDO $connection, private readonly string $rootPath)
    {
    }

    public function run(): void
    {
        $this->connection->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(120) PRIMARY KEY, applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->registerExisting('001_initial_schema', 'companies');
        $this->registerExisting('002_labor_schema', 'workers');
        $this->registerExisting('003_production_schema', 'production_entries');
        $this->registerExisting('004_procurement_schema', 'suppliers');
        $this->registerExisting('005_budget_schema', 'budgets');
        $this->registerExisting('006_machinery_schema', 'machinery');
        $this->registerExisting('008_platform_entities', 'company_settings');
        $this->registerExisting('009_system_logs', 'system_logs');
        $this->registerExisting('010_system_catalogs', 'system_catalogs');
        foreach (glob($this->rootPath . '/database/migrations/*.sql') ?: [] as $path) {
            $version = pathinfo($path, PATHINFO_FILENAME);
            $check = $this->connection->prepare('SELECT version FROM schema_migrations WHERE version = ?');
            $check->execute([$version]);
            if ($check->fetchColumn()) {
                continue;
            }
            try {
                foreach (preg_split('/;\s*(?:\r?\n|$)/', file_get_contents($path)) as $statement) {
                    if (trim($statement) !== '') {
                        try {
                            $this->connection->exec($statement);
                        } catch (\PDOException $exception) {
                            $errorInfo = $exception->errorInfo;
                            if ((int) ($errorInfo[1] ?? 0) !== 1061) {
                                throw $exception;
                            }
                        }
                    }
                }
                $this->connection->prepare('INSERT INTO schema_migrations (version) VALUES (?)')->execute([$version]);
            } catch (\Throwable $exception) {
                if ($this->connection->inTransaction()) {
                    $this->connection->rollBack();
                }
                throw $exception;
            }
        }
    }

    private function registerExisting(string $version, string $table): void
    {
        $exists = $this->connection->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
        $exists->execute([$table]);
        if (!$exists->fetchColumn()) {
            return;
        }
        $this->connection->prepare('INSERT IGNORE INTO schema_migrations (version) VALUES (?)')->execute([$version]);
    }
}

