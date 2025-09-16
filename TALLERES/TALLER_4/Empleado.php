<?php
// TALLERES/TALLER_4/Empleado.php
// Clase base con encapsulación (getters/setters)

class Empleado {
    private string $nombre;
    private string $idEmpleado;
    private float $salarioBase;

    public function __construct(string $nombre, string $idEmpleado, float $salarioBase) {
        $this->setNombre($nombre);
        $this->setIdEmpleado($idEmpleado);
        $this->setSalarioBase($salarioBase);
    }

    // Getters
    public function getNombre(): string { return $this->nombre; }
    public function getIdEmpleado(): string { return $this->idEmpleado; }
    public function getSalarioBase(): float { return $this->salarioBase; }

    // Setters
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setIdEmpleado(string $idEmpleado): void { $this->idEmpleado = $idEmpleado; }
    public function setSalarioBase(float $salarioBase): void {
        if ($salarioBase < 0) { throw new InvalidArgumentException("El salario no puede ser negativo."); }
        $this->salarioBase = $salarioBase;
    }

    // Polimorfismo: las hijas pueden redefinir este cálculo
    public function getSalarioActual(): float {
        return $this->salarioBase;
    }
}
