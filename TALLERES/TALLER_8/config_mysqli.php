<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'taller8_user');
define('DB_PASSWORD', '123456');
define('DB_NAME', 'taller8_db');

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn === false) {
    die("ERROR: No se pudo conectar. " . mysqli_connect_error());
}

echo "Conexión exitosa a la base de datos con MySQLi.";
?>
