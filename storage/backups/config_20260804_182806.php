<?php

declare(strict_types=1);

return array (
  'app' => 
  array (
    'name' => 'Sistema de Gestión Agrícola PCCURICO',
    'url' => 'http://laesperanza',
    'timezone' => 'America/Santiago',
    'environment' => 'produccion',
    'version' => 'v1.8.04',
  ),
  'database' => 
  array (
    'host' => 'localhost',
    'port' => 3306,
    'name' => 'laesperanza',
    'user' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
  ),
  'security' => 
  array (
    'session_name' => 'pccurico_session',
    'csrf_key' => '2cafa2fb88ad5b55e7c462c582841d08f879784834458b9f9418779d99f66235',
  ),
);
