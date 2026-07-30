<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;

final class CatalogLookup extends BaseService
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function exists(string $catalogCode, string $valueCode): bool
    {
        $query = $this->connection->prepare(
            'SELECT v.id FROM system_catalog_values v
             INNER JOIN system_catalogs c ON c.id = v.catalog_id
             WHERE c.code = ? AND c.active = 1 AND v.active = 1
               AND v.code = ? AND (v.company_id IS NULL OR v.company_id = ?) LIMIT 1'
        );
        $query->execute([$catalogCode, strtoupper(trim($valueCode)), $this->companyId]);
        return (bool) $query->fetchColumn();
    }
}
