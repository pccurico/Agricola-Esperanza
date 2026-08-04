<?php

declare(strict_types=1);

$root = __DIR__;
$tree = [
    'library' => $root,
    'public/assets' => dirname($root) . '/public/assets',
    'vendor' => dirname($root) . '/vendor',
];

foreach ($tree as $label => $path) {
    echo $label . ': ' . (is_dir($path) ? 'present' : 'missing') . PHP_EOL;
}

$manualChart = dirname($root) . '/public/assets/js/chart.min.js';
if (is_file($manualChart)) {
    echo 'manual_chart: present' . PHP_EOL;
}
