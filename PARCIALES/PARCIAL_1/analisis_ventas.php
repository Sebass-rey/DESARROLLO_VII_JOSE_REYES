<?php
// analisis_ventas.php
include 'procesamiento_ventas.php';

$ventas = [
    "Laptop" => ["Norte" => 1200, "Sur" => 900, "Este" => 800, "Oeste" => 1100],
    "Tablet" => ["Norte" => 700, "Sur" => 850, "Este" => 600, "Oeste" => 950],
    "Teléfono" => ["Norte" => 1500, "Sur" => 1400, "Este" => 1300, "Oeste" => 1200],
    "Monitor" => ["Norte" => 400, "Sur" => 500, "Este" => 450, "Oeste" => 550],
    "Teclado" => ["Norte" => 200, "Sur" => 250, "Este" => 300, "Oeste" => 280]
];

$total_general = calcular_total_ventas($ventas);
$producto_top = producto_mas_vendido($ventas);
$por_region = ventas_por_region($ventas);
?>

<!DOCTYPE html>
<html lang="es">
<body>
    <h2>Resumen de Ventas</h2>

    <table>
        <tr><th>Producto</th><th>Total de Ventas</th></tr>
        <?php foreach ($ventas as $producto => $valores): ?>
            <tr>
                <td><?= $producto ?></td>
                <td><?= array_sum($valores) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p><strong>Total General:</strong> <?= $total_general ?></p>
    <p><strong>Producto más vendido:</strong> <?= $producto_top ?></p>

    <h3>Ventas por Región (descendente)</h3>
    <table>
        <tr><th>Región</th><th>Total Ventas</th></tr>
        <?php foreach ($por_region as $region => $valor): ?>
            <tr>
                <td><?= $region ?></td>
                <td><?= $valor ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
