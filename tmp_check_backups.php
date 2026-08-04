<?php
require 'app/bootstrap.php';
ini_set('display_errors', '1');
error_reporting(E_ALL);
$query = database()->connection()->query('SELECT id, file_path, file_size, status, created_at, created_by FROM backup_records ORDER BY id DESC LIMIT 10');
foreach ($query as $row) {
    foreach ($row as $key => $value) {
        if (is_int($key)) {
            continue;
        }
        echo "$key=$value\n";
    }
    echo "---\n";
}
