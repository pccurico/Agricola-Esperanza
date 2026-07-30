<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

abstract class BaseController
{
    protected function json(array $payload, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($statusCode !== 200) {
            http_response_code($statusCode);
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    protected function redirect(string $location): never
    {
        header('Location: ' . $location);
        exit;
    }
}
