<?php

declare(strict_types=1);

namespace AgroPCC\Library\Pdf;

final class PdfService
{
    public function render(array $lines): string
    {
        $content = "BT\n/F1 10 Tf\n50 790 Td\n";
        foreach ($lines as $index => $line) {
            $content .= '<' . bin2hex(mb_convert_encoding((string) $line, 'UTF-16BE', 'UTF-8')) . '> Tj\n';
            if ($index !== array_key_last($lines)) {
                $content .= '0 -14 Td\n';
            }
        }
        $content .= "ET\n";

        return $content;
    }
}
