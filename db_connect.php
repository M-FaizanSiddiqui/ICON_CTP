<?php 

// Production credentials should be configured on the server, not committed to Git.

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "icon_ctp_system_19072026";
// Create connection
$conn = mysqli_connect($servername, $username, $password , $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
