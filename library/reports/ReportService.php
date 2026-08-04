<?php

declare(strict_types=1);

namespace CampoSur\Library\Reports;

final class ReportService
{
    public function exportPdf(array $data): array
    {
        return [
            'format' => 'pdf',
            'filename' => 'reporte.pdf',
            'content' => $data,
        ];
    }

    public function exportExcel(array $data): array
    {
        return [
            'format' => 'excel',
            'filename' => 'reporte.xlsx',
            'content' => $data,
        ];
    }

    public function exportCsv(array $data): array
    {
        return [
            'format' => 'csv',
            'filename' => 'reporte.csv',
            'content' => $data,
        ];
    }
}
