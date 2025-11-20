<?php
require "config.php";

if(isset($_POST["add"])) {
    $sql = "INSERT INTO usuarios (nombre,email,password) VALUES (?,?,?)";
    $stmt = mysqli_prepare($conn,$sql);
    mysqli_stmt_bind_param($stmt,"sss", $_POST["nombre"],$_POST["email"],$_POST["password"]);
    mysqli_stmt_execute($stmt);
}

$result = mysqli_query($conn,"SELECT * FROM usuarios");

if(isset($_GET["buscar"])) {
    $b = "%".$_GET["buscar"]."%";
    $stmt = mysqli_prepare($conn,"SELECT * FROM usuarios WHERE nombre LIKE ? OR email LIKE ?");
    mysqli_stmt_bind_param($stmt,"ss",$b,$b);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>
<h2>Usuarios</h2>

<form method="post">
    Nombre: <input name="nombre">
    Email: <input name="email">
    Contraseña: <input name="password">
    <button name="add">Registrar</button>
</form>

<form method="get">
    <input name="buscar" placeholder="Buscar">
</form>

<?php while($u=mysqli_fetch_assoc($result)): ?>
    <p><?= $u["nombre"]." - ".$u["email"] ?></p>
<?php endwhile; ?>
