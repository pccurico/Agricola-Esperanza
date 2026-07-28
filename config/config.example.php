<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'CampoSur',
        'url' => 'http://laesperanza',
        'timezone' => 'America/Santiago',
        'environment' => 'local',
    ],
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'laesperanza',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'session_name' => 'camposur_session',
        'csrf_key' => 'CAMBIAR_POR_UN_VALOR_ALEATORIO',
    ],
];
