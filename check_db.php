<?php
$mysqli = @new mysqli('127.0.0.1', 'root', '');
if ($mysqli->connect_error) {
    echo "MySQL Error: " . $mysqli->connect_error . "\n";
} else {
    echo "MySQL Connected Successfully!\n";
    $mysqli->query("CREATE DATABASE IF NOT EXISTS ebay_profit_db");
    echo "Database ebay_profit_db created/verified!\n";
    $res = $mysqli->query("SHOW DATABASES");
    while ($row = $res->fetch_row()) {
        echo " - " . $row[0] . "\n";
    }
}
