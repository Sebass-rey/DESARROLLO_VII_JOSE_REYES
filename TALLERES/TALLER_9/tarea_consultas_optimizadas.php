<?php
require_once "config_pdo.php";

// 1) Productos filtrados por categoría + rango de precio 
function productosPorCategoriaYPrecio(PDO $pdo, int $categoria_id, float $min, float $max)
{
    $sql = "
        SELECT p.id, p.nombre, p.precio, p.stock
        FROM productos p
        WHERE p.categoria_id = :cat
          AND p.precio BETWEEN :min AND :max
          AND p.stock > 0
        ORDER BY p.precio ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':cat' => $categoria_id,
        ':min' => $min,
        ':max' => $max
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 2) Ventas de un cliente en un período 
function ventasPorClientePeriodo(PDO $pdo, int $cliente_id, string $fi, string $ff)
{
    $sql = "
        SELECT v.id, v.fecha_venta, v.total, v.estado
        FROM ventas v
        WHERE v.cliente_id = :cliente
          AND v.fecha_venta BETWEEN :fi AND :ff
        ORDER BY v.fecha_venta DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':cliente' => $cliente_id,
        ':fi' => $fi,
        ':ff' => $ff
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 3) Reporte simple de ventas por categoría 
function reporteVentasPorCategoria(PDO $pdo, string $fi, string $ff)
{
    $sql = "
        SELECT 
            c.nombre AS categoria,
            COUNT(DISTINCT v.id) AS total_ventas,
            SUM(dv.cantidad) AS productos_vendidos,
            SUM(dv.subtotal) AS total_ingresos
        FROM categorias c
        JOIN productos p ON p.categoria_id = c.id
        JOIN detalles_venta dv ON dv.producto_id = p.id
        JOIN ventas v ON v.id = dv.venta_id
        WHERE v.fecha_venta BETWEEN :fi AND :ff
        GROUP BY c.id, c.nombre
        ORDER BY total_ingresos DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fi' => $fi,
        ':ff' => $ff
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



// 1) Productos
$productos = productosPorCategoriaYPrecio($pdo, 1, 50, 1500);
echo "<h3>1) Productos por categoría y precio</h3>";
echo "<pre>";
print_r($productos);
echo "</pre>";

// 2) Ventas cliente
$ventasCliente = ventasPorClientePeriodo($pdo, 1, '2020-01-01', '2030-12-31');
echo "<h3>2) Ventas por cliente y período</h3>";
echo "<pre>";
print_r($ventasCliente);
echo "</pre>";

// 3) Reporte por categoría
$reporte = reporteVentasPorCategoria($pdo, '2020-01-01', '2030-12-31');
echo "<h3>3) Reporte de ventas por categoría</h3>";
echo "<pre>";
print_r($reporte);
echo "</pre>";

$pdo = null;
?>
