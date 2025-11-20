<?php
require "config.php";

/* REGISTRAR PRESTAMO */
if(isset($_POST["prestar"])) {
    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn,"UPDATE libros SET cantidad = cantidad - 1 WHERE id=".$_POST["libro"]);
        mysqli_query($conn,"INSERT INTO prestamos (usuario_id,libro_id,fecha_prestamo) VALUES (".$_POST["usuario"].",".$_POST["libro"].",CURDATE())");
        mysqli_commit($conn);
    } catch(Exception $e) {
        mysqli_rollback($conn);
    }
}

/* DEVOLVER */
if(isset($_GET["devolver"])) {
    mysqli_query($conn,"UPDATE prestamos SET devuelto=1,fecha_devolucion=CURDATE() WHERE id=".$_GET["devolver"]);
    mysqli_query($conn,"UPDATE libros SET cantidad=cantidad+1 WHERE id=".$_GET["libro"]);
}

/* LISTAR */
$prestamos = mysqli_query($conn,"
    SELECT p.*, u.nombre as usuario, l.titulo as libro
    FROM prestamos p
    JOIN usuarios u ON p.usuario_id=u.id
    JOIN libros l ON p.libro_id=l.id
");
?>
<h2>Préstamos</h2>

<form method="post">
    Usuario ID: <input name="usuario">
    Libro ID: <input name="libro">
    <button name="prestar">Prestar</button>
</form>

<h3>Lista:</h3>
<?php while($p=mysqli_fetch_assoc($prestamos)): ?>
    <p>
        <?= $p["usuario"] ?> → <?= $p["libro"] ?>  
        <?php if(!$p["devuelto"]): ?>
            <a href="?devolver=<?= $p["id"] ?>&libro=<?= $p["libro_id"] ?>">Devolver</a>
        <?php endif; ?>
    </p>
<?php endwhile; ?>
