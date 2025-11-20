<?php
require_once "config_pdo.php";
require_once "log_errores.php";

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST['nombre'];
        $email  = $_POST['email'];

        $sql = "INSERT INTO usuarios (nombre, email) VALUES (:nombre, :email)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);

        $stmt->execute();

        if ($stmt->errorCode() !== '00000') {
            $info = $stmt->errorInfo();
            throw new Exception("Error en la consulta: " . $info[2]);
        }

        echo "Usuario creado con éxito.";
    }
} catch (Exception $e) {
    registrar_error("crear_usuario_pdo: " . $e->getMessage());
    echo "Ocurrió un error al crear el usuario.";
}

$pdo = null;
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div><label>Nombre</label><input type="text" name="nombre" required></div>
    <div><label>Email</label><input type="email" name="email" required></div>
    <input type="submit" value="Crear Usuario">
</form>


