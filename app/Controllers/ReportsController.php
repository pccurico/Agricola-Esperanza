<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class ReportsController extends BaseController
{
    public function handle(): array
    {
        $reportConfig = $this->loadReportConfig();
        $reportKey = $this->normalizeReportKey((string) ($_GET['report_key'] ?? ''));
        $reportKey = $reportKey !== '' ? $reportKey : $this->normalizeReportKey((string) ($_GET['report'] ?? ''));

        if ($reportKey !== '') {
            $_GET['report'] = $reportKey;
        }

        if ($reportKey === '' && !isset($_GET['report'])) {
            return [
                'view' => 'reports/index.php',
                'reports' => $reportConfig,
                'metrics' => $this->centerMetrics($reportConfig),
                'permissions' => $this->permissionMap($reportConfig),
            ];
        }

        $reportType = $this->normalizeReportType((string) ($_GET['report'] ?? 'executive'));
        $report = new \AgroPCC\Services\ReportService(database()->connection(), (int) $_SESSION['company_id']);
        $response = $report->summary($this->buildFilters(), $reportType);
        $response['report_type'] = $reportType;
        $response['report_config'] = $reportConfig;
        $response['permissions'] = $this->permissionMap($reportConfig);

        if ($this->isJsonRequest()) {
            $this->json($response);
            exit;
        }

        $exportFormat = (string) ($_GET['export'] ?? '');
        if ($exportFormat === 'csv') {
            (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'csv', 'report' => $reportType]);
            $this->exportCsv($response, $reportType);
        }
        if ($exportFormat === 'xlsx') {
            (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['user_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'xlsx', 'report' => $reportType]);
            $this->exportXlsx($response, $reportType);
        }
        if ($exportFormat === 'pdf') {
            (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'pdf', 'report' => $reportType]);
            $this->exportPdf($response, $reportType);
        }

        return $response;
    }

    private function buildFilters(): array
    {
        $filters = [];
        $mapping = [
            'from' => 'date_from',
            'to' => 'date_to',
            'farm' => 'farm_id',
            'block' => 'block_id',
            'season' => 'season_id',
            'cost_center' => 'cost_center_id',
            'worker' => 'worker_id',
            'supervisor' => 'supervisor_id',
            'warehouse' => 'warehouse_id',
            'supplier' => 'supplier_id',
            'process' => 'process',
        ];

        foreach ($mapping as $fromKey => $toKey) {
            $value = $_GET[$fromKey] ?? null;
            if ($value === null || $value === '' || $value === '0' || $value === 0) {
                continue;
            }
            if ($toKey === 'date_from' || $toKey === 'date_to') {
                $filters[$toKey] = (string) $value;
            } elseif ($toKey === 'process') {
                $filters[$toKey] = (string) $value;
            } else {
                $filters[$toKey] = (int) $value;
            }
        }

        foreach (['date_from' => 'from', 'date_to' => 'to', 'farm_id' => 'farm', 'block_id' => 'block', 'season_id' => 'season', 'cost_center_id' => 'cost_center', 'worker_id' => 'worker', 'supervisor_id' => 'supervisor', 'warehouse_id' => 'warehouse', 'supplier_id' => 'supplier', 'process' => 'process'] as $legacyKey => $newKey) {
            if (array_key_exists($legacyKey, $_GET) && !array_key_exists($newKey, $_GET) && !array_key_exists($legacyKey, $filters)) {
                $value = $_GET[$legacyKey] ?? null;
                if ($value === null || $value === '' || $value === '0' || $value === 0) {
                    continue;
                }
                $filters[$legacyKey] = $legacyKey === 'process' ? (string) $value : (int) $value;
            }
        }

        $filters['date_from'] = isset($filters['date_from']) ? (string) $filters['date_from'] : (string) ($_GET['date_from'] ?? '');
        $filters['date_to'] = isset($filters['date_to']) ? (string) $filters['date_to'] : (string) ($_GET['date_to'] ?? '');
        $filters['farm_id'] = isset($filters['farm_id']) ? (int) $filters['farm_id'] : (int) ($_GET['farm_id'] ?? 0);
        $filters['block_id'] = isset($filters['block_id']) ? (int) $filters['block_id'] : (int) ($_GET['block_id'] ?? 0);
        $filters['season_id'] = isset($filters['season_id']) ? (int) $filters['season_id'] : (int) ($_GET['season_id'] ?? 0);
        $filters['cost_center_id'] = isset($filters['cost_center_id']) ? (int) $filters['cost_center_id'] : (int) ($_GET['cost_center_id'] ?? 0);
        $filters['worker_id'] = isset($filters['worker_id']) ? (int) $filters['worker_id'] : (int) ($_GET['worker_id'] ?? 0);
        $filters['supervisor_id'] = isset($filters['supervisor_id']) ? (int) $filters['supervisor_id'] : (int) ($_GET['supervisor_id'] ?? 0);
        $filters['process'] = isset($filters['process']) ? (string) $filters['process'] : (string) ($_GET['process'] ?? '');

        return $filters;
    }

    private function normalizeReportType(string $reportType): string
    {
        $allowed = array_merge(array_keys($this->loadReportConfig()), ['executive', 'costs', 'production', 'labor', 'inventory', 'procurement', 'finance', 'budgets', 'machinery', 'productivity', 'comparatives', 'trends', 'kpis']);
        $value = strtolower(trim($reportType));
        return in_array($value, $allowed, true) ? $value : 'executive';
    }

    private function isJsonRequest(): bool
    {
        if (isset($_GET['ajax']) && (string) $_GET['ajax'] === '1') {
            return true;
        }
        $acceptHeader = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        if (str_contains($acceptHeader, 'application/json')) {
            return true;
        }
        return strtoupper((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'XMLHTTPREQUEST';
    }

    private function normalizeReportKey(string $reportKey): string
    {
        $value = strtolower(trim($reportKey));
        $config = $this->loadReportConfig();
        return isset($config[$value]) ? $value : '';
    }

    private function loadReportConfig(): array
    {
        $configFile = dirname(__DIR__, 2) . '/config/reports.php';
        if (!is_file($configFile)) {
            return [];
        }
        return require $configFile;
    }

    private function permissionMap(array $reports): array
    {
        $permissionMap = [];
        foreach ($reports as $key => $report) {
            $permissionMap[$key] = isset($report['permission']) && (new \AgroPCC\Services\Auth(database()->connection()))->can((int) ($_SESSION['role_id'] ?? 0), (string) $report['permission'], (int) ($_SESSION['user_id'] ?? 0));
        }
        return $permissionMap;
    }

    private function centerMetrics(array $reports): array
    {
        $metrics = [];
        $service = new \AgroPCC\Services\ReportService(database()->connection(), (int) $_SESSION['company_id']);
        $now = new \DateTimeImmutable('today');
        $filters = ['date_from' => $now->format('Y-m-01'), 'date_to' => $now->format('Y-m-d')];
        foreach (array_keys($reports) as $reportKey) {
            $metrics[$reportKey] = $this->centerMetricForReport($reportKey, $service, $filters);
        }
        return $metrics;
    }

    private function centerMetricForReport(string $reportKey, \AgroPCC\Services\ReportService $service, array $filters): array
    {
        $default = ['label' => 'Indicador clave', 'value' => 0, 'change' => 0, 'updated_at' => '—'];
        $report = $service->summary($filters);
        $summary = (array) ($report['summary'] ?? []);
        $previousPeriod = $this->previousMonthRange($filters['date_from']);
        $previousReport = $service->summary(['date_from' => $previousPeriod['from'], 'date_to' => $previousPeriod['to']]);
        $previousSummary = (array) ($previousReport['summary'] ?? []);
        $value = 0;
        $label = 'Indicador clave';
        switch ($reportKey) {
            case 'executive':
                $value = (float) ($summary['total'] ?? 0);
                $label = 'Costo total';
                break;
            case 'production':
                $value = (float) ($summary['production'] ?? 0);
                $label = 'Producción';
                break;
            case 'costs':
                $value = (float) ($summary['total'] ?? 0);
                $label = 'Costo total';
                break;
            case 'profitability':
                $value = (float) ($summary['total'] ?? 0) > 0 ? (float) ($summary['production'] ?? 0) / (float) $summary['total'] : 0;
                $label = 'Rentabilidad';
                break;
            case 'labor':
                $value = (float) ($report['labor_summary']['total'] ?? 0);
                $label = 'Costo laboral';
                break;
            case 'inventory':
                $value = $this->countCriticalInventory();
                $label = 'Alertas críticas';
                break;
            case 'procurement':
                $value = $this->countPendingPurchases();
                $label = 'Órdenes abiertas';
                break;
            case 'finance':
                $value = $this->sumExpenses($filters);
                $label = 'Gastos totales';
                break;
            case 'budgets':
                $value = (float) ($report['budget']['execution'] ?? 0);
                $label = 'Ejecución %';
                break;
            case 'machinery':
                $value = $this->sumMachineryHours($filters);
                $label = 'Horas máquina';
                break;
            case 'productivity':
                $value = ($summary['production'] ?? 0) > 0 && ($report['labor_summary']['quantity'] ?? 0) > 0 ? (float) ($summary['production'] ?? 0) / (float) ($report['labor_summary']['quantity'] ?? 0) : 0;
                $label = 'Kg por jornada';
                break;
            case 'comparatives':
                $value = abs((float) ($summary['total'] ?? 0) - (float) ($previousSummary['total'] ?? 0));
                $label = 'Comparativo';
                break;
            case 'trends':
                $value = $this->countTrendRows($filters);
                $label = 'Puntos de tendencia';
                break;
            case 'kpis':
                $value = $this->countAvailableKpis();
                $label = 'KPIs activos';
                break;
            default:
                return $default;
        }
        $change = $this->computeChange($value, $previousSummary, $reportKey);
        $updatedAt = $this->latestUpdate($filters);
        return ['label' => $label, 'value' => $value, 'change' => $change, 'updated_at' => $updatedAt];
    }

    private function computeChange(float $value, array $previousSummary, string $reportKey): float
    {
        $summary = (array) ($previousSummary['summary'] ?? $previousSummary);
        $previous = 0;
        switch ($reportKey) {
            case 'executive':
            case 'costs':
                $previous = (float) ($summary['total'] ?? 0);
                break;
            case 'production':
                $previous = (float) ($summary['production'] ?? 0);
                break;
            case 'labor':
                $previous = (float) ($previousSummary['labor_summary']['total'] ?? 0);
                break;
            case 'productivity':
                $prevProd = (float) ($summary['production'] ?? 0);
                $prevLabor = (float) ($previousSummary['labor_summary']['quantity'] ?? 0);
                $previous = $prevLabor > 0 ? $prevProd / $prevLabor : 0;
                break;
            default:
                $previous = $value;
                break;
        }
        $denominator = abs((float) $previous);
        if ($denominator <= 0.0 || !is_finite($denominator)) {
            return 0;
        }
        return ($value - $previous) / $denominator * 100;
    }

    private function previousMonthRange(string $fromDate): array
    {
        $start = new \DateTimeImmutable($fromDate);
        $previousEnd = $start->modify('-1 day');
        $previousStart = $previousEnd->modify('first day of last month');
        return ['from' => $previousStart->format('Y-m-d'), 'to' => $previousEnd->format('Y-m-d')];
    }

    private function latestUpdate(array $filters): string
    {
        $query = database()->connection()->prepare('SELECT GREATEST(COALESCE(MAX(entry_date), "0000-00-00"), COALESCE(MAX(labor_date), "0000-00-00"), COALESCE(MAX(production_date), "0000-00-00")) AS latest FROM (SELECT entry_date, NULL AS labor_date, NULL AS production_date FROM expense_entries WHERE company_id = ? UNION ALL SELECT NULL AS entry_date, labor_date, NULL AS production_date FROM labor_entries WHERE company_id = ? UNION ALL SELECT NULL AS entry_date, NULL AS labor_date, production_date FROM production_entries WHERE company_id = ?) source');
        $query->execute([(int) $_SESSION['company_id'], (int) $_SESSION['company_id'], (int) $_SESSION['company_id']]);
        $latest = (string) $query->fetchColumn();
        if ($latest === '' || $latest === '0000-00-00') {
            return '—';
        }
        return (new \DateTimeImmutable($latest))->format('Y-m-d');
    }

    private function countCriticalInventory(): int
    {
        $query = database()->connection()->prepare('SELECT COUNT(*) FROM inventory_items i WHERE i.company_id = ? AND i.active = 1 AND EXISTS (SELECT 1 FROM inventory_movements m WHERE m.company_id = i.company_id AND m.item_id = i.id AND m.movement_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))');
        $query->execute([(int) $_SESSION['company_id']]);
        return (int) $query->fetchColumn();
    }

    private function countPendingPurchases(): int
    {
        $query = database()->connection()->prepare('SELECT COUNT(*) FROM purchase_orders WHERE company_id = ? AND status IN ("OPEN", "PENDING")');
        $query->execute([(int) $_SESSION['company_id']]);
        return (int) $query->fetchColumn();
    }

    private function sumExpenses(array $filters): float
    {
        $query = database()->connection()->prepare('SELECT COALESCE(SUM(amount), 0) FROM expense_entries WHERE company_id = ? AND entry_date BETWEEN ? AND ?');
        $query->execute([(int) $_SESSION['company_id'], $filters['date_from'], $filters['date_to']]);
        return (float) $query->fetchColumn();
    }

    private function sumMachineryHours(array $filters): float
    {
        $query = database()->connection()->prepare('SELECT COALESCE(SUM(meter), 0) FROM machinery WHERE company_id = ?');
        $query->execute([(int) $_SESSION['company_id']]);
        return (float) $query->fetchColumn();
    }

    private function countTrendRows(array $filters): int
    {
        $query = database()->connection()->prepare('SELECT COUNT(*) FROM production_entries WHERE company_id = ? AND production_date BETWEEN ? AND ?');
        $query->execute([(int) $_SESSION['company_id'], $filters['date_from'], $filters['date_to']]);
        return (int) $query->fetchColumn();
    }

    private function countAvailableKpis(): int
    {
        return 12;
    }

    private function exportCsv(array $summary, string $reportType): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="pccurico-' . $reportType . '-reporte.csv"');
        $output = fopen('php://output', 'wb');
        foreach ($this->exportRows($summary, $reportType) as $section) {
            fputcsv($output, [$section['title']], ';');
            fputcsv($output, $section['headers'], ';');
            foreach ($section['rows'] as $row) {
                fputcsv($output, $row, ';');
            }
            fputcsv($output, []);
        }
        fclose($output);
        exit;
    }

    private function exportPdf(array $summary, string $reportType): never
    {
        $company = $this->companyProfile();
        $companyName = trim((string) (($company['trade_name'] ?: $company['legal_name']) ?? 'Empresa sin nombre'));
        $reportLabel = match ($reportType) {
            'costs' => 'Reporte de costos',
            'production' => 'Reporte de producción',
            'labor' => 'Reporte de mano de obra',
            'documents' => 'Reporte de documentos',
            default => 'Reporte ejecutivo',
        };

        $lines = [
            $companyName,
            'Email: ' . trim((string) ($company['email'] ?? '')),
            'Teléfono: ' . trim((string) ($company['phone'] ?? '')),
            'Ubicación: ' . trim((string) (($company['commune'] ?? '') . (!empty($company['region']) ? ', ' . $company['region'] : ''))),
            '',
            $reportLabel,
            'Período: ' . ($summary['filters']['date_from'] ?? '—') . ' a ' . ($summary['filters']['date_to'] ?? '—'),
            'Tipo: ' . $reportType,
            '',
        ];

        foreach ($this->exportRows($summary, $reportType) as $section) {
            $lines[] = strtoupper((string) $section['title']);
            $lines[] = implode(' | ', array_map(static fn ($header): string => (string) $header, $section['headers']));
            foreach ($section['rows'] as $row) {
                $lines[] = implode(' | ', array_map(static fn ($value): string => (string) $value, $row));
            }
            $lines[] = '';
        }

        $contentStream = $this->buildPdfContentStream($lines);
        $objects = [
            1 => "<< /Type /Catalog /Pages 2 0 R >>",
            2 => "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
            3 => "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>",
            4 => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
            5 => "<< /Length " . strlen($contentStream) . " >>\nstream\n" . $contentStream . "\nendstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $objectNumber => $objectData) {
            $offsets[$objectNumber] = strlen($pdf);
            $pdf .= $objectNumber . " 0 obj\n" . $objectData . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="pccurico-' . $reportType . '-reporte.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    private function companyProfile(): array
    {
        $query = database()->connection()->prepare('SELECT legal_name, trade_name, email, phone, commune, region, logo_path FROM companies WHERE active = 1 ORDER BY id DESC LIMIT 1');
        $query->execute();
        return $query->fetch() ?: [];
    }

    private function logoPdfObject(string $logoPath): ?array
    {
        if ($logoPath === '') {
            return null;
        }
        $resolvedPath = dirname(__DIR__, 2) . '/' . ltrim($logoPath, '/');
        if (!is_file($resolvedPath)) {
            return null;
        }

        $imageData = @file_get_contents($resolvedPath);
        if ($imageData === false) {
            return null;
        }

        $image = @imagecreatefromstring($imageData);
        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $resized = imagecreatetruecolor((int) min(120, $width), (int) min(60, $height));
        if ($resized === false) {
            imagedestroy($image);
            return null;
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, min(120, $width), min(60, $height), $width, $height);
        ob_start();
        imagejpeg($resized, null, 80);
        $jpeg = ob_get_clean();
        imagedestroy($image);
        imagedestroy($resized);

        if ($jpeg === false || $jpeg === '') {
            return null;
        }

        return ['stream' => $jpeg, 'width' => min(120, $width), 'height' => min(60, $height)];
    }

    private function pdfDocumentLines(array $summary, string $reportType, array $company): array
    {
        $companyName = trim((string) (($company['trade_name'] ?: $company['legal_name']) ?? 'Empresa sin nombre'));
        $reportLabel = match ($reportType) {
            'costs' => 'Reporte de costos',
            'production' => 'Reporte de producción',
            'labor' => 'Reporte de mano de obra',
            'documents' => 'Reporte de documentos',
            default => 'Reporte ejecutivo',
        };

        $lines = [
            $companyName,
            'Email: ' . trim((string) ($company['email'] ?? '')),
            'Teléfono: ' . trim((string) ($company['phone'] ?? '')),
            'Ubicación: ' . trim((string) (($company['commune'] ?? '') . (!empty($company['region']) ? ', ' . $company['region'] : ''))),
            '',
            $reportLabel,
            'Período: ' . ($summary['filters']['date_from'] ?? '—') . ' a ' . ($summary['filters']['date_to'] ?? '—'),
            'Tipo: ' . $reportType,
            '',
        ];

        foreach ($this->exportRows($summary, $reportType) as $section) {
            $lines[] = strtoupper((string) $section['title']);
            $lines[] = implode(' | ', array_map(static fn ($header): string => (string) $header, $section['headers']));
            foreach ($section['rows'] as $row) {
                $lines[] = implode(' | ', array_map(static fn ($value): string => (string) $value, $row));
            }
            $lines[] = '';
        }

        return $lines;
    }

    private function buildPdfContentStream(array $lines): string
    {
        $content = "BT\n/F1 10 Tf\n";
        $y = 790;

        foreach ($lines as $line) {
            $content .= "50 {$y} Td\n" . $this->pdfTextString((string) $line) . " Tj\n";
            $y -= 14;
        }

        $content .= "ET\n";

        return $content;
    }

    private function pdfTextString(string $value): string
    {
        $utf16 = mb_convert_encoding($value, 'UTF-16BE', 'UTF-8');
        return '<' . bin2hex($utf16) . '>';
    }

    private function exportXlsx(array $summary, string $reportType): never
    {
        if (!class_exists(\ZipArchive::class)) {
            http_response_code(503);
            exit('La extensión ZIP de PHP es necesaria para exportar XLSX.');
        }
        $rows = [];
        foreach ($this->exportRows($summary, $reportType) as $section) {
            $rows[] = [$section['title']];
            $rows[] = $section['headers'];
            foreach ($section['rows'] as $row) {
                $rows[] = $row;
            }
            $rows[] = [];
        }
        $sheetRows = '';
        foreach ($rows as $rowIndex => $row) {
            $cells = '';
            foreach ($row as $columnIndex => $value) {
                $reference = chr(65 + $columnIndex) . ($rowIndex + 1);
                $cells .= '<c r="' . $reference . '" t="inlineStr"><is><t>' . htmlspecialchars((string) $value, ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            $sheetRows .= '<row r="' . ($rowIndex + 1) . '">' . $cells . '</row>';
        }
        $temporary = tempnam(sys_get_temp_dir(), 'pccurico-xlsx-');
        $zip = new \ZipArchive();
        $zip->open($temporary, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Informe" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' . $sheetRows . '</sheetData></worksheet>');
        $zip->close();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="pccurico-' . $reportType . '-reporte.xlsx"');
        readfile($temporary);
        unlink($temporary);
        exit;
    }

    private function exportRows(array $summary, string $reportType): array
    {
        $documentService = new \AgroPCC\Services\DocumentManagement(database()->connection(), (int) $_SESSION['company_id'], dirname(__DIR__, 2));
        $documents = $documentService->documents();

        $executive = [
            ['title' => 'Resumen ejecutivo', 'headers' => ['Indicador', 'Valor'], 'rows' => [
                ['Costo total', (string) ($summary['summary']['total'] ?? 0)],
                ['Costo por hectárea', (string) ($summary['summary']['cost_per_hectare'] ?? 0)],
                ['Producción', (string) ($summary['summary']['production'] ?? 0)],
                ['Costo por unidad', (string) ($summary['summary']['cost_per_unit'] ?? 0)],
                ['Presupuesto planificado', (string) ($summary['budget']['planned'] ?? 0)],
                ['Presupuesto ejecutado', (string) ($summary['budget']['actual'] ?? 0)],
            ]],
            ['title' => 'Costos por fundo', 'headers' => ['Fundo', 'Total'], 'rows' => array_map(static fn (array $row): array => [(string) ($row['name'] ?? ''), (string) ($row['total'] ?? 0)], $summary['farms'] ?? [])],
            ['title' => 'Costos por proceso', 'headers' => ['Proceso', 'Total'], 'rows' => array_map(static fn (array $row): array => [(string) ($row['process'] ?? ''), (string) ($row['total'] ?? 0)], $summary['processes'] ?? [])],
            ['title' => 'Producción por cuartel', 'headers' => ['Fundo', 'Cuartel', 'Cantidad', 'Unidad'], 'rows' => array_map(static fn (array $row): array => [(string) ($row['farm_name'] ?? ''), (string) ($row['block_name'] ?? ''), (string) ($row['quantity'] ?? 0), (string) ($row['unit'] ?? '')], $summary['blocks'] ?? [])],
            ['title' => 'Productividad de trabajadores', 'headers' => ['Trabajador', 'Jornadas', 'Total'], 'rows' => array_map(static fn (array $row): array => [(string) ($row['full_name'] ?? ''), (string) ($row['quantity'] ?? 0), (string) ($row['total'] ?? 0)], $summary['workers'] ?? [])],
            ['title' => 'Centros de costo', 'headers' => ['Centro', 'Categoría', 'Total'], 'rows' => array_map(static fn (array $row): array => [(string) ($row['name'] ?? ''), (string) ($row['category'] ?? ''), (string) ($row['total'] ?? 0)], $summary['centers'] ?? [])],
        ];

        $production = [
            ['title' => 'Producción por cuartel', 'headers' => ['Fundo', 'Cuartel', 'Cantidad', 'Unidad'], 'rows' => array_map(static fn (array $row): array => [(string) ($row['farm_name'] ?? ''), (string) ($row['block_name'] ?? ''), (string) ($row['quantity'] ?? 0), (string) ($row['unit'] ?? '')], $summary['blocks'] ?? [])],
            ['title' => 'Producción total', 'headers' => ['Indicador', 'Valor'], 'rows' => [
                ['Cantidad total', (string) ($summary['summary']['production'] ?? 0)],
                ['Unidad', (string) ($summary['summary']['production_unit'] ?? 'unidades')],
                ['Producción por hectárea', (string) ($summary['summary']['production_per_hectare'] ?? 0)],
                ['Registros', (string) ($summary['summary']['production_entries'] ?? 0)],
            ]],
        ];

        $labor = [
            ['title' => 'Productividad de trabajadores', 'headers' => ['Trabajador', 'Jornadas', 'Total'], 'rows' => array_map(static fn (array $row): array => [(string) ($row['full_name'] ?? ''), (string) ($row['quantity'] ?? 0), (string) ($row['total'] ?? 0)], $summary['workers'] ?? [])],
            ['title' => 'Resumen mano de obra', 'headers' => ['Indicador', 'Valor'], 'rows' => [
                ['Jornadas', (string) ($summary['labor_summary']['quantity'] ?? 0)],
                ['Costo total', (string) ($summary['labor_summary']['total'] ?? 0)],
                ['Trabajadores', (string) ($summary['labor_summary']['workers'] ?? 0)],
                ['Productividad', (string) ($summary['summary']['labor_productivity'] ?? 0)],
            ]],
        ];

        $costs = [
            ['title' => 'Resumen ejecutivo', 'headers' => ['Indicador', 'Valor'], 'rows' => [
                ['Costo total', (string) ($summary['summary']['total'] ?? 0)],
                ['Costo por hectárea', (string) ($summary['summary']['cost_per_hectare'] ?? 0)],
                ['Costo por unidad', (string) ($summary['summary']['cost_per_unit'] ?? 0)],
                ['Presupuesto ejecutado', (string) ($summary['budget']['actual'] ?? 0)],
            ]],
            ['title' => 'Costos por fundo', 'headers' => ['Fundo', 'Total'], 'rows' => array_map(static fn (array $row): array => [(string) ($row['name'] ?? ''), (string) ($row['total'] ?? 0)], $summary['farms'] ?? [])],
            ['title' => 'Costos por proceso', 'headers' => ['Proceso', 'Total'], 'rows' => array_map(static fn (array $row): array => [(string) ($row['process'] ?? ''), (string) ($row['total'] ?? 0)], $summary['processes'] ?? [])],
            ['title' => 'Centros de costo', 'headers' => ['Centro', 'Categoría', 'Total'], 'rows' => array_map(static fn (array $row): array => [(string) ($row['name'] ?? ''), (string) ($row['category'] ?? ''), (string) ($row['total'] ?? 0)], $summary['centers'] ?? [])],
        ];

        $documentsRows = [
            ['title' => 'Documentos registrados', 'headers' => ['Tipo', 'Número', 'Fecha', 'Proveedor', 'Cliente', 'Estado'], 'rows' => array_map(static fn (array $row): array => [
                (string) ($row['document_type'] ?? '—'),
                (string) ($row['document_number'] ?? '—'),
                (string) ($row['issue_date'] ?? '—'),
                (string) ($row['supplier_name'] ?? '—'),
                (string) ($row['client_name'] ?? '—'),
                (string) ($row['status'] ?? '—'),
            ], $documents)],
        ];

        return match ($reportType) {
            'costs' => $costs,
            'production' => $production,
            'labor' => $labor,
            'documents' => $documentsRows,
            default => $executive,
        };
    }
}
