<?php
require_once "config_pdo.php";

/**
 * Registra un error de transacción en la tabla log_transacciones.
 */
function registrarErrorTransaccion(PDO $pdo, string $nombre, string $detalle, string $mensajeError)
{
    try {
        $sql = "INSERT INTO log_transacciones (nombre_operacion, detalle, mensaje_error)
                VALUES (:nombre, :detalle, :error)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'  => $nombre,
            ':detalle' => $detalle,
            ':error'   => $mensajeError
        ]);
    } catch (PDOException $e) {
        // Si hasta el log falla, solo lo ignoramos para no romper más cosas.
    }
}

/**
 * 1) Sistema de procesamiento de pedidos con múltiples SAVEPOINT.
 * Usa ventas + detalles_venta + productos (stock).
 */
function procesarPedidoConSavepoints(PDO $pdo, int $clienteId, array $items)
{
    echo "<h2>1) Procesar pedido con SAVEPOINT</h2>";

    try {
        $pdo->beginTransaction();

        // Crear la venta como "pendiente" inicialmente
        $sqlVenta = "INSERT INTO ventas (cliente_id, total, estado) VALUES (?, 0, 'pendiente')";
        $stmt = $pdo->prepare($sqlVenta);
        $stmt->execute([$clienteId]);
        $ventaId = (int)$pdo->lastInsertId();

        $pdo->exec("SAVEPOINT sp_venta_creada");

        $totalVenta      = 0;
        $itemsProcesados = 0;

        foreach ($items as $i => $item) {
            $productoId = (int)$item['producto_id'];
            $cantidad   = (int)$item['cantidad'];

            try {
                // Guardar punto antes de procesar el item
                $pdo->exec("SAVEPOINT sp_item_" . $i);

                // Bloquear fila de producto para evitar problemas de concurrencia
                $sqlProd = "SELECT stock, precio 
                            FROM productos 
                            WHERE id = ? 
                            FOR UPDATE";
                $stmt = $pdo->prepare($sqlProd);
                $stmt->execute([$productoId]);
                $prod = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$prod) {
                    throw new Exception("Producto $productoId no existe");
                }

                if ($prod['stock'] < $cantidad) {
                    throw new Exception("Stock insuficiente para producto $productoId");
                }

                // Actualizar stock
                $sqlUpdate = "UPDATE productos SET stock = stock - ? WHERE id = ?";
                $stmt = $pdo->prepare($sqlUpdate);
                $stmt->execute([$cantidad, $productoId]);

                // Insertar detalle
                $subtotal = $prod['precio'] * $cantidad;

                $sqlDet = "INSERT INTO detalles_venta 
                           (venta_id, producto_id, cantidad, precio_unitario, subtotal)
                           VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sqlDet);
                $stmt->execute([$ventaId, $productoId, $cantidad, $prod['precio'], $subtotal]);

                $totalVenta      += $subtotal;
                $itemsProcesados += 1;

            } catch (Exception $eItem) {
                // Volver al estado antes de este item, pero NO tirar toda la venta
                $pdo->exec("ROLLBACK TO SAVEPOINT sp_item_" . $i);
                $msg = "Error en item $i (producto $productoId): " . $eItem->getMessage();
                echo $msg . "<br>";
                registrarErrorTransaccion($pdo, "procesar_pedido_item", "venta_id=$ventaId", $msg);
                // seguimos con el siguiente producto
            }
        }

        if ($itemsProcesados === 0) {
            // Ningún item se pudo procesar, revertimos todo
            $pdo->rollBack();
            echo "Ningún item se pudo procesar. Pedido cancelado.<br>";
            registrarErrorTransaccion(
                $pdo,
                "procesar_pedido",
                "cliente_id=$clienteId",
                "Pedido cancelado, todos los items fallaron."
            );
            return;
        }

        // Actualizar total y marcar como completada
        $sqlUpdateVenta = "UPDATE ventas SET total = ?, estado = 'completada' WHERE id = ?";
        $stmt = $pdo->prepare($sqlUpdateVenta);
        $stmt->execute([$totalVenta, $ventaId]);

        $pdo->commit();
        echo "Pedido procesado. Venta ID: $ventaId, Total: $totalVenta, Items OK: $itemsProcesados<br>";

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error general en la transacción de pedido: " . $e->getMessage() . "<br>";
        registrarErrorTransaccion(
            $pdo,
            "procesar_pedido",
            "cliente_id=$clienteId",
            $e->getMessage()
        );
    }
}

/**
 * 2) Actualización concurrente de inventario con RETRY (para deadlocks).
 */
function actualizarInventarioConRetry(PDO $pdo, int $productoId, int $deltaCantidad, int $maxIntentos = 3)
{
    echo "<h2>2) Actualizar inventario con retry</h2>";

    $intento = 0;

    while ($intento < $maxIntentos) {
        try {
            $intento++;
            $pdo->beginTransaction();

            // Bloquear el producto
            $sql = "SELECT stock FROM productos WHERE id = ? FOR UPDATE";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$productoId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new Exception("Producto no encontrado");
            }

            $nuevoStock = $row['stock'] + $deltaCantidad;
            if ($nuevoStock < 0) {
                throw new Exception("Stock no puede quedar negativo");
            }

            $sqlUpdate = "UPDATE productos SET stock = ? WHERE id = ?";
            $stmt = $pdo->prepare($sqlUpdate);
            $stmt->execute([$nuevoStock, $productoId]);

            $pdo->commit();
            echo "Stock actualizado correctamente. Nuevo stock: $nuevoStock (intento $intento)<br>";
            return;

        } catch (PDOException $e) {
            $pdo->rollBack();

            // 1213 = deadlock
            $codigo = isset($e->errorInfo[1]) ? $e->errorInfo[1] : null;

            if ($codigo === 1213 && $intento < $maxIntentos) {
                echo "Deadlock detectado, reintentando... (intento $intento)<br>";
                sleep(1);
                continue;
            }

            echo "Error al actualizar inventario: " . $e->getMessage() . "<br>";
            registrarErrorTransaccion(
                $pdo,
                "actualizar_inventario",
                "producto_id=$productoId, delta=$deltaCantidad",
                $e->getMessage()
            );
            return;

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Error al actualizar inventario: " . $e->getMessage() . "<br>";
            registrarErrorTransaccion(
                $pdo,
                "actualizar_inventario",
                "producto_id=$productoId, delta=$deltaCantidad",
                $e->getMessage()
            );
            return;
        }
    }
}

/**
 * 3) “Transacción distribuida” entre varias tablas:
 *    - ventas
 *    - pagos_ventas
 *    - log_transacciones (solo para dejar constancia)
 */
function registrarPagoVenta(PDO $pdo, int $ventaId, float $monto, string $metodo = 'efectivo')
{
    echo "<h2>3) Registrar pago de venta (varias tablas)</h2>";

    try {
        $pdo->beginTransaction();

        // Verificar venta
        $stmt = $pdo->prepare("SELECT total, estado FROM ventas WHERE id = ?");
        $stmt->execute([$ventaId]);
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$venta) {
            throw new Exception("La venta $ventaId no existe");
        }

        // Insertar pago
        $sqlPago = "INSERT INTO pagos_ventas (venta_id, monto, metodo) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sqlPago);
        $stmt->execute([$ventaId, $monto, $metodo]);

        // Si el monto cubre o supera el total, marcar como completada
        if ($monto >= $venta['total']) {
            $sqlUpdate = "UPDATE ventas SET estado = 'completada' WHERE id = ?";
            $stmt = $pdo->prepare($sqlUpdate);
            $stmt->execute([$ventaId]);
        }

        // Registrar en el log (aunque no haya error, a modo de traza simple)
        registrarErrorTransaccion(
            $pdo,
            "registrar_pago",
            "venta_id=$ventaId, monto=$monto",
            "OK"
        );

        $pdo->commit();
        echo "Pago registrado correctamente para la venta $ventaId<br>";

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error al registrar pago: " . $e->getMessage() . "<br>";
        registrarErrorTransaccion(
            $pdo,
            "registrar_pago",
            "venta_id=$ventaId, monto=$monto",
            $e->getMessage()
        );
    }
}

//PRUEBAS DE EJEMPLO


// 1) Pedido con varios productos (el último puede fallar si no hay stock)
$itemsEjemplo = [
    ['producto_id' => 1, 'cantidad' => 1],
    ['producto_id' => 2, 'cantidad' => 1],
    ['producto_id' => 3, 'cantidad' => 50] // esta cantidad grande suele forzar error
];
procesarPedidoConSavepoints($pdo, 1, $itemsEjemplo);

// 2) Actualizar inventario (restar 2 unidades al producto 1)
actualizarInventarioConRetry($pdo, 1, -2);

// 3) Registrar pago para la venta 1
registrarPagoVenta($pdo, 1, 1000.00, 'tarjeta');

$pdo = null;
?>
