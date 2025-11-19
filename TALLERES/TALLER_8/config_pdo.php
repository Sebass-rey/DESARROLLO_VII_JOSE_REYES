<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'taller8_user');
define('DB_PASSWORD', '123456');
define('DB_NAME', 'taller8_db');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexión exitosa a la base de datos con PDO.";
} catch (PDOException $e) {
    die("ERROR: No se pudo conectar. " . $e->getMessage());
}
?>
