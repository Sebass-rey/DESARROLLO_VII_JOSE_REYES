<?php
require "config.php";

/* AÑADIR */
if(isset($_POST["add"])) {
    $sql = "INSERT INTO libros (titulo, autor, isbn, anio, cantidad) VALUES (?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST["titulo"], $_POST["autor"], $_POST["isbn"], $_POST["anio"], $_POST["cantidad"]
    ]);
}

/* LISTAR */
$result = $pdo->query("SELECT * FROM libros");

/* BUSCAR */
if(isset($_GET["buscar"])) {
    $b = "%".$_GET["buscar"]."%";
    $stmt = $pdo->prepare("SELECT * FROM libros WHERE titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?");
    $stmt->execute([$b,$b,$b]);
    $result = $stmt;
}
?>
<h2>Libros</h2>

<form method="post">
    Título: <input name="titulo">
    Autor: <input name="autor">
    ISBN: <input name="isbn">
    Año: <input name="anio">
    Cantidad: <input name="cantidad">
    <button name="add">Agregar</button>
</form>

<form method="get">
    <input name="buscar" placeholder="Buscar">
</form>

<?php foreach($result as $r): ?>
    <p><?= $r["titulo"]." - ".$r["autor"] ?></p>
<?php endforeach; ?>
