<?php
require "config.php"; // usa el PDO de pdo/config.php

// Registrar usuario
if (isset($_POST["add"])) {
    try {
        $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST["nombre"],
            $_POST["email"],
            $_POST["password"]
        ]);
        echo "Usuario registrado.<br>";
    } catch (Exception $e) {
        echo "Error al registrar usuario.<br>";
    }
}

// Listar todos o buscar
try {
    if (isset($_GET["buscar"]) && $_GET["buscar"] !== "") {
        $b = "%".$_GET["buscar"]."%";
        $sql = "SELECT * FROM usuarios WHERE nombre LIKE ? OR email LIKE ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$b, $b]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->query("SELECT * FROM usuarios");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $usuarios = [];
}
?>

<h2>Usuarios (PDO)</h2>

<form method="post">
    Nombre: <input name="nombre">
    Email: <input name="email">
    Contraseña: <input name="password" type="password">
    <button name="add">Registrar</button>
</form>

