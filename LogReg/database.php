<?php
$servername = 'localhost';
$username = 'jombina';
$password = 'WH6U9nzPqdAZGV2';
$dbname = 'ezrdb';
$port = 3306;
$charset = 'utf8mb4';

$conn = mysqli_connect($servername, $username, $password, $dbname, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!mysqli_set_charset($conn, $charset)) {
    die("Error setting character encoding: " . mysqli_error($conn));
}

if (!mysqli_select_db($conn, $dbname)) {
    die("Error selecting database: " . mysqli_error($conn));
}
?>
