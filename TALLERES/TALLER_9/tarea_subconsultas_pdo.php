<?php
require_once "config_pdo.php";

try {
    // 1) Productos que nunca se han vendido
    $sql1 = "
        SELECT p.id, p.nombre, p.precio, p.stock
        FROM productos p
        WHERE p.id NOT IN (
            SELECT DISTINCT producto_id
            FROM detalles_venta
        )
    ";

    $stmt1 = $pdo->query($sql1);

    echo "<h3>1) Productos que nunca se han vendido</h3>";

    $rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    if ($rows1) {
        foreach ($rows1 as $row) {
            echo "ID: " . $row['id'] .
                 " - Producto: " . $row['nombre'] .
                 " - Precio: " . $row['precio'] .
                 " - Stock: " . $row['stock'] . "<br>";
        }
    } else {
        echo "No hay productos sin ventas.<br>";
    }

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

    $stmt2 = $pdo->query($sql2);

    echo "<h3>2) Categorías con número de productos y valor total del inventario</h3>";

    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    if ($rows2) {
        foreach ($rows2 as $row) {
            echo "Categoría: " . $row['categoria'] .
                 " - Productos: " . $row['total_productos'] .
                 " - Valor inventario: " . $row['valor_inventario'] . "<br>";
        }
    } else {
        echo "No se encontraron categorías.<br>";
    }

    // 3) Clientes que han comprado todos los productos de una categoría específica
    $categoriaId = 2; // cambiar si quieres otra categoría

    $sql3 = "
        SELECT c.id, c.nombre, c.email
        FROM clientes c
        JOIN ventas v ON v.cliente_id = c.id
        JOIN detalles_venta dv ON dv.venta_id = v.id
        JOIN productos p ON p.id = dv.producto_id
        WHERE p.categoria_id = :categoria_id
        GROUP BY c.id, c.nombre, c.email
        HAVING COUNT(DISTINCT p.id) = (
            SELECT COUNT(*)
            FROM productos
            WHERE categoria_id = :categoria_id
        )
    ";

    $stmt3 = $pdo->prepare($sql3);
    $stmt3->execute([':categoria_id' => $categoriaId]);

    echo "<h3>3) Clientes que han comprado todos los productos de la categoría ID = $categoriaId</h3>";

    $rows3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    if ($rows3) {
        foreach ($rows3 as $row) {
            echo "Cliente: " . $row['nombre'] .
                 " - Email: " . $row['email'] . "<br>";
        }
    } else {
        echo "Ningún cliente ha comprado todos los productos de esa categoría.<br>";
    }

    // 4) Porcentaje de ventas de cada producto respecto al total
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

    $stmt4 = $pdo->query($sql4);

    echo "<h3>4) Porcentaje de ventas de cada producto respecto al total</h3>";

    $rows4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    if ($rows4) {
        foreach ($rows4 as $row) {
            echo "Producto: " . $row['nombre'] .
                 " - Total vendido: " . $row['total_producto'] .
                 " - % del total: " . $row['porcentaje'] . "%<br>";
        }
    } else {
        echo "No hay información de ventas.<br>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$pdo = null;
?>
