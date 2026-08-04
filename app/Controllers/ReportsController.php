<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class ReportsController extends BaseController
{
    public function handle(): array
    {
        $reportType = $this->normalizeReportType((string) ($_GET['report'] ?? 'executive'));
        $report = new \AgroPCC\Services\ReportService(database()->connection(), (int) $_SESSION['company_id']);
        $summary = $report->summary([
            'date_from' => (string) ($_GET['date_from'] ?? ''),
            'date_to' => (string) ($_GET['date_to'] ?? ''),
            'farm_id' => (int) ($_GET['farm_id'] ?? 0),
            'block_id' => (int) ($_GET['block_id'] ?? 0),
            'season_id' => (int) ($_GET['season_id'] ?? 0),
            'cost_center_id' => (int) ($_GET['cost_center_id'] ?? 0),
            'worker_id' => (int) ($_GET['worker_id'] ?? 0),
            'supervisor_id' => (int) ($_GET['supervisor_id'] ?? 0),
            'process' => (string) ($_GET['process'] ?? ''),
        ]);
        $summary['report_type'] = $reportType;

        if (($_GET['export'] ?? '') === 'csv') {
            (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'csv', 'report' => $reportType]);
            $this->exportCsv($summary, $reportType);
        }
        if (($_GET['export'] ?? '') === 'xlsx') {
            (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'xlsx', 'report' => $reportType]);
            $this->exportXlsx($summary, $reportType);
        }
        if (($_GET['export'] ?? '') === 'pdf') {
            (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'pdf', 'report' => $reportType]);
            $this->exportPdf($summary, $reportType);
        }
        return $summary;
    }

    private function normalizeReportType(string $reportType): string
    {
        $allowed = ['executive', 'costs', 'production', 'labor', 'documents'];
        $value = strtolower(trim($reportType));
        return in_array($value, $allowed, true) ? $value : 'executive';
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
