<?php
require_once 'Prestable.php';

class Libro implements Prestable {
    // ... (propiedades y métodos anteriores) ...

    private $disponible = true;

    public function prestar() {
        if ($this->disponible) {
            $this->disponible = false;
            return true;
        }
        return false;
    }

    public function devolver() {
        $this->disponible = true;
    }

    public function estaDisponible() {
        return $this->disponible;
    }
}

// Ejemplo de uso
$libro = new Libro("Rayuela", "Julio Cortázar", 1963);
echo $libro->obtenerInformacion() . "\n";
echo "Disponible: " . ($libro->estaDisponible() ? "Sí" : "No") . "\n";
$libro->prestar();
echo "Disponible después de prestar: " . ($libro->estaDisponible() ? "Sí" : "No") . "\n";
$libro->devolver();
echo "Disponible después de devolver: " . ($libro->estaDisponible() ? "Sí" : "No") . "\n";