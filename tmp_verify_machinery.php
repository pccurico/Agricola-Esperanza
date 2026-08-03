<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require 'app/bootstrap.php';
session_start();
$_SESSION['company_id'] = 1;
$_SESSION['user_id'] = 1;
$_SESSION['role_id'] = 1;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['module'] = 'machinery';
ob_start();
require 'public/index.php';
$content = ob_get_clean();
$hasIssue = str_contains($content, 'Undefined array key') || str_contains($content, 'Trying to access array offset on value of type null') || str_contains($content, 'Warning:');
echo $hasIssue ? 'HAS_WARNING' : 'OK';
echo PHP_EOL;
if ($hasIssue) {
    $pos = strpos($content, 'Warning:');
    if ($pos !== false) {
        echo substr($content, max(0, $pos - 300), 1200);
    }
}
