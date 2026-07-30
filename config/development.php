<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);

return [
    'environment' => 'development',
    'stack' => [
        'name' => 'WampServer 64',
        'root' => 'C:/wamp64',
        'apache_root' => 'C:/wamp64/bin/apache',
        'mysql_root' => 'C:/wamp64/bin/mysql',
        'php_root' => 'C:/wamp64/bin/php',
    ],
    'php' => [
        'required_version' => '8.2.29',
        'required_major' => 8,
        'required_minor' => 2,
        'expected_sapi' => 'apache2handler',
        'executable' => 'C:/wamp64/bin/php/php8.2.29/php.exe',
        'document_root' => $projectRoot . '/public',
    ],
    'project' => [
        'root' => $projectRoot,
        'public' => $projectRoot . '/public',
        'storage' => $projectRoot . '/storage',
        'config' => $projectRoot . '/config',
    ],
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'pccurico_agricola',
    ],
];
