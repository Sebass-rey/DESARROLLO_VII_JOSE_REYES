<?php
include 'config_sesion.php';

// Lista simple de productos
$productos = [
    1 => ['nombre' => 'Camiseta', 'precio' => 15.00],
    2 => ['nombre' => 'Pantalón', 'precio' => 25.00],
    3 => ['nombre' => 'Zapatos', 'precio' => 40.00],
    4 => ['nombre' => 'Gorra', 'precio' => 10.00],
    5 => ['nombre' => 'Mochila', 'precio' => 30.00],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos</title>
</head>
<body>
    <h2>Lista de Productos</h2>
    <ul>
        <?php foreach ($productos as $id => $p): ?>
            <li>
                <?php echo htmlspecialchars($p['nombre']); ?> -
                B/.<?php echo number_format($p['precio'], 2); ?>
                <a href="agregar_al_carrito.php?id=<?php echo $id; ?>">🛒 Añadir al carrito</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <p><a href="ver_carrito.php">Ver carrito</a></p>
</body>
</html>
