<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class CompanySettings extends BaseService
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId, private readonly string $rootPath)
    {
    }

    public function company(): array
    {
        $query = $this->connection->prepare('SELECT id, legal_name, trade_name, tax_id, logo_path, email, phone, address, commune, region FROM companies WHERE id = ? LIMIT 1');
        $query->execute([$this->companyId]);
        return $query->fetch() ?: [];
    }

    public function update(array $input, array $logo): void
    {
        if (trim((string) ($input['legal_name'] ?? '')) === '' || trim((string) ($input['trade_name'] ?? '')) === '') {
            throw new RuntimeException('La razÃ³n social y el nombre visible son obligatorios.');
        }
        $logoPath = $this->storeLogo($logo);
        $fields = ['legal_name = ?', 'trade_name = ?', 'tax_id = ?', 'email = ?', 'phone = ?', 'address = ?', 'commune = ?', 'region = ?'];
        $values = [trim($input['legal_name']), trim($input['trade_name']), trim($input['tax_id']) ?: null, trim($input['email']) ?: null, trim($input['phone']) ?: null, trim($input['address']) ?: null, trim($input['commune']) ?: null, trim($input['region']) ?: null];
        if ($logoPath) {
            $fields[] = 'logo_path = ?';
            $values[] = $logoPath;
        }
        $values[] = $this->companyId;
        $this->connection->prepare('UPDATE companies SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($values);
    }

    private function storeLogo(array $logo): ?string
    {
        if (($logo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($logo['error'] !== UPLOAD_ERR_OK || $logo['size'] > 2 * 1024 * 1024) {
            throw new RuntimeException('El logo debe pesar menos de 2 MB.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($logo['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime])) {
            throw new RuntimeException('El logo debe ser JPG, PNG o WEBP.');
        }
        $directory = $this->rootPath . '/storage/uploads';
        if (!is_dir($directory)) {
            mkdir($directory, 0750, true);
        }
        $filename = 'company-logo-' . bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($logo['tmp_name'], $directory . '/' . $filename)) {
            throw new RuntimeException('No fue posible guardar el logo.');
        }
        return 'storage/uploads/' . $filename;
    }
}
