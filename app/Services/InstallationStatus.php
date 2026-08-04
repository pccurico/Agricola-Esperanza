<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;

final class InstallationStatus extends BaseService
{
    public function __construct(protected readonly PDO $connection)
    {
    }

    public function isComplete(): bool
    {
        $requiredTables = ['companies', 'users', 'roles', 'permissions', 'role_permissions', 'schema_migrations'];
        $placeholders = implode(',', array_fill(0, count($requiredTables), '?'));
        $tables = $this->connection->prepare(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ($placeholders)"
        );
        $tables->execute($requiredTables);
        if ((int) $tables->fetchColumn() !== count($requiredTables)) {
            return false;
        }

        $counts = $this->connection->query(
            'SELECT (SELECT COUNT(*) FROM companies) AS companies_count,
                    (SELECT COUNT(*) FROM users) AS users_count,
                    (SELECT COUNT(*) FROM roles) AS roles_count,
                    (SELECT COUNT(*) FROM permissions) AS permissions_count,
                    (SELECT COUNT(*) FROM schema_migrations) AS migrations_count'
        )->fetch();

        return (int) $counts['companies_count'] > 0
            && (int) $counts['users_count'] > 0
            && (int) $counts['roles_count'] > 0
            && (int) $counts['permissions_count'] > 0
            && (int) $counts['migrations_count'] > 0;
    }
}

