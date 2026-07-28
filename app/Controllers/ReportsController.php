<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class ReportsController
{
    public function handle(): array
    {
        $report = new \CampoSur\Services\ReportService(database()->connection(), (int) $_SESSION['company_id']);
        $summary = $report->summary();
        if (($_GET['export'] ?? '') === 'csv') {
            $this->exportCsv($summary);
        }
        return $summary;
    }

    private function exportCsv(array $summary): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="camposur-informe-costos.csv"');
        $output = fopen('php://output', 'wb');
        fputcsv($output, ['Clasificación', 'Total'], ';');
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
}
