<?php
require 'app/bootstrap.php';
session_start();
$_SESSION['company_id'] = 1;
$_SESSION['user_id'] = 1;
$_SESSION['role_id'] = 1;
$_SERVER['REQUEST_METHOD'] = 'GET';
$response = (new CampoSur\Controllers\MachineryController())->handle();
foreach ((array) ($response['machinery'] ?? []) as $item) {
    $label = trim(((string) ($item['code'] ?? '')) . ' · ' . ((string) ($item['name'] ?? 'Sin nombre')));
    if ($label === '·') {
        $label = 'Sin maquinaria';
    }
    echo $label . PHP_EOL;
}
