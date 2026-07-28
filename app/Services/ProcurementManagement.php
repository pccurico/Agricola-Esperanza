<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class ProcurementManagement
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function suppliers(): array
    {
        $query = $this->connection->prepare('SELECT id, tax_id, business_name, contact_name, email, phone, active FROM suppliers WHERE company_id = ? ORDER BY business_name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function orders(): array
    {
        $query = $this->connection->prepare('SELECT p.id, p.order_number, p.order_date, p.status, s.business_name, COUNT(i.id) AS items_count FROM purchase_orders p INNER JOIN suppliers s ON s.id = p.supplier_id LEFT JOIN purchase_order_items i ON i.purchase_order_id = p.id WHERE p.company_id = ? GROUP BY p.id ORDER BY p.order_date DESC, p.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function createSupplier(array $input): void
    {
        if (trim((string) ($input['business_name'] ?? '')) === '') {
            throw new RuntimeException('La razón social del proveedor es obligatoria.');
        }
        $query = $this->connection->prepare('INSERT INTO suppliers (company_id, tax_id, business_name, contact_name, email, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, trim($input['tax_id']) ?: null, trim($input['business_name']), trim($input['contact_name']) ?: null, trim($input['email']) ?: null, trim($input['phone']) ?: null, trim($input['address']) ?: null]);
    }

    public function createOrder(array $input, int $userId): void
    {
        foreach (['supplier_id', 'order_number', 'order_date'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Completa los datos obligatorios de la orden.');
            }
        }
        $supplier = $this->connection->prepare('SELECT id FROM suppliers WHERE id = ? AND company_id = ? AND active = 1');
        $supplier->execute([(int) $input['supplier_id'], $this->companyId]);
        if (!$supplier->fetchColumn()) {
            throw new RuntimeException('El proveedor no pertenece a esta empresa.');
        }
        foreach ([['seasons', $input['season_id'] ?? null], ['farms', $input['farm_id'] ?? null]] as [$table, $id]) {
            if ($id) {
                $reference = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ?');
                $reference->execute([(int) $id, $this->companyId]);
                if (!$reference->fetchColumn()) {
                    throw new RuntimeException('Una referencia seleccionada no pertenece a esta empresa.');
                }
            }
        }
        $query = $this->connection->prepare('INSERT INTO purchase_orders (company_id, supplier_id, season_id, farm_id, order_number, order_date, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, (int) $input['supplier_id'], $input['season_id'] ?: null, $input['farm_id'] ?: null, strtoupper(trim($input['order_number'])), $input['order_date'], trim($input['notes']) ?: null, $userId]);
    }

    public function options(): array
    {
        $suppliers = $this->connection->prepare('SELECT id, business_name FROM suppliers WHERE company_id = ? AND active = 1 ORDER BY business_name');
        $suppliers->execute([$this->companyId]);
        $seasons = $this->connection->prepare('SELECT id, name FROM seasons WHERE company_id = ? AND active = 1 ORDER BY starts_on DESC');
        $seasons->execute([$this->companyId]);
        $farms = $this->connection->prepare('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name');
        $farms->execute([$this->companyId]);
        return ['supplier_options' => $suppliers->fetchAll(), 'season_options' => $seasons->fetchAll(), 'farm_options' => $farms->fetchAll()];
    }
}
