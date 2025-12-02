<?php
require_once "config_pdo.php";

// 1) Productos con bajo stock
echo "<h3>1) Productos con bajo stock</h3>";
try {
    $stmt1 = $pdo->query("SELECT * FROM vista_productos_bajo_stock");
    $rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    if ($rows1) {
        echo "<table border='1'>";
        echo "<tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Stock</th>
                <th>Precio</th>
                <th>Cant. vendida</th>
                <th>Total vendido</th>
              </tr>";
        foreach ($rows1 as $row) {
            echo "<tr>";
            echo "<td>".$row['id']."</td>";
            echo "<td>".$row['nombre']."</td>";
            echo "<td>".$row['stock']."</td>";
            echo "<td>".$row['precio']."</td>";
            echo "<td>".$row['cantidad_vendida']."</td>";
            echo "<td>".$row['total_vendido']."</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay productos con bajo stock.<br>";
    }
} catch (PDOException $e) {
    echo "Error: ".$e->getMessage();
}

echo "<br>";

// 2) Historial de clientes
echo "<h3>2) Historial de clientes</h3>";
try {
    $stmt2 = $pdo->query("SELECT * FROM vista_historial_clientes");
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    if ($rows2) {
        echo "<table border='1'>";
        echo "<tr>
                <th>Cliente</th>
                <th>Email</th>
                <th>Venta</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Total venta</th>
              </tr>";
        foreach ($rows2 as $row) {
            echo "<tr>";
            echo "<td>".$row['cliente']."</td>";
            echo "<td>".$row['email']."</td>";
            echo "<td>".$row['venta_id']."</td>";
            echo "<td>".$row['fecha_venta']."</td>";
            echo "<td>".$row['estado']."</td>";
            echo "<td>".$row['producto']."</td>";
            echo "<td>".$row['cantidad']."</td>";
            echo "<td>".$row['subtotal']."</td>";
            echo "<td>".$row['total_venta']."</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay historial de clientes.<br>";
    }
} catch (PDOException $e) {
    echo "Error: ".$e->getMessage();
}

echo "<br>";

// 3) Métricas por categoría
echo "<h3>3) Métricas por categoría</h3>";
try {
    $stmt3 = $pdo->query("SELECT * FROM vista_metricas_categorias");
    $rows3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    if ($rows3) {
        echo "<table border='1'>";
        echo "<tr>
                <th>Categoría</th>
                <th>Cant. productos</th>
                <th>Ventas totales</th>
              </tr>";
        foreach ($rows3 as $row) {
            echo "<tr>";
            echo "<td>".$row['categoria']."</td>";
            echo "<td>".$row['cantidad_productos']."</td>";
            echo "<td>".$row['ventas_totales']."</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay métricas de categorías.<br>";
    }
} catch (PDOException $e) {
    echo "Error: ".$e->getMessage();
}

echo "<br>";

// 4) Ventas por mes
echo "<h3>4) Ventas por mes</h3>";
try {
    $stmt4 = $pdo->query("SELECT * FROM vista_ventas_por_mes");
    $rows4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    if ($rows4) {
        echo "<table border='1'>";
        echo "<tr>
                <th>Año-Mes</th>
                <th>Ventas mes</th>
                <th>Ventas mes anterior</th>
              </tr>";
        foreach ($rows4 as $row) {
            echo "<tr>";
            echo "<td>".$row['anio_mes']."</td>";
            echo "<td>".$row['ventas_mes']."</td>";
            echo "<td>".$row['ventas_mes_anterior']."</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay datos de ventas por mes.<br>";
    }
} catch (PDOException $e) {
    echo "Error: ".$e->getMessage();
}

$pdo = null;
?>
