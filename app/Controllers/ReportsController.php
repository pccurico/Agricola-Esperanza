<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class ReportsController extends BaseController
{
    public function handle(): array
    {
        $reportType = $this->normalizeReportType((string) ($_GET['report'] ?? 'executive'));
        $report = new \CampoSur\Services\ReportService(database()->connection(), (int) $_SESSION['company_id']);
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
            (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'csv', 'report' => $reportType]);
            $this->exportCsv($summary, $reportType);
        }
        if (($_GET['export'] ?? '') === 'xlsx') {
            (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'xlsx', 'report' => $reportType]);
            $this->exportXlsx($summary, $reportType);
        }
        if (($_GET['export'] ?? '') === 'pdf') {
            (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'pdf', 'report' => $reportType]);
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
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="pccurico-' . $reportType . '-reporte.html"');
        echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Reporte agrícola</title><style>body{font-family:Arial,sans-serif;padding:24px;color:#1f2937}h1{font-size:22px;margin-bottom:8px}h2{font-size:16px;margin:24px 0 8px}table{border-collapse:collapse;width:100%;margin-bottom:18px;font-size:12px}th,td{border:1px solid #d1d5db;padding:8px;text-align:left;vertical-align:top}th{background:#f3f4f6;font-weight:700}small{color:#6b7280}.report-header{margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #e5e7eb}</style></head><body><div class="report-header"><h1>Reporte agrícola</h1><small>Tipo: ' . htmlspecialchars($reportType, ENT_QUOTES, 'UTF-8') . '</small></div>';
        foreach ($this->exportRows($summary, $reportType) as $section) {
            echo '<h2>' . htmlspecialchars((string) $section['title'], ENT_QUOTES, 'UTF-8') . '</h2><table><tr>';
            foreach ($section['headers'] as $header) {
                echo '<th>' . htmlspecialchars((string) $header, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            echo '</tr>';
            foreach ($section['rows'] as $row) {
                echo '<tr>';
                foreach ($row as $value) {
                    echo '<td>' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        }
        echo '</body></html>';
        exit;
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
        $documentService = new \CampoSur\Services\DocumentManagement(database()->connection(), (int) $_SESSION['company_id'], dirname(__DIR__, 2));
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
