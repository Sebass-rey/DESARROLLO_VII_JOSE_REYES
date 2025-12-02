<?php
// Conexión MySQLi básica
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "taller9_db";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Error de conexión MySQLi: " . mysqli_connect_error());
}
?>
