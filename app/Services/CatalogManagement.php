<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;
use RuntimeException;

final class CatalogManagement extends BaseService
{
    public function __construct(
        protected readonly PDO $connection,
        protected readonly int $companyId,
        private readonly AuditLog $audit
    ) {
    }

    public function catalogs(): array
    {
        return $this->connection->query('SELECT id, code, name, scope, active FROM system_catalogs WHERE active = 1 ORDER BY name')->fetchAll();
    }

    public function values(string $catalogCode): array
    {
        $query = $this->connection->prepare(
            'SELECT v.id, v.code, v.label, v.sort_order, v.active, v.metadata_json, c.scope
             FROM system_catalog_values v
             INNER JOIN system_catalogs c ON c.id = v.catalog_id
             WHERE c.code = ? AND c.active = 1 AND v.active = 1
               AND (v.company_id IS NULL OR v.company_id = ?)
             ORDER BY v.sort_order, v.label'
        );
        $query->execute([$catalogCode, $this->companyId]);
        return $query->fetchAll();
    }

    public function automaticCode(string $catalogCode, string $label, ?int $excludeId = null): string
    {
        $catalog = $this->catalog($catalogCode);
        if (!$catalog) {
            throw new RuntimeException('El catálogo no existe.');
        }
        $base = strtoupper(trim((string) preg_replace('/[^A-Z0-9]+/i', '_', strtr(trim($label), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N']))));
        $base = trim($base, '_') ?: 'SUBCATEGORIA';
        $base = substr($base, 0, 70);
        $code = $base;
        $suffix = 2;
        do {
            $query = $this->connection->prepare('SELECT COUNT(*) FROM system_catalog_values WHERE catalog_id = ? AND company_id = ? AND code = ? AND id <> ?');
            $query->execute([(int) $catalog['id'], $this->companyId, $code, $excludeId ?? 0]);
            $exists = (int) $query->fetchColumn() > 0;
            if ($exists) {
                $code = substr($base, 0, 70 - strlen((string) $suffix)) . '_' . $suffix++;
            }
        } while ($exists);
        return $code;
    }

    public function createCompanyValue(int $userId, string $catalogCode, string $code, string $label, int $sortOrder = 0, ?string $metadataJson = null): int
    {
        $catalog = $this->catalog($catalogCode);
        if (!$catalog || $catalog['scope'] !== 'COMPANY') {
            throw new RuntimeException('El catálogo no admite valores por empresa.');
        }
        if (trim($code) === '' || trim($label) === '') {
            throw new RuntimeException('El código y la etiqueta son obligatorios.');
        }
        $query = $this->connection->prepare(
            'INSERT INTO system_catalog_values (catalog_id, company_id, code, label, sort_order, metadata_json) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $query->execute([(int) $catalog['id'], $this->companyId, strtoupper(trim($code)), trim($label), $sortOrder, $metadataJson]);
        $id = (int) $this->connection->lastInsertId();
        $this->audit->record($userId, 'CREATE', 'system_catalog_values', $id, ['catalog' => $catalogCode]);
        return $id;
    }

    public function updateCompanyValue(int $userId, int $valueId, string $catalogCode, string $label, int $sortOrder, string $metadataJson): void
    {
        $catalog = $this->catalog($catalogCode);
        if (!$catalog || $catalog['scope'] !== 'COMPANY') {
            throw new RuntimeException('El catálogo no admite valores por empresa.');
        }
        if (trim($label) === '') {
            throw new RuntimeException('El nombre visible es obligatorio.');
        }
        $exists = $this->connection->prepare('SELECT id FROM system_catalog_values WHERE id = ? AND catalog_id = ? AND company_id = ? AND active = 1 LIMIT 1');
        $exists->execute([$valueId, (int) $catalog['id'], $this->companyId]);
        if (!$exists->fetchColumn()) {
            throw new RuntimeException('La subcategoría no existe para la empresa.');
        }
        $query = $this->connection->prepare('UPDATE system_catalog_values SET label = ?, sort_order = ?, metadata_json = ? WHERE id = ? AND catalog_id = ? AND company_id = ?');
        $query->execute([trim($label), $sortOrder, $metadataJson, $valueId, (int) $catalog['id'], $this->companyId]);
        $this->audit->record($userId, 'UPDATE', 'system_catalog_values', $valueId, ['catalog' => $catalogCode]);
    }

    public function deactivateValue(int $userId, int $valueId): void
    {
        $query = $this->connection->prepare('UPDATE system_catalog_values SET active = 0 WHERE id = ? AND company_id = ?');
        $query->execute([$valueId, $this->companyId]);
        if ($query->rowCount() === 0) {
            throw new RuntimeException('El valor de catálogo no existe para la empresa.');
        }
        $this->audit->record($userId, 'DEACTIVATE', 'system_catalog_values', $valueId);
    }

    private function catalog(string $code): ?array
    {
        $query = $this->connection->prepare('SELECT id, code, scope FROM system_catalogs WHERE code = ? AND active = 1 LIMIT 1');
        $query->execute([$code]);
        $catalog = $query->fetch();
        return $catalog ?: null;
    }
}
