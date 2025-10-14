<?php
// estadisticas.php

function calcular_media($datos) {
    $total = array_sum($datos);
    $cantidad = count($datos);
    return $cantidad > 0 ? $total / $cantidad : 0;
}

function calcular_mediana($datos) {
    sort($datos);
    $cantidad = count($datos);
    $mitad = floor($cantidad / 2);

    if ($cantidad % 2 == 0) {
        return ($datos[$mitad - 1] + $datos[$mitad]) / 2;
    } else {
        return $datos[$mitad];
    }
}

function encontrar_moda($datos) {
    $frecuencias = array_count_values($datos);
    $max_frecuencia = max($frecuencias);
    $modas = array_keys($frecuencias, $max_frecuencia);

    return implode(", ", $modas);
}
?>
