<?php
// TALLERES/TALLER_4/Empresa.php

require_once __DIR__ . '/Empleado.php';
require_once __DIR__ . '/Gerente.php';
require_once __DIR__ . '/Desarrollador.php';
require_once __DIR__ . '/Evaluable.php';

class Empresa {
    /** @var Empleado[] */
    private array $empleados = [];

    public function agregarEmpleado(Empleado $empleado): void {
        $this->empleados[] = $empleado;
    }

    /** @return Empleado[] */
    public function listarEmpleados(): array {
        return $this->empleados;
    }

    public function calcularNominaTotal(): float {
        $total = 0.0;
        foreach ($this->empleados as $e) {
            // polimorfismo: cada subclase puede cambiar su salario actual
            $total += $e->getSalarioActual();
        }
        return $total;
    }

    /** @return array<string,string> idEmpleado => resultado */
    public function evaluarTodos(): array {
        $resultados = [];
        foreach ($this->empleados as $e) {
            if ($e instanceof Evaluable) {
                $resultados[$e->getIdEmpleado()] = $e->evaluarDesempenio();
            } else {
                // Manejo de error solicitado en el enunciado
                $resultados[$e->getIdEmpleado()] = "No evaluable (no implementa Evaluable)";
            }
        }
        return $resultados;
    }
}
