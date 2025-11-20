<?php
require "config.php";

/* AÑADIR */
if(isset($_POST["add"])) {
    $sql = "INSERT INTO libros (titulo, autor, isbn, anio, cantidad) VALUES (?,?,?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssii",
        $_POST["titulo"], $_POST["autor"], $_POST["isbn"], $_POST["anio"], $_POST["cantidad"]);
    mysqli_stmt_execute($stmt);
}

/* LISTAR */
$result = mysqli_query($conn, "SELECT * FROM libros");

/* BUSCAR */
if(isset($_GET["buscar"])) {
    $b = "%".$_GET["buscar"]."%";
    $sql = "SELECT * FROM libros WHERE titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $b,$b,$b);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
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

<?php while($r=mysqli_fetch_assoc($result)): ?>
    <p>
        <?php echo $r["titulo"]." - ".$r["autor"]; ?>
    </p>
<?php endwhile; ?>
