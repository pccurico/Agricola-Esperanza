<?php

declare(strict_types=1);

namespace AgroPCC\Library\Excel;

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
