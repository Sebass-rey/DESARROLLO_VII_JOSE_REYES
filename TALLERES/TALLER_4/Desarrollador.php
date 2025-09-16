<?php
// TALLERES/TALLER_4/Desarrollador.php
require_once __DIR__ . '/Empleado.php';
require_once __DIR__ . '/Evaluable.php';

class Desarrollador extends Empleado implements Evaluable {
    private string $lenguajePrincipal;
    private string $nivelExperiencia; // p.ej. "Junior", "SemiSenior", "Senior"

    public function __construct(
        string $nombre,
        string $idEmpleado,
        float $salarioBase,
        string $lenguajePrincipal,
        string $nivelExperiencia
    ) {
        parent::__construct($nombre, $idEmpleado, $salarioBase);
        $this->lenguajePrincipal = $lenguajePrincipal;
        $this->nivelExperiencia = $nivelExperiencia;
    }

    public function getLenguajePrincipal(): string { return $this->lenguajePrincipal; }
    public function setLenguajePrincipal(string $lang): void { $this->lenguajePrincipal = $lang; }

    public function getNivelExperiencia(): string { return $this->nivelExperiencia; }
    public function setNivelExperiencia(string $nivel): void { $this->nivelExperiencia = $nivel; }

    // Lógica de evaluación distinta a Gerente
    public function evaluarDesempenio(): string {
        // Ejemplo simple: nivel define el resultado
        switch (strtolower($this->nivelExperiencia)) {
            case 'senior': return "Excelente (alta calidad de código y mentoring)";
            case 'semisenior': return "Bueno (cumple plazos, necesita refinar revisiones)";
            default: return "En crecimiento (necesita soporte y buenas prácticas)";
        }
    }
}
