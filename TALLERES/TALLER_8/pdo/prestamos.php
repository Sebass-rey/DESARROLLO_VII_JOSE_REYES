<?php
require "config.php";

// Registrar préstamo (con transacción)
if (isset($_POST["prestar"])) {
    try {
        $pdo->beginTransaction();

        // Reducir stock del libro
        $stmt = $pdo->prepare("UPDATE libros SET cantidad = cantidad - 1 WHERE id = ?");
        $stmt->execute([$_POST["libro"]]);

        // Insertar préstamo
        $stmt = $pdo->prepare("INSERT INTO prestamos (usuario_id, libro_id, fecha_prestamo) 
                               VALUES (?, ?, CURDATE())");
        $stmt->execute([$_POST["usuario"], $_POST["libro"]]);

        $pdo->commit();
        echo "Préstamo registrado.<br>";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "Error al registrar el préstamo.<br>";
    }
}

// Registrar devolución (también en transacción para que se vea bonito)
if (isset($_GET["devolver"]) && isset($_GET["libro"])) {
    try {
        $pdo->beginTransaction();

        // Marcar préstamo como devuelto
        $stmt = $pdo->prepare("UPDATE prestamos 
                               SET devuelto = 1, fecha_devolucion = CURDATE() 
                               WHERE id = ?");
        $stmt->execute([$_GET["devolver"]]);

        // Devolver stock al libro
        $stmt = $pdo->prepare("UPDATE libros SET cantidad = cantidad + 1 WHERE id = ?");
        $stmt->execute([$_GET["libro"]]);

        $pdo->commit();
        echo "Devolución registrada.<br>";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "Error al registrar la devolución.<br>";
    }
}

// Listar préstamos con JOIN
try {
    $sql = "SELECT p.*, u.nombre AS usuario, l.titulo AS libro
            FROM prestamos p
            JOIN usuarios u ON p.usuario_id = u.id
            JOIN libros l ON p.libro_id = l.id
            ORDER BY p.fecha_prestamo DESC";
    $stmt = $pdo->query($sql);
    $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $prestamos = [];
}
?>

<h2>Préstamos (PDO)</h2>

<form method="post">
    Usuario ID: <input name="usuario">
    Libro ID: <input name="libro">
    <button name="prestar">Registrar préstamo</button>
</form>

<h3>Lista de préstamos:</h3>
<?php if (count($prestamos) > 0): ?>
    <?php foreach ($prestamos as $p): ?>
        <p>
            <?= $p["usuario"] ?> → <?= $p["libro"] ?> |
            Fecha préstamo: <?= $p["fecha_prestamo"] ?> |
            <?php if (!$p["devuelto"]): ?>
                <a href="?devolver=<?= $p["id"] ?>&libro=<?= $p["libro_id"] ?>">Devolver</a>
            <?php else: ?>
                Devuelto el <?= $p["fecha_devolucion"] ?>
            <?php endif; ?>
        </p>
    <?php endforeach; ?>
<?php else: ?>
    <p>No hay préstamos registrados.</p>
<?php endif; ?>
