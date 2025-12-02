<?php
require_once "config_mysqli.php";

// 1) Productos que nunca se han vendido
$sql1 = "
    SELECT p.id, p.nombre, p.precio, p.stock
    FROM productos p
    WHERE p.id NOT IN (
        SELECT DISTINCT producto_id
        FROM detalles_venta
    )
";

$result1 = mysqli_query($conn, $sql1);

echo "<h3>1) Productos que nunca se han vendido</h3>";

if ($result1 && mysqli_num_rows($result1) > 0) {
    while ($row = mysqli_fetch_assoc($result1)) {
        echo "ID: " . $row['id'] .
             " - Producto: " . $row['nombre'] .
             " - Precio: " . $row['precio'] .
             " - Stock: " . $row['stock'] . "<br>";
    }
} else {
    echo "No hay productos sin ventas.<br>";
}

mysqli_free_result($result1);

// 2) Categorías con número de productos y valor total del inventario
$sql2 = "
    SELECT 
        c.id,
        c.nombre AS categoria,
        COUNT(p.id) AS total_productos,
        IFNULL(SUM(p.precio * p.stock), 0) AS valor_inventario
    FROM categorias c
    LEFT JOIN productos p ON p.categoria_id = c.id
    GROUP BY c.id, c.nombre
";

$result2 = mysqli_query($conn, $sql2);

echo "<h3>2) Categorías con número de productos y valor total del inventario</h3>";

if ($result2 && mysqli_num_rows($result2) > 0) {
    while ($row = mysqli_fetch_assoc($result2)) {
        echo "Categoría: " . $row['categoria'] .
             " - Productos: " . $row['total_productos'] .
             " - Valor inventario: " . $row['valor_inventario'] . "<br>";
    }
} else {
    echo "No se encontraron categorías.<br>";
}

mysqli_free_result($result2);

// 3) Clientes que han comprado todos los productos de una categoría específica
$categoriaId = 2; // puedes cambiar el id de categoría aquí

$sql3 = "
    SELECT c.id, c.nombre, c.email
    FROM clientes c
    JOIN ventas v ON v.cliente_id = c.id
    JOIN detalles_venta dv ON dv.venta_id = v.id
    JOIN productos p ON p.id = dv.producto_id
    WHERE p.categoria_id = $categoriaId
    GROUP BY c.id, c.nombre, c.email
    HAVING COUNT(DISTINCT p.id) = (
        SELECT COUNT(*)
        FROM productos
        WHERE categoria_id = $categoriaId
    )
";

$result3 = mysqli_query($conn, $sql3);

echo "<h3>3) Clientes que han comprado todos los productos de la categoría ID = $categoriaId</h3>";

if ($result3 && mysqli_num_rows($result3) > 0) {
    while ($row = mysqli_fetch_assoc($result3)) {
        echo "Cliente: " . $row['nombre'] .
             " - Email: " . $row['email'] . "<br>";
    }
} else {
    echo "Ningún cliente ha comprado todos los productos de esa categoría.<br>";
}

mysqli_free_result($result3);

// 4) Porcentaje de ventas de cada producto respecto al total de ventas
$sql4 = "
    SELECT 
        p.id,
        p.nombre,
        IFNULL(SUM(dv.subtotal), 0) AS total_producto,
        ROUND(
            IFNULL(SUM(dv.subtotal), 0) * 100 /
            (SELECT IFNULL(SUM(subtotal), 1) FROM detalles_venta),
        2) AS porcentaje
    FROM productos p
    LEFT JOIN detalles_venta dv ON dv.producto_id = p.id
    GROUP BY p.id, p.nombre
";

$result4 = mysqli_query($conn, $sql4);

echo "<h3>4) Porcentaje de ventas de cada producto respecto al total</h3>";

if ($result4 && mysqli_num_rows($result4) > 0) {
    while ($row = mysqli_fetch_assoc($result4)) {
        echo "Producto: " . $row['nombre'] .
             " - Total vendido: " . $row['total_producto'] .
             " - % del total: " . $row['porcentaje'] . "%<br>";
    }
} else {
    echo "No hay información de ventas.<br>";
}

mysqli_free_result($result4);

mysqli_close($conn);
?>
