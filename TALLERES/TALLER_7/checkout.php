<?php
include 'config_sesion.php';

$carrito = $_SESSION['carrito'] ?? [];
$total = 0;

if (empty($carrito)) {
    echo "<p>Tu carrito está vacío. <a href='productos.php'>Volver</a></p>";
    exit();
}

// Calcular total
foreach ($carrito as $item) {
    $total += $item['producto']['precio'] * $item['cantidad'];
}

// Crear cookie del usuario (simulación)
$nombreUsuario = "Cliente_" . rand(100, 999);
setcookie("usuario", $nombreUsuario, time() + 86400, "/", "", false, true);

// Vaciar carrito
unset($_SESSION['carrito']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
</head>
<body>
    <h2>Compra Finalizada</h2>
    <p>Gracias por tu compra, <?php echo htmlspecialchars($nombreUsuario); ?>.</p>
    <p>Total pagado: <strong>B/.<?php echo number_format($total, 2); ?></strong></p>
    <p>Tu cookie de cliente fue guardada por 24 horas.</p>
    <a href="productos.php">Volver a la tienda</a>
</body>
</html>
