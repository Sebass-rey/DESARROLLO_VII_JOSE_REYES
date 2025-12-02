<?php
require_once "config_mysqli.php";

echo "<h2>1) Productos con bajo stock</h2>";
$sql1 = "SELECT * FROM vista_productos_bajo_stock";
$res1 = mysqli_query($conn, $sql1);

if ($res1 && mysqli_num_rows($res1) > 0) {
    echo "<table border='1'><tr>
            <th>ID</th><th>Producto</th><th>Stock</th>
            <th>Precio</th><th>Cant. Vendida</th><th>Total Vendido</th>
          </tr>";

    while ($r = mysqli_fetch_assoc($res1)) {
        echo "<tr>
                <td>{$r['id']}</td>
                <td>{$r['nombre']}</td>
                <td>{$r['stock']}</td>
                <td>{$r['precio']}</td>
                <td>{$r['cantidad_vendida']}</td>
                <td>{$r['total_vendido']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No hay productos con bajo stock.<br>";
}

echo "<hr>";


echo "<h2>2) Historial de clientes</h2>";
$sql2 = "SELECT * FROM vista_historial_clientes";
$res2 = mysqli_query($conn, $sql2);

if ($res2 && mysqli_num_rows($res2) > 0) {
    echo "<table border='1'><tr>
            <th>Cliente</th><th>Email</th><th>Venta</th>
            <th>Fecha</th><th>Estado</th><th>Producto</th>
            <th>Cant</th><th>Subtotal</th><th>Total Venta</th>
          </tr>";

    while ($r = mysqli_fetch_assoc($res2)) {
        echo "<tr>
                <td>{$r['cliente']}</td>
                <td>{$r['email']}</td>
                <td>{$r['venta_id']}</td>
                <td>{$r['fecha_venta']}</td>
                <td>{$r['estado']}</td>
                <td>{$r['producto']}</td>
                <td>{$r['cantidad']}</td>
                <td>{$r['subtotal']}</td>
                <td>{$r['total_venta']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No hay historial disponible.<br>";
}

echo "<hr>";


echo "<h2>3) Métricas por categoría</h2>";
$sql3 = "SELECT * FROM vista_metricas_categorias";
$res3 = mysqli_query($conn, $sql3);

if ($res3 && mysqli_num_rows($res3) > 0) {
    echo "<table border='1'><tr>
            <th>Categoría</th><th>Productos</th><th>Ventas Totales</th>
          </tr>";

    while ($r = mysqli_fetch_assoc($res3)) {
        echo "<tr>
                <td>{$r['categoria']}</td>
                <td>{$r['cantidad_productos']}</td>
                <td>{$r['ventas_totales']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No hay métricas disponibles.<br>";
}

echo "<hr>";


echo "<h2>4) Ventas por mes</h2>";
$sql4 = "SELECT * FROM vista_ventas_por_mes";
$res4 = mysqli_query($conn, $sql4);

if ($res4 && mysqli_num_rows($res4) > 0) {
    echo "<table border='1'><tr>
            <th>Mes</th><th>Ventas</th><th>Mes Anterior</th>
          </tr>";

    while ($r = mysqli_fetch_assoc($res4)) {
        echo "<tr>
                <td>{$r['anio_mes']}</td>
                <td>{$r['ventas_mes']}</td>
                <td>{$r['ventas_mes_anterior']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No hay datos de ventas por mes.<br>";
}

mysqli_close($conn);
?>
