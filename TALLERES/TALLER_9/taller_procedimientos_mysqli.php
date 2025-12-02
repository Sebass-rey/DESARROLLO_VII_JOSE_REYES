<?php
require_once "config_mysqli.php";

// 1) Devolución de producto
function procesarDevolucion($conn, $venta_id, $producto_id, $cantidad) {
    $sql = "CALL sp_procesar_devolucion(?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $venta_id, $producto_id, $cantidad);

    try {
        mysqli_stmt_execute($stmt);
        echo "<h3>Devolución (MySQLi)</h3>";
        echo "Venta: $venta_id - Producto: $producto_id - Cantidad devuelta: $cantidad<br>";
    } catch (mysqli_sql_exception $e) {
        echo "<h3>Devolución (MySQLi)</h3>";
        echo "Error en devolución: " . $e->getMessage() . "<br>";
    }

    mysqli_stmt_close($stmt);
    mysqli_next_result($conn);
}

// 2) Descuento según historial
function aplicarDescuentoCliente($conn, $cliente_id) {
    $sql = "CALL sp_aplicar_descuento_cliente(?, @pct)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $cliente_id);

    if (mysqli_stmt_execute($stmt)) {
        $res = mysqli_query($conn, "SELECT @pct AS porcentaje");
        $row = mysqli_fetch_assoc($res);
        echo "<h3>Descuento aplicado</h3>";
        echo "Cliente $cliente_id - Porcentaje usado: " . $row['porcentaje'] . "%<br>";
        mysqli_free_result($res);
    } else {
        echo "Error al aplicar descuento: " . mysqli_error($conn) . "<br>";
    }

    mysqli_stmt_close($stmt);
    mysqli_next_result($conn);
}

// 3) Reporte de bajo stock
function mostrarBajoStock($conn, $minimo) {
    $sql = "CALL sp_reporte_bajo_stock(?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $minimo);

    echo "<h3>Productos con stock menor a $minimo</h3>";

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>ID</th><th>Producto</th><th>Stock</th>
                        <th>Precio</th><th>Vendidos</th><th>Sugerido reponer</th>
                    </tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".$row['nombre']."</td>";
                echo "<td>".$row['stock']."</td>";
                echo "<td>".$row['precio']."</td>";
                echo "<td>".$row['cantidad_vendida']."</td>";
                echo "<td>".$row['sugerencia_reposicion']."</td>";
                echo "</tr>";
            }
            echo "</table>";
            mysqli_free_result($result);
        } else {
            echo "No hay productos con bajo stock.<br>";
        }
    } else {
        echo "Error en reporte de bajo stock: " . mysqli_error($conn) . "<br>";
    }

    mysqli_stmt_close($stmt);
    mysqli_next_result($conn);
}

// 4) Comisiones de ventas
function mostrarComisiones($conn, $fi, $ff, $porcentaje) {
    $sql = "CALL sp_comisiones_ventas(?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssd", $fi, $ff, $porcentaje);

    echo "<h3>Comisiones entre $fi y $ff (".$porcentaje."%)</h3>";

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>Venta</th><th>Fecha</th><th>Total</th>
                        <th>Productos</th><th>Comisión</th>
                    </tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>".$row['venta_id']."</td>";
                echo "<td>".$row['fecha_venta']."</td>";
                echo "<td>".$row['total']."</td>";
                echo "<td>".$row['productos_vendidos']."</td>";
                echo "<td>".$row['comision']."</td>";
                echo "</tr>";
            }
            echo "</table>";
            mysqli_free_result($result);
        } else {
            echo "No hay ventas en ese rango.<br>";
        }
    } else {
        echo "Error al calcular comisiones: " . mysqli_error($conn) . "<br>";
    }

    mysqli_stmt_close($stmt);
    mysqli_next_result($conn);
}


//procesarDevolucion($conn, 1, 1, 1);              
aplicarDescuentoCliente($conn, 2);               
mostrarBajoStock($conn, 5);                      
mostrarComisiones($conn, '2020-01-01', '2100-01-01', 5.0);

mysqli_close($conn);
?>
