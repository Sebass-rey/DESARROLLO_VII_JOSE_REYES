
<?php
// Ejemplo básico de trim()
$textoConEspacios = "   Hola, mundo!   ";
$textoLimpio = trim($textoConEspacios);
echo "Texto original: '$textoConEspacios'</br>";
echo "Texto limpio: '$textoLimpio'</br>";

// Ejemplo con caracteres específicos
$textoConCaracteres = "...Hola, mundo!...";
$textoSinPuntos = trim($textoConCaracteres, ".");
echo "</br>Texto con puntos: '$textoConCaracteres'</br>";
echo "Texto sin puntos: '$textoSinPuntos'</br>";

// Ejercicio: Limpia el siguiente texto eliminando espacios y guiones bajos al inicio y al final
$textoParaLimpiar = "___   Mi nombre es Juan   ___";
$textoLimpiado = trim($textoParaLimpiar, " _");
echo "</br>Texto original para limpiar: '$textoParaLimpiar'</br>";
echo "Texto limpiado: '$textoLimpiado'</br>";

// Bonus: Uso de ltrim() y rtrim()
$textoIzquierda = "   Izquierda  ";
$textoDerecha = "  Derecha   ";
echo "</br>Trim izquierdo: '" . ltrim($textoIzquierda) . "'</br>";
echo "Trim derecho: '" . rtrim($textoDerecha) . "'</br>";

// Extra: Limpieza de un array de strings
$arrayConEspacios = [
    "   Primer elemento   ",
    "  Segundo elemento  ",
    " Tercer elemento "
];
$arrayLimpio = array_map('trim', $arrayConEspacios);
echo "</br>Array original:</br>";
print_r($arrayConEspacios);
echo "Array limpio:</br>";
print_r($arrayLimpio);

// Desafío: Crea una función que limpie una cadena de caracteres no deseados al inicio y al final
function limpiarCadena($cadena, $caracteresNoDeseados = " 	</br>
") {
    return trim($cadena, $caracteresNoDeseados);
}

$cadenaSucia = "	</br>Hola, esto es una prueba!@#@";
$cadenaLimpia = limpiarCadena($cadenaSucia, " 	</br>
@#");
echo "</br>Cadena sucia: '$cadenaSucia'</br>";
echo "Cadena limpia: '$cadenaLimpia'</br>";
?>
      
