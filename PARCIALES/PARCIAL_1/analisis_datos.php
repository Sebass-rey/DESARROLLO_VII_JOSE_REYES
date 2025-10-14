<?php
// analisis_datos.php
include 'estadisticas.php';

$datos = [5, 8, 10, 10, 12, 13, 15, 15, 15, 18, 20, 22, 25, 25, 30, 35, 40, 40, 42, 50];

$media = calcular_media($datos);
$mediana = calcular_mediana($datos);
$moda = encontrar_moda($datos);
?>
<!DOCTYPE html>
<html lang="es">
<body>
    <h2>Resultados del Análisis de Datos</h2>
    <table>
        <tr><th>Medida</th><th>Resultado</th></tr>
        <tr><td>Media</td><td><?php echo number_format($media, 2); ?></td></tr>
        <tr><td>Mediana</td><td><?php echo $mediana; ?></td></tr>
        <tr><td>Moda</td><td><?php echo $moda; ?></td></tr>
    </table>
</body>
</html>
