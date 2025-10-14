<?php
// procesamiento_ventas.php

function calcular_total_ventas($datos_ventas) {
    $total = 0;
    foreach ($datos_ventas as $producto => $ventas) {
        $total += array_sum($ventas);
    }
    return $total;
}

function producto_mas_vendido($datos_ventas) {
    $totales = [];
    foreach ($datos_ventas as $producto => $ventas) {
        $totales[$producto] = array_sum($ventas);
    }
    arsort($totales);
    return array_key_first($totales);
}

function ventas_por_region($datos_ventas) {
    $regiones = [];
    foreach ($datos_ventas as $producto => $ventas) {
        foreach ($ventas as $region => $valor) {
            if (!isset($regiones[$region])) {
                $regiones[$region] = 0;
            }
            $regiones[$region] += $valor;
        }
    }
    arsort($regiones);
    return $regiones;
}
?>
