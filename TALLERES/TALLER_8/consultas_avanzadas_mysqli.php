<?php
require_once "config_mysqli.php";

// 1. Usuarios con número de publicaciones
$sql = "SELECT u.id, u.nombre, COUNT(p.id) AS num_publicaciones
        FROM usuarios u
        LEFT JOIN publicaciones p ON u.id = p.usuario_id
        GROUP BY u.id";

$result = mysqli_query($conn, $sql);

echo "<h3>Usuarios y número de publicaciones:</h3>";
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Usuario: " . $row['nombre'] . ", Publicaciones: " . $row['num_publicaciones'] . "<br>";
    }
}

// 2. Publicaciones con autor
$sql = "SELECT p.titulo, u.nombre AS autor, p.fecha_publicacion
        FROM publicaciones p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.fecha_publicacion DESC";

$result = mysqli_query($conn, $sql);

echo "<h3>Publicaciones con nombre del autor:</h3>";
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Título: " . $row['titulo'] . ", Autor: " . $row['autor'] . ", Fecha: " . $row['fecha_publicacion'] . "<br>";
    }
}

// 3. Usuario con más publicaciones
$sql = "SELECT u.nombre, COUNT(p.id) AS num_publicaciones
        FROM usuarios u
        LEFT JOIN publicaciones p ON u.id = p.usuario_id
        GROUP BY u.id
        ORDER BY num_publicaciones DESC
        LIMIT 1";

$result = mysqli_query($conn, $sql);

echo "<h3>Usuario con más publicaciones:</h3>";
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Nombre: " . $row['nombre'] . ", Número de publicaciones: " . $row['num_publicaciones'];
}
// 4. Mostrar las últimas 5 publicaciones con autor y fecha
$sql = "SELECT p.titulo, u.nombre AS autor, p.fecha_publicacion
        FROM publicaciones p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.fecha_publicacion DESC
        LIMIT 5";

$result = mysqli_query($conn, $sql);

echo "<h3>Últimas 5 publicaciones:</h3>";
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Título: " . $row['titulo'] . ", Autor: " . $row['autor'] . ", Fecha: " . $row['fecha_publicacion'] . "<br>";
    }
    mysqli_free_result($result);
} else {
    echo "Error: " . mysqli_error($conn) . "<br>";
}

// 5. Listar usuarios que no han realizado ninguna publicación
$sql = "SELECT u.id, u.nombre
        FROM usuarios u
        LEFT JOIN publicaciones p ON u.id = p.usuario_id
        WHERE p.id IS NULL";

$result = mysqli_query($conn, $sql);

echo "<h3>Usuarios sin publicaciones:</h3>";
if ($result) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "ID: " . $row['id'] . ", Nombre: " . $row['nombre'] . "<br>";
        }
    } else {
        echo "Todos los usuarios tienen al menos una publicación.<br>";
    }
    mysqli_free_result($result);
} else {
    echo "Error: " . mysqli_error($conn) . "<br>";
}

// 6. Calcular el promedio de publicaciones por usuario
$sql = "SELECT AVG(num_publicaciones) AS promedio
        FROM (
            SELECT u.id, COUNT(p.id) AS num_publicaciones
            FROM usuarios u
            LEFT JOIN publicaciones p ON u.id = p.usuario_id
            GROUP BY u.id
        ) AS t";

$result = mysqli_query($conn, $sql);

echo "<h3>Promedio de publicaciones por usuario:</h3>";
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Promedio: " . $row['promedio'] . "<br>";
    mysqli_free_result($result);
} else {
    echo "Error: " . mysqli_error($conn) . "<br>";
}

// 7. Publicación más reciente de cada usuario
$sql = "SELECT u.nombre, p.titulo, p.fecha_publicacion
        FROM usuarios u
        INNER JOIN publicaciones p ON u.id = p.usuario_id
        WHERE p.fecha_publicacion = (
            SELECT MAX(p2.fecha_publicacion)
            FROM publicaciones p2
            WHERE p2.usuario_id = u.id
        )
        ORDER BY u.nombre";

$result = mysqli_query($conn, $sql);

echo "<h3>Publicación más reciente de cada usuario:</h3>";
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Usuario: " . $row['nombre'] . ", Título: " . $row['titulo'] . ", Fecha: " . $row['fecha_publicacion'] . "<br>";
    }
    mysqli_free_result($result);
} else {
    echo "Error: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
