<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'Sistema de Gestión Agrícola PCCURICO',
        'url' => 'http://localhost',
        'timezone' => 'America/Santiago',
        'environment' => 'local',
        'version' => 'v1.8.04',
    ],
    'updates' => [
        'github_repo' => 'pccurico/Agricola-Esperanza',
        'github_api' => 'https://api.github.com/repos/pccurico/Agricola-Esperanza/releases/latest',
    ],
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'pccurico_agricola',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'session_name' => 'pccurico_session',
        'csrf_key' => 'CAMBIAR_POR_UN_VALOR_ALEATORIO',
    ],
];
