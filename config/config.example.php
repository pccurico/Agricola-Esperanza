<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'Sistema de Gestión Agrícola PCCURICO',
        'url' => '',
        'timezone' => 'America/Santiago',
        'environment' => 'production',
<<<<<<< HEAD
        'version' => 'v2.8.01',
=======
        'version' => 'v2.8.1',
>>>>>>> a4b804b (36)
    ],

    'updates' => [
        'github_repo' => 'pccurico/AgroPCC-Web',
        'github_api' => 'https://api.github.com/repos/pccurico/AgroPCC-Web/releases/latest',
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