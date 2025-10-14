<?php
include 'config_sesion.php';

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de producto no válido.");
}

$id = (int) $_GET['id'];

// Lista de productos disponibles
$productos = [
    1 => ['nombre' => 'Camiseta', 'precio' => 15.00],
    2 => ['nombre' => 'Pantalón', 'precio' => 25.00],
    3 => ['nombre' => 'Zapatos', 'precio' => 40.00],
    4 => ['nombre' => 'Gorra', 'precio' => 10.00],
    5 => ['nombre' => 'Mochila', 'precio' => 30.00],
];

// Si el producto existe
if (isset($productos[$id])) {
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    if (!isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id] = ['cantidad' => 1, 'producto' => $productos[$id]];
    } else {
        $_SESSION['carrito'][$id]['cantidad']++;
    }

    header("Location: ver_carrito.php");
    exit();
} else {
    die("Producto no encontrado.");
}
?>
