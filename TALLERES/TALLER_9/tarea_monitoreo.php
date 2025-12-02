<?php
require_once "config_pdo.php";

function ejecutarConMonitoreo(PDO $pdo, string $nombre, string $sql, array $params = [])
{
    $inicio = microtime(true);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $fin = microtime(true);

    $tiempo = $fin - $inicio;
    $filas = $stmt->rowCount();

    // guardar en log_consultas
    $logSql = "
        INSERT INTO log_consultas (nombre_consulta, sql_text, tiempo_ejecucion, filas)
        VALUES (:nombre, :sql_text, :tiempo, :filas)
    ";

    $stmtLog = $pdo->prepare($logSql);
    $stmtLog->execute([
        ':nombre' => $nombre,
        ':sql_text' => $sql,
        ':tiempo' => $tiempo,
        ':filas' => $filas
    ]);

    echo "<strong>$nombre</strong><br>";
    echo "Tiempo: " . number_format($tiempo, 6) . " segundos<br>";
    echo "Filas: $filas<br><br>";
}

// Ejemplos de consultas críticas
ejecutarConMonitoreo(
    $pdo,
    "Productos por categoría",
    "SELECT id, nombre, precio FROM productos WHERE categoria_id = :cat",
    [':cat' => 1]
);

ejecutarConMonitoreo(
    $pdo,
    "Ventas completadas",
    "SELECT id, total FROM ventas WHERE estado = 'completada'"
);

ejecutarConMonitoreo(
    $pdo,
    "Reporte simple por cliente",
    "SELECT cliente_id, SUM(total) AS total_cliente 
     FROM ventas 
     GROUP BY cliente_id"
);

// Mostrar últimas entradas del log
echo "<h2>Últimas ejecuciones registradas</h2>";

$stmt = $pdo->query("
    SELECT nombre_consulta, tiempo_ejecucion, filas, fecha_ejecucion
    FROM log_consultas
    ORDER BY fecha_ejecucion DESC
    LIMIT 10
");

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($logs);
echo "</pre>";

$pdo = null;
?>
