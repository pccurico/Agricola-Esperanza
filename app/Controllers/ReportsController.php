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
        header('Content-Disposition: attachment; filename="pccurico-informe-costos.csv"');
        $output = fopen('php://output', 'wb');
        fputcsv($output, ['ClasificaciÃ³n', 'Total'], ';');
        foreach ($summary['categories'] as $row) {
            fputcsv($output, [$row['category'], $row['total']], ';');
        }
        fputcsv($output, []);
        fputcsv($output, ['Temporada', 'Total'], ';');
        foreach ($summary['seasons'] as $row) {
            fputcsv($output, [$row['name'], $row['total']], ';');
        }
        fclose($output);
        exit;
    }

    private function exportPdf(array $summary): never
    {
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="pccurico-informe-costos.html"');
        echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Informe de costos</title><style>body{font-family:Arial,sans-serif;color:#222}table{border-collapse:collapse;width:100%;margin-bottom:24px}th,td{border:1px solid #ccc;padding:8px;text-align:left}h1{font-size:22px}</style></head><body><h1>Informe de costos</h1><h2>Resumen por categorÃ­a</h2><table><tr><th>CategorÃ­a</th><th>Total</th></tr>';
        foreach ($summary['categories'] as $row) {
            echo '<tr><td>' . htmlspecialchars((string) $row['category'], ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars((string) $row['total'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
        }
        echo '</table><h2>Resumen por temporada</h2><table><tr><th>Temporada</th><th>Total</th></tr>';
        foreach ($summary['seasons'] as $row) {
            echo '<tr><td>' . htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars((string) $row['total'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
        }
        echo '</table><script>window.print()</script></body></html>';
        exit;
    }

    private function exportXlsx(array $summary): never
    {
        if (!class_exists(\ZipArchive::class)) {
            http_response_code(503);
            exit('La extensiÃ³n ZIP de PHP es necesaria para exportar XLSX.');
        }
        $rows = [['ClasificaciÃ³n', 'Total']];
        foreach ($summary['categories'] as $row) {
            $rows[] = [(string) $row['category'], (string) $row['total']];
        }
        $rows[] = [];
        $rows[] = ['Temporada', 'Total'];
        foreach ($summary['seasons'] as $row) {
            $rows[] = [(string) $row['name'], (string) $row['total']];
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
}
