<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/request_helpers.php';

$servername = icon_config('db.host', 'localhost');
$username = icon_config('db.user', 'root');
$password = icon_config('db.password', '');
$dbname = icon_config('db.name', '');
$dbport = (int)icon_config('db.port', 3306);

$conn = mysqli_connect($servername, $username, $password, $dbname, $dbport);
if (!$conn) {
	if(icon_config('app_debug', false)){
		die("Connection failed: " . mysqli_connect_error());
	}
	die("Database connection failed.");
}
mysqli_set_charset($conn, 'utf8mb4');
?>
