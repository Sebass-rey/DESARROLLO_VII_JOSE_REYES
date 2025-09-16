<?php
// TALLERES/TALLER_4/Gerente.php
require_once __DIR__ . '/Empleado.php';
require_once __DIR__ . '/Evaluable.php';

class Gerente extends Empleado implements Evaluable {
    private string $departamento;
    private float $bono = 0.0;

    public function __construct(string $nombre, string $idEmpleado, float $salarioBase, string $departamento) {
        parent::__construct($nombre, $idEmpleado, $salarioBase);
        $this->departamento = $departamento;
    }

    public function getDepartamento(): string { return $this->departamento; }
    public function setDepartamento(string $dep): void { $this->departamento = $dep; }

    // Método único: asignar bono
    public function asignarBono(float $monto): void {
        if ($monto < 0) { throw new InvalidArgumentException("El bono no puede ser negativo."); }
        $this->bono = $monto;
    }

    public function getBono(): float { return $this->bono; }

    // Salario actual incluye bono
    public function getSalarioActual(): float {
        return $this->getSalarioBase() + $this->bono;
    }

    // Lógica de evaluación propia
    public function evaluarDesempenio(): string {
        // Ejemplo simple: si el bono es alto, desempeño "Excelente"
        if ($this->bono >= 0.2 * $this->getSalarioBase()) {
            return "Excelente (liderazgo y metas alcanzadas)";
        } elseif ($this->bono > 0) {
            return "Bueno (objetivos cumplidos con áreas a mejorar)";
        }
        return "Pendiente de mejora (sin bono asignado)";
    }
}
