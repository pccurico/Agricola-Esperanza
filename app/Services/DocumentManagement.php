<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class DocumentManagement
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId, private readonly string $rootPath)
    {
    }

    public function documents(): array
    {
        $query = $this->connection->prepare('SELECT d.id, d.document_type, d.document_number, d.issue_date, d.status, d.created_at, s.business_name AS supplier_name, c.business_name AS client_name FROM documents d LEFT JOIN suppliers s ON s.id = d.supplier_id LEFT JOIN clients c ON c.id = d.client_id WHERE d.company_id = ? ORDER BY d.issue_date DESC, d.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function attachments(int $documentId): array
    {
        $query = $this->connection->prepare('SELECT id, original_name, mime_type, file_size, created_at FROM attachments WHERE company_id = ? AND document_id = ? ORDER BY created_at DESC, id DESC');
        $query->execute([$this->companyId, $documentId]);
        return $query->fetchAll();
    }

    public function options(): array
    {
        $suppliers = $this->connection->prepare('SELECT id, business_name FROM suppliers WHERE company_id = ? AND active = 1 ORDER BY business_name');
        $suppliers->execute([$this->companyId]);
        $clients = $this->connection->prepare('SELECT id, business_name FROM clients WHERE company_id = ? AND active = 1 ORDER BY business_name');
        $clients->execute([$this->companyId]);
        return ['suppliers' => $suppliers->fetchAll(), 'clients' => $clients->fetchAll()];
    }

    public function create(array $input, array $file, int $userId): int
    {
        $type = strtoupper(trim((string) ($input['document_type'] ?? '')));
        if ($type === '') {
            throw new RuntimeException('El tipo de documento es obligatorio.');
        }
        $supplierId = $input['supplier_id'] ?: null;
        $clientId = $input['client_id'] ?: null;
        if ($supplierId) {
            $this->belongs('suppliers', $supplierId);
        }
        if ($clientId) {
            $this->belongs('clients', $clientId);
        }
        $this->connection->beginTransaction();
        try {
            $document = $this->connection->prepare('INSERT INTO documents (company_id, document_type, document_number, issue_date, supplier_id, client_id, status, created_by) VALUES (?, ?, ?, ?, ?, ?, \'DRAFT\', ?)');
            $document->execute([$this->companyId, $type, trim((string) ($input['document_number'] ?? '')) ?: null, $input['issue_date'] ?: null, $supplierId, $clientId, $userId]);
            $documentId = (int) $this->connection->lastInsertId();
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $this->attach($documentId, $file, $userId);
            }
            $this->connection->commit();
            (new AuditLog($this->connection, $this->companyId))->record($userId, 'CREATE', 'documents', $documentId);
            return $documentId;
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }

    public function attach(int $documentId, array $file, int $userId): int
    {
        $document = $this->connection->prepare('SELECT id FROM documents WHERE id = ? AND company_id = ?');
        $document->execute([$documentId, $this->companyId]);
        if (!$document->fetchColumn()) {
            throw new RuntimeException('El documento no pertenece a esta empresa.');
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
            throw new RuntimeException('El archivo adjunto no es válido.');
        }
        if ((int) $file['size'] > 10 * 1024 * 1024) {
            throw new RuntimeException('El archivo adjunto supera los 10 MB.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'];
        if (!isset($extensions[$mime])) {
            throw new RuntimeException('El tipo de archivo no está permitido.');
        }
        $directory = $this->rootPath . '/storage/uploads/documents';
        if (!is_dir($directory)) {
            mkdir($directory, 0750, true);
        }
        $storedName = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $storedName)) {
            throw new RuntimeException('No fue posible guardar el archivo.');
        }
        $query = $this->connection->prepare('INSERT INTO attachments (company_id, document_id, entity_type, entity_id, original_name, stored_path, mime_type, file_size, uploaded_by) VALUES (?, ?, \'documents\', ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, $documentId, $documentId, basename((string) $file['name']), 'storage/uploads/documents/' . $storedName, $mime, (int) $file['size'], $userId]);
        $id = (int) $this->connection->lastInsertId();
        (new AuditLog($this->connection, $this->companyId))->record($userId, 'ATTACH', 'attachments', $id, ['document_id' => $documentId]);
        return $id;
    }

    public function attachment(int $attachmentId): ?array
    {
        $query = $this->connection->prepare('SELECT original_name, stored_path, mime_type FROM attachments WHERE id = ? AND company_id = ? LIMIT 1');
        $query->execute([$attachmentId, $this->companyId]);
        $attachment = $query->fetch();
        if (!$attachment) {
            return null;
        }
        $file = realpath($this->rootPath . '/' . $attachment['stored_path']);
        $directory = realpath($this->rootPath . '/storage/uploads/documents');
        if (!$file || !$directory || !str_starts_with($file, $directory) || !is_file($file)) {
            return null;
        }
        $attachment['path'] = $file;
        return $attachment;
    }

    private function belongs(string $table, mixed $id): void
    {
        if (!in_array($table, ['suppliers', 'clients'], true)) {
            throw new RuntimeException('Referencia no válida.');
        }
        $query = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ? AND active = 1');
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('La referencia seleccionada no pertenece a esta empresa.');
        }
    }
}
