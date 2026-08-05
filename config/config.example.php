<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'Sistema de Gestión Agrícola PCCURICO',
        'url' => '',
        'timezone' => 'America/Santiago',
        'environment' => 'production',
        'version' => 'v1.8.04',
    ],

    'updates' => [
        'github_repo' => 'pccurico/Agricola-Esperanza',
        'github_api' => 'https://api.github.com/repos/pccurico/Agricola-Esperanza/releases/latest',
    ],

    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => '',
        'user' => '',
        'password' => '',
        'charset' => 'utf8mb4',
    ],

    'security' => [
        'session_name' => 'pccurico_session',
        'csrf_key' => '',
    ],
];