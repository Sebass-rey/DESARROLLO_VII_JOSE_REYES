<?php
include 'config_sesion.php';

$carrito = $_SESSION['carrito'] ?? [];
$total = 0.0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
</head>
<body>
    <h2>Tu Carrito</h2>
    <?php if (empty($carrito)): ?>
        <p>No hay productos en el carrito.</p>
    <?php else: ?>
        <table border="1" cellpadding="8">
            <tr>
                <th>Producto</th>
                <th>Precio Unitario</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Acción</th>
            </tr>
            <?php foreach ($carrito as $id => $item): 
                $subtotal = $item['producto']['precio'] * $item['cantidad'];
                $total += $subtotal;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($item['producto']['nombre']); ?></td>
                <td>B/.<?php echo number_format($item['producto']['precio'], 2); ?></td>
                <td><?php echo $item['cantidad']; ?></td>
                <td>B/.<?php echo number_format($subtotal, 2); ?></td>
                <td><a href="eliminar_del_carrito.php?id=<?php echo $id; ?>">❌ Eliminar</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <h3>Total: B/.<?php echo number_format($total, 2); ?></h3>
        <a href="checkout.php">Finalizar compra</a>
    <?php endif; ?>
    <p><a href="productos.php">Seguir comprando</a></p>
</body>
</html>
