<?php
require_once "config_pdo.php";

// 1) Devolución de producto
function procesarDevolucionPDO($pdo, $venta_id, $producto_id, $cantidad) {
    echo "<h3>Devolución (PDO)</h3>";
    try {
        $stmt = $pdo->prepare("CALL sp_procesar_devolucion(:venta, :producto, :cant)");
        $stmt->bindParam(':venta', $venta_id, PDO::PARAM_INT);
        $stmt->bindParam(':producto', $producto_id, PDO::PARAM_INT);
        $stmt->bindParam(':cant', $cantidad, PDO::PARAM_INT);
        $stmt->execute();

        echo "Venta: $venta_id - Producto: $producto_id - Cantidad devuelta: $cantidad<br>";
    } catch (PDOException $e) {
        echo "Error en devolución (PDO): " . $e->getMessage() . "<br>";
    }
}

// 2) Descuento según historial de cliente
function aplicarDescuentoClientePDO($pdo, $cliente_id) {
    echo "<h3>Descuento por historial (PDO)</h3>";
    try {
        $stmt = $pdo->prepare("CALL sp_aplicar_descuento_cliente(:cliente, @pct)");
        $stmt->bindParam(':cliente', $cliente_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $pdo->query("SELECT @pct AS porcentaje")->fetch(PDO::FETCH_ASSOC);
        echo "Cliente: $cliente_id - % aplicado: " . $row['porcentaje'] . "%<br>";
    } catch (PDOException $e) {
        echo "Error en descuento (PDO): " . $e->getMessage() . "<br>";
    }
}

// 3) Reporte de bajo stock
function mostrarBajoStockPDO($pdo, $minimo) {
    echo "<h3>Productos con bajo stock (PDO)</h3>";
    try {
        $stmt = $pdo->prepare("CALL sp_reporte_bajo_stock(:minimo)");
        $stmt->bindParam(':minimo', $minimo, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            echo "<table border='1'>
                    <tr>
                        <th>ID</th><th>Producto</th><th>Stock</th>
                        <th>Precio</th><th>Vendidos</th><th>Sugerido reponer</th>
                    </tr>";
            foreach ($rows as $r) {
                echo "<tr>";
                echo "<td>".$r['id']."</td>";
                echo "<td>".$r['nombre']."</td>";
                echo "<td>".$r['stock']."</td>";
                echo "<td>".$r['precio']."</td>";
                echo "<td>".$r['cantidad_vendida']."</td>";
                echo "<td>".$r['sugerencia_reposicion']."</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No hay productos con bajo stock.<br>";
        }
    } catch (PDOException $e) {
        echo "Error en reporte bajo stock (PDO): " . $e->getMessage() . "<br>";
    }
}

// 4) Comisiones por ventas
function mostrarComisionesPDO($pdo, $fi, $ff, $porcentaje) {
    echo "<h3>Comisiones (PDO)</h3>";
    try {
        $stmt = $pdo->prepare("CALL sp_comisiones_ventas(:fi, :ff, :porc)");
        $stmt->bindParam(':fi', $fi);
        $stmt->bindParam(':ff', $ff);
        $stmt->bindParam(':porc', $porcentaje);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            echo "<table border='1'>
                    <tr>
                        <th>Venta</th><th>Fecha</th><th>Total</th>
                        <th>Productos</th><th>Comisión</th>
                    </tr>";
            foreach ($rows as $r) {
                echo "<tr>";
                echo "<td>".$r['venta_id']."</td>";
                echo "<td>".$r['fecha_venta']."</td>";
                echo "<td>".$r['total']."</td>";
                echo "<td>".$r['productos_vendidos']."</td>";
                echo "<td>".$r['comision']."</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No hay ventas en ese rango.<br>";
        }
    } catch (PDOException $e) {
        echo "Error en comisiones (PDO): " . $e->getMessage() . "<br>";
    }
}


//procesarDevolucionPDO($pdo, 1, 1, 1);
aplicarDescuentoClientePDO($pdo, 2);
mostrarBajoStockPDO($pdo, 5);
mostrarComisionesPDO($pdo, '2020-01-01', '2100-01-01', 5.0);

$pdo = null;
?>
