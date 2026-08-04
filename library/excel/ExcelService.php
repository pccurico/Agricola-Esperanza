<?php

declare(strict_types=1);

namespace CampoSur\Library\Excel;

final class ExcelService
{
    public function export(array $rows): array
    {
        return [
            'filename' => 'reporte.xlsx',
            'rows' => $rows,
        ];
    }
}
