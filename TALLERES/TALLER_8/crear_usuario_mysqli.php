<?php
require_once "config_mysqli.php";
require_once "log_errores.php";

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
        $email  = mysqli_real_escape_string($conn, $_POST['email']);

        $sql = "INSERT INTO usuarios (nombre, email) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ss", $nombre, $email);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al ejecutar la consulta: " . mysqli_error($conn));
        }

        echo "Usuario creado con éxito.";

        mysqli_stmt_close($stmt);
    }
} catch (Exception $e) {
    registrar_error("crear_usuario_mysqli: " . $e->getMessage());
    echo "Ocurrió un error al crear el usuario.";
}

mysqli_close($conn);
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div><label>Nombre</label><input type="text" name="nombre" required></div>
    <div><label>Email</label><input type="email" name="email" required></div>
    <input type="submit" value="Crear Usuario">
</form>
