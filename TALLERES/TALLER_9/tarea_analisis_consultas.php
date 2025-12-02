<?php
require_once "config_pdo.php";

function analizarConsultaSimple(PDO $pdo, string $descripcion, string $sql)
{
    echo "<h2>$descripcion</h2>";
    echo "<pre>SQL: " . htmlspecialchars($sql) . "</pre>";

    try {
        // EXPLAIN normal (más sencillo que FORMAT=JSON)
        $stmt = $pdo->prepare("EXPLAIN " . $sql);
        $stmt->execute();
        $plan = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h4>Plan de ejecución (EXPLAIN):</h4>";
        echo "<pre>";
        print_r($plan);
        echo "</pre>";

        // medir tiempo
        $inicio = microtime(true);
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $fin = microtime(true);

        $tiempo = $fin - $inicio;

        echo "Tiempo de ejecución: " . number_format($tiempo, 6) . " segundos<br>";
        echo "Filas devueltas: " . $stmt->rowCount() . "<br><br>";

    } catch (PDOException $e) {
        echo "Error al analizar la consulta: " . $e->getMessage() . "<br>";
    }
}

$consultas = [
    "Productos por categoría " => "
        SELECT p.id, p.nombre, p.precio, p.stock
        FROM productos p
        WHERE p.categoria_id = 1
    ",
    "Ventas por rango de fechas " => "
        SELECT v.id, v.fecha_venta, v.total
        FROM ventas v
        WHERE v.fecha_venta BETWEEN '2023-01-01' AND '2030-12-31'
    ",
    "Ventas por cliente y estado " => "
        SELECT v.id, v.total, v.estado
        FROM ventas v
        WHERE v.cliente_id = 1
          AND v.estado = 'completada'
    ",
    "Búsqueda de texto usando FULLTEXT" => "
        SELECT id, nombre, precio
        FROM productos
        WHERE MATCH(nombre, descripcion) AGAINST('laptop' IN NATURAL LANGUAGE MODE)
    "
];

foreach ($consultas as $desc => $sql) {
    analizarConsultaSimple($pdo, $desc, $sql);
}

$pdo = null;
?>
