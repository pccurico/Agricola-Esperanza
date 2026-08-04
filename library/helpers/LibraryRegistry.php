<?php

declare(strict_types=1);

namespace AgroPCC\Library\Helpers;

final class LibraryRegistry
{
    /**
     * Registry of library categories that must stay centralized in /library.
     */
    public static function categories(): array
    {
        return [
            'charts',
            'pdf',
            'excel',
            'barcode',
            'qrcode',
            'images',
            'mail',
            'reports',
            'import',
            'export',
            'documents',
            'validation',
            'security',
            'cache',
            'storage',
            'maps',
            'api',
            'helpers',
            'integrations',
        ];
    }
}
