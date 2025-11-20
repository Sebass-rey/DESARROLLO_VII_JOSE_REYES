<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=biblioteca_db", "root", "");
} catch (Exception $e) {
    die("Error de conexión.");
}
?>
