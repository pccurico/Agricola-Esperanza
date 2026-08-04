<?php
require 'app/bootstrap.php';
$auth = new CampoSur\Services\Auth(database()->connection());
$resolver = new CampoSur\Services\Dashboard\PermissionResolver(
    $auth,
    1,
    1,
    ['inventory','production','costs','labor','procurement','reports','audit','machinery','documents','masters','settings','tools'],
    false
);
$widgets = [
    ['type' => 'kpi', 'permission' => 'costs.view', 'module' => 'costs'],
    ['type' => 'chart', 'permission' => 'production.view', 'module' => 'production'],
    ['type' => 'alert', 'permission' => 'inventory.view', 'module' => 'inventory'],
];
$filtered = $resolver->filterWidgets($widgets);
var_export($filtered);
