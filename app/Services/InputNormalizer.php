<?php

declare(strict_types=1);

namespace CampoSur\Services;

final class InputNormalizer
{
    public static function text(string $value): string
    {
        return mb_strtoupper(trim($value), 'UTF-8');
    }

    public static function email(string $value): string
    {
        return mb_strtolower(trim($value), 'UTF-8');
    }

    public static function rut(string $value): string
    {
        $clean = preg_replace('/[^0-9kK]/', '', $value) ?? '';
        if (strlen($clean) < 2) {
            return mb_strtoupper($clean, 'UTF-8');
        }

        $checkDigit = mb_strtoupper(substr($clean, -1), 'UTF-8');
        $body = substr($clean, 0, -1);
        $body = preg_replace('/^0+(?=\d)/', '', $body) ?: '0';
        $formattedBody = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $body) ?? $body;

        return $formattedBody . '-' . $checkDigit;
    }

    public static function phone(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';
        if (str_starts_with($digits, '56')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 8) {
            $digits = '9' . $digits;
        }
        if (strlen($digits) !== 9) {
            return trim($value);
        }

        $area = substr($digits, 0, 1);
        return '+56 ' . $area . ' ' . substr($digits, 1, 4) . ' ' . substr($digits, 5, 4);
    }
}
