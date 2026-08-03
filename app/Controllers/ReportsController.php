<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class ReportsController extends BaseController
{
    public function handle(): array
    {
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
        if (($_GET['export'] ?? '') === 'csv') {
            (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'csv']);
            $this->exportCsv($summary);
        }
        if (($_GET['export'] ?? '') === 'xlsx') {
            (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'xlsx']);
            $this->exportXlsx($summary);
        }
        if (($_GET['export'] ?? '') === 'pdf') {
            (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'EXPORT', 'reports', null, ['format' => 'pdf']);
            $this->exportPdf($summary);
        }
        return $summary;
    }

    private function exportCsv(array $summary): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="pccurico-informe-ejecutivo.csv"');
        $output = fopen('php://output', 'wb');
        foreach ($this->exportRows($summary) as $section) {
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

    private function exportPdf(array $summary): never
    {
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="pccurico-informe-ejecutivo.html"');
        echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Dashboard ejecutivo agrícola</title><style>body{font-family:Arial,sans-serif;color:#222}table{border-collapse:collapse;width:100%;margin-bottom:24px}th,td{border:1px solid #ccc;padding:8px;text-align:left}h1{font-size:22px}h2{font-size:16px}</style></head><body><h1>Dashboard ejecutivo agrícola</h1>';
        foreach ($this->exportRows($summary) as $section) {
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
        echo '<script>window.print()</script></body></html>';
        exit;
    }

    private function exportXlsx(array $summary): never
    {
        if (!class_exists(\ZipArchive::class)) {
            http_response_code(503);
            exit('La extensiÃ³n ZIP de PHP es necesaria para exportar XLSX.');
        }
        $rows = [];
        foreach ($this->exportRows($summary) as $section) {
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
                $cells .= '<c r="' . $reference . '" t="inlineStr"><is><t>' . htmlspecialchars($value, ENT_XML1, 'UTF-8') . '</t></is></c>';
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
        header('Content-Disposition: attachment; filename="pccurico-informe-costos.xlsx"');
        readfile($temporary);
        unlink($temporary);
        exit;
    }

    private function exportRows(array $summary): array
    {
        return [
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
    }
}
