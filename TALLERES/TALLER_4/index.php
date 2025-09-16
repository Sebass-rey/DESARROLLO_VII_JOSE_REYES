<?php
// TALLERES/TALLER_4/index.php
// Demostración: agregar empleados, listar, nómina total y evaluar desempeño

require_once __DIR__ . '/Empresa.php'; // este ya incluye al resto

// Crear empresa
$miEmpresa = new Empresa();

// Crear empleados de distintos tipos
$g1 = new Gerente("Ana Torres", "G-001", 2000.00, "Operaciones");
$g1->asignarBono(600.00); // 30% del salario base -> Excelente

$d1 = new Desarrollador("Carlos Pérez", "D-101", 1500.00, "PHP", "SemiSenior");
$d2 = new Desarrollador("María Gómez", "D-102", 1200.00, "JavaScript", "Junior");

// (Opcional) Empleado base no evaluable para mostrar manejo de error
$eBase = new Empleado("Invitado", "E-000", 800.00);

// Agregar a la empresa
$miEmpresa->agregarEmpleado($g1);
$miEmpresa->agregarEmpleado($d1);
$miEmpresa->agregarEmpleado($d2);
$miEmpresa->agregarEmpleado($eBase);

// Listado
echo "<h2>Listado de Empleados</h2>";
echo "<ul>";
foreach ($miEmpresa->listarEmpleados() as $e) {
    $extra = "";
    if ($e instanceof Gerente) {
        $extra = " | Departamento: " . $e->getDepartamento() . " | Bono: B/. " . number_format($e->getBono(), 2);
    } elseif ($e instanceof Desarrollador) {
        $extra = " | Lenguaje: " . $e->getLenguajePrincipal() . " | Nivel: " . $e->getNivelExperiencia();
    }
    echo "<li><strong>{$e->getNombre()}</strong> (ID: {$e->getIdEmpleado()}) | Salario actual: B/. " . number_format($e->getSalarioActual(), 2) . $extra . "</li>";
}
echo "</ul>";

// Nómina total
echo "<h3>Nómina total: B/. " . number_format($miEmpresa->calcularNominaTotal(), 2) . "</h3>";

// Evaluaciones de desempeño
echo "<h2>Evaluaciones de Desempeño</h2>";
echo "<ul>";
foreach ($miEmpresa->evaluarTodos() as $id => $resultado) {
    echo "<li><strong>{$id}</strong>: {$resultado}</li>";
}
echo "</ul>";

