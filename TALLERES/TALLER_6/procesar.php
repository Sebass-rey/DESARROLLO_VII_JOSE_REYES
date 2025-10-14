<?php
require_once 'validaciones.php';
require_once 'sanitizacion.php';

$errores = [];
$datos = [];

// Sanitizar
$datos['nombre'] = sanitizarTexto($_POST['nombre'] ?? '');
$datos['email'] = sanitizarEmail($_POST['email'] ?? '');
$datos['sitio_web'] = sanitizarUrl($_POST['sitio_web'] ?? '');
$datos['genero'] = sanitizarTexto($_POST['genero'] ?? '');
$datos['intereses'] = $_POST['intereses'] ?? [];
$datos['comentarios'] = sanitizarTexto($_POST['comentarios'] ?? '');
$datos['fecha_nacimiento'] = $_POST['fecha_nacimiento'] ?? '';

// Calcular edad
if (!empty($datos['fecha_nacimiento'])) {
    $fecha_nac = new DateTime($datos['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nac)->y;
    $datos['edad'] = $edad;
} else {
    $errores[] = "La fecha de nacimiento es obligatoria.";
}

// Validar datos
if (!validarNombre($datos['nombre'])) $errores[] = "Nombre inválido.";
if (!validarEmail($datos['email'])) $errores[] = "Email inválido.";

// Subir imagen
if (!empty($_FILES['foto_perfil']['name'])) {
    $nombre_original = basename($_FILES['foto_perfil']['name']);
    $ext = pathinfo($nombre_original, PATHINFO_EXTENSION);
    $nombre_unico = uniqid('foto_', true) . '.' . $ext;
    $ruta_destino = "uploads/" . $nombre_unico;

    if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta_destino)) {
        $datos['foto_perfil'] = $nombre_unico;
    } else {
        $errores[] = "Error al subir la foto.";
    }
}

// Si hay errores, mostrar formulario con datos previos
if (!empty($errores)) {
    foreach ($errores as $error) {
        echo "<p style='color:red;'>$error</p>";
    }
    include 'formulario.html';
    exit;
}

// Guardar registro
$archivo_json = 'registros.json';
$registros = [];

if (file_exists($archivo_json)) {
    $registros = json_decode(file_get_contents($archivo_json), true) ?? [];
}

$registros[] = $datos;

file_put_contents($archivo_json, json_encode($registros, JSON_PRETTY_PRINT));

echo "<h2>Registro exitoso</h2>";
echo "<a href='resumen.php'>Ver todos los registros</a>";
?>
