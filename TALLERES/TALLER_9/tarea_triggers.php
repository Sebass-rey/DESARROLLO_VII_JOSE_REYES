<?php
require_once "config_pdo.php";

// 1) Probar trigger de membresía
function probarMembresia($pdo, $cliente_id) {
    echo "<h2>1) Trigger membresía cliente</h2>";

    // ver nivel antes
    $stmt = $pdo->prepare("SELECT nivel_membresia FROM clientes WHERE id = ?");
    $stmt->execute([$cliente_id]);
    $antes = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Nivel antes: " . $antes['nivel_membresia'] . "<br>";

    // crear una venta completada de prueba
    $stmt = $pdo->prepare("INSERT INTO ventas (cliente_id, total, estado) VALUES (?, ?, 'completada')");
    $stmt->execute([$cliente_id, 700.00]); // monto cualquiera

    // ver nivel después
    $stmt = $pdo->prepare("SELECT nivel_membresia FROM clientes WHERE id = ?");
    $stmt->execute([$cliente_id]);
    $despues = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Nivel después: " . $despues['nivel_membresia'] . "<br>";
}

// 2) Probar stats por categoría
function probarStatsCategoria($pdo, $venta_id, $producto_id) {
    echo "<h2>2) Trigger stats por categoría</h2>";

    // insertar un detalle de venta de prueba
    $stmt = $pdo->prepare("
        INSERT INTO detalles_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal)
        VALUES (?, ?, 1, 100.00, 100.00)
    ");
    $stmt->execute([$venta_id, $producto_id]);

    // buscar categoría
    $stmt = $pdo->prepare("SELECT categoria_id FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    $cat_id = $prod['categoria_id'];

    // ver stats
    $stmt = $pdo->prepare("SELECT * FROM stats_categorias WHERE categoria_id = ?");
    $stmt->execute([$cat_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stats) {
        echo "Categoría ID: " . $stats['categoria_id'] . "<br>";
        echo "Total ventas: " . $stats['total_ventas'] . "<br>";
        echo "Total productos: " . $stats['total_productos'] . "<br>";
        echo "Última actualización: " . $stats['ultima_actualizacion'] . "<br>";
    } else {
        echo "No hay stats para esa categoría.<br>";
    }
}

// 3) Probar alerta de stock crítico
function probarAlertaStock($pdo, $producto_id) {
    echo "<h2>3) Trigger alerta de stock</h2>";

    // bajar stock a un valor crítico (ej. 2)
    $stmt = $pdo->prepare("UPDATE productos SET stock = 2 WHERE id = ?");
    $stmt->execute([$producto_id]);

    // buscar última alerta de ese producto
    $stmt = $pdo->prepare("
        SELECT * FROM alertas_stock
        WHERE producto_id = ?
        ORDER BY fecha_alerta DESC
        LIMIT 1
    ");
    $stmt->execute([$producto_id]);
    $alerta = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($alerta) {
        echo "Producto ID: " . $alerta['producto_id'] . "<br>";
        echo "Stock actual: " . $alerta['stock_actual'] . "<br>";
        echo "Mensaje: " . $alerta['mensaje'] . "<br>";
        echo "Fecha: " . $alerta['fecha_alerta'] . "<br>";
    } else {
        echo "No hay alertas registradas para ese producto.<br>";
    }
}

// 4) Probar historial de estado del cliente
function probarHistorialEstadoCliente($pdo, $cliente_id) {
    echo "<h2>4) Trigger historial estado cliente</h2>";

    // cambiar el estado (si está TRUE lo pongo FALSE, y viceversa)
    $stmt = $pdo->prepare("SELECT activo FROM clientes WHERE id = ?");
    $stmt->execute([$cliente_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row === false) {
        echo "Cliente no encontrado.<br>";
        return;
    }

    $nuevoEstado = $row['activo'] ? 0 : 1;

    $stmt = $pdo->prepare("UPDATE clientes SET activo = ? WHERE id = ?");
    $stmt->execute([$nuevoEstado, $cliente_id]);

    // ver último registro en historial
    $stmt = $pdo->prepare("
        SELECT * FROM historial_estado_clientes
        WHERE cliente_id = ?
        ORDER BY fecha_cambio DESC
        LIMIT 1
    ");
    $stmt->execute([$cliente_id]);
    $hist = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($hist) {
        echo "Cliente ID: " . $hist['cliente_id'] . "<br>";
        echo "Estado anterior: " . $hist['estado_anterior'] . "<br>";
        echo "Estado nuevo: " . $hist['estado_nuevo'] . "<br>";
        echo "Fecha cambio: " . $hist['fecha_cambio'] . "<br>";
    } else {
        echo "No hay historial para ese cliente.<br>";
    }
}

/* ==== LLAMADAS DE PRUEBA ==== */
/* Ajusta los IDs a algo que exista en tu BD */

probarMembresia($pdo, 1);          // cliente 1
probarStatsCategoria($pdo, 1, 1);  // venta 1, producto 1
probarAlertaStock($pdo, 1);        // producto 1
probarHistorialEstadoCliente($pdo, 1); // cliente 1

$pdo = null;
?>
