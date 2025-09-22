<?php
declare(strict_types=1);

/**
 * Proyecto Final – Sistema de Gestión de Estudiantes
 * Requisitos implementados:
 * - POO con clases Estudiante y SistemaGestionEstudiantes
 * - Arreglos asociativos/multidimensionales
 * - Búsqueda, filtrado, ranking, reportería
 * - array_map, array_filter, array_reduce
 * - Type hints + manejo de errores
 * - __toString() en Estudiante
 * - Flags automáticos (honor roll / en riesgo)
 * - Graduación de estudiantes y lista de graduados
 * - Persistencia opcional en JSON
 *
 * Ejecución (CLI):
 *   php proyecto_final.php
 */

final class Estudiante
{
    private int $id;
    private string $nombre;
    private int $edad;
    private string $carrera;
    /** @var array<string,float> materias => calificación */
    private array $materias = [];
    /** @var array<string,bool> */
    private array $flags = [
        'honor_roll' => false,
        'en_riesgo'  => false,
        'reprobado_alguna' => false,
    ];

    public function __construct(int $id, string $nombre, int $edad, string $carrera, array $materias = [])
    {
        if ($edad <= 0) {
            throw new InvalidArgumentException("La edad debe ser positiva.");
        }
        $this->id = $id;
        $this->nombre = $nombre;
        $this->edad = $edad;
        $this->carrera = $carrera;
        foreach ($materias as $m => $c) {
            $this->agregarMateria((string)$m, (float)$c);
        }
        $this->actualizarFlags();
    }

    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getEdad(): int { return $this->edad; }
    public function getCarrera(): string { return $this->carrera; }
    /** @return array<string,float> */
    public function getMaterias(): array { return $this->materias; }
    /** @return array<string,bool> */
    public function getFlags(): array { return $this->flags; }

    public function agregarMateria(string $materia, float $calificacion): void
    {
        if ($calificacion < 0 || $calificacion > 100) {
            throw new InvalidArgumentException("La calificación debe estar entre 0 y 100.");
        }
        $this->materias[$materia] = $calificacion;
        $this->actualizarFlags();
    }

    public function obtenerPromedio(): float
    {
        if (empty($this->materias)) {
            return 0.0;
        }
        return array_sum($this->materias) / count($this->materias);
    }

    /** @return array<string,mixed> */
    public function obtenerDetalles(): array
    {
        return [
            'id'       => $this->id,
            'nombre'   => $this->nombre,
            'edad'     => $this->edad,
            'carrera'  => $this->carrera,
            'materias' => $this->materias,
            'promedio' => $this->obtenerPromedio(),
            'flags'    => $this->flags,
        ];
    }

    public function __toString(): string
    {
        return sprintf(
            "Estudiante #%d: %s (%s) – Edad: %d – Promedio: %.2f",
            $this->id, $this->nombre, $this->carrera, $this->edad, $this->obtenerPromedio()
        );
    }

    private function actualizarFlags(): void
    {
        $prom = $this->obtenerPromedio();
        $reprobadoAlguna = array_reduce($this->materias, function (bool $carry, float $nota): bool {
            return $carry || ($nota < 60.0);
        }, false);

        $this->flags = [
            'honor_roll' => $prom >= 90.0,
            'en_riesgo'  => $prom < 70.0 || $reprobadoAlguna,
            'reprobado_alguna' => $reprobadoAlguna,
        ];
    }

    /** @return array<string,mixed> serialización simple para JSON */
    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'nombre'   => $this->nombre,
            'edad'     => $this->edad,
            'carrera'  => $this->carrera,
            'materias' => $this->materias,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (string)$data['nombre'],
            (int)$data['edad'],
            (string)$data['carrera'],
            is_array($data['materias'] ?? []) ? $data['materias'] : []
        );
    }
}

final class SistemaGestionEstudiantes
{
    /** @var array<int,Estudiante> */
    private array $estudiantes = [];
    /** @var array<int,Estudiante> */
    private array $graduados = [];

    public function agregarEstudiante(Estudiante $estudiante): void
    {
        $id = $estudiante->getId();
        if (isset($this->estudiantes[$id])) {
            throw new RuntimeException("Ya existe un estudiante con ID $id.");
        }
        $this->estudiantes[$id] = $estudiante;
    }

    public function obtenerEstudiante(int $id): Estudiante
    {
        if (!isset($this->estudiantes[$id])) {
            throw new OutOfBoundsException("No existe estudiante con ID $id.");
        }
        return $this->estudiantes[$id];
    }

    /** @return Estudiante[] */
    public function listarEstudiantes(): array
    {
        return array_values($this->estudiantes);
    }

    public function calcularPromedioGeneral(): float
    {
        if (empty($this->estudiantes)) return 0.0;
        $promedios = array_map(fn(Estudiante $e) => $e->obtenerPromedio(), $this->estudiantes);
        return array_sum($promedios) / count($promedios);
    }

    /** @return Estudiante[] */
    public function obtenerEstudiantesPorCarrera(string $carrera): array
    {
        $carreraLower = mb_strtolower($carrera);
        return array_values(array_filter($this->estudiantes, function (Estudiante $e) use ($carreraLower) {
            return mb_strtolower($e->getCarrera()) === $carreraLower;
        }));
    }

    public function obtenerMejorEstudiante(): ?Estudiante
    {
        if (empty($this->estudiantes)) return null;
        return array_reduce($this->estudiantes, function (?Estudiante $best, Estudiante $cur): Estudiante {
            if ($best === null) return $cur;
            return $cur->obtenerPromedio() > $best->obtenerPromedio() ? $cur : $best;
        });
    }

    /**
     * Reporte por materia (en todo el sistema): promedio, máx, mín.
     * @return array<string,array{promedio:float,max:float,min:float}>
     */
    public function generarReporteRendimiento(): array
    {
        $acumulado = []; // materia => [sum, count, max, min]
        foreach ($this->estudiantes as $e) {
            foreach ($e->getMaterias() as $materia => $nota) {
                if (!isset($acumulado[$materia])) {
                    $acumulado[$materia] = ['sum' => 0.0, 'count' => 0, 'max' => $nota, 'min' => $nota];
                }
                $acumulado[$materia]['sum'] += $nota;
                $acumulado[$materia]['count']++;
                $acumulado[$materia]['max'] = max($acumulado[$materia]['max'], $nota);
                $acumulado[$materia]['min'] = min($acumulado[$materia]['min'], $nota);
            }
        }
        $reporte = [];
        foreach ($acumulado as $materia => $info) {
            $reporte[$materia] = [
                'promedio' => $info['count'] ? $info['sum'] / $info['count'] : 0.0,
                'max' => $info['max'],
                'min' => $info['min'],
            ];
        }
        ksort($reporte);
        return $reporte;
    }

    public function graduarEstudiante(int $id): bool
    {
        if (!isset($this->estudiantes[$id])) return false;
        $this->graduados[$id] = $this->estudiantes[$id];
        unset($this->estudiantes[$id]);
        return true;
    }

    /** @return Estudiante[] */
    public function listarGraduados(): array
    {
        return array_values($this->graduados);
    }

    /** Ranking descendente por promedio. @return Estudiante[] */
    public function generarRanking(): array
    {
        $lista = $this->listarEstudiantes();
        usort($lista, function (Estudiante $a, Estudiante $b): int {
            return $b->obtenerPromedio() <=> $a->obtenerPromedio();
        });
        return $lista;
    }

    /**
     * Búsqueda parcial case-insensitive por nombre o carrera.
     * @return Estudiante[]
     */
    public function buscar(string $termino): array
    {
        $needle = mb_strtolower($termino);
        return array_values(array_filter($this->estudiantes, function (Estudiante $e) use ($needle) {
            return str_contains(mb_strtolower($e->getNombre()), $needle)
                || str_contains(mb_strtolower($e->getCarrera()), $needle);
        }));
    }

    /**
     * Estadísticas por carrera: número de estudiantes, promedio general y mejor estudiante.
     * @return array<string,array{cantidad:int,promedio:float,mejor:string|null}>
     */
    public function estadisticasPorCarrera(): array
    {
        $porCarrera = [];
        foreach ($this->estudiantes as $e) {
            $c = $e->getCarrera();
            if (!isset($porCarrera[$c])) {
                $porCarrera[$c] = ['cantidad' => 0, 'sumProm' => 0.0, 'mejor' => null, 'mejorProm' => -1.0];
            }
            $porCarrera[$c]['cantidad']++;
            $p = $e->obtenerPromedio();
            $porCarrera[$c]['sumProm'] += $p;
            if ($p > $porCarrera[$c]['mejorProm']) {
                $porCarrera[$c]['mejorProm'] = $p;
                $porCarrera[$c]['mejor'] = $e->__toString();
            }
        }
        // Normalizar
        $out = [];
        foreach ($porCarrera as $c => $data) {
            $out[$c] = [
                'cantidad' => $data['cantidad'],
                'promedio' => $data['cantidad'] ? $data['sumProm'] / $data['cantidad'] : 0.0,
                'mejor'    => $data['mejor'],
            ];
        }
        ksort($out);
        return $out;
    }

    // ---------------------- Persistencia JSON (opcional) ----------------------

    public function guardarEnJson(string $ruta): void
    {
        $payload = [
            'estudiantes' => array_map(fn(Estudiante $e) => $e->toArray(), $this->listarEstudiantes()),
            'graduados'   => array_map(fn(Estudiante $e) => $e->toArray(), $this->listarGraduados()),
        ];
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new RuntimeException("Error al codificar JSON.");
        }
        if (file_put_contents($ruta, $json) === false) {
            throw new RuntimeException("No se pudo escribir el archivo JSON en $ruta.");
        }
    }

    public function cargarDesdeJson(string $ruta): void
    {
        if (!file_exists($ruta)) {
            throw new RuntimeException("No existe el archivo $ruta.");
        }
        $raw = file_get_contents($ruta);
        if ($raw === false) {
            throw new RuntimeException("No se pudo leer $ruta.");
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException("JSON inválido en $ruta.");
        }
        $this->estudiantes = [];
        $this->graduados = [];
        foreach (($data['estudiantes'] ?? []) as $e) {
            $obj = Estudiante::fromArray($e);
            $this->estudiantes[$obj->getId()] = $obj;
        }
        foreach (($data['graduados'] ?? []) as $e) {
            $obj = Estudiante::fromArray($e);
            $this->graduados[$obj->getId()] = $obj;
        }
    }
}

// --------------------------- Sección de Pruebas --------------------------- //
if (PHP_SAPI === 'cli') {
    $sistema = new SistemaGestionEstudiantes();

    // Crear 10 estudiantes con distintas carreras y materias
    $datosDemo = [
        [1, 'Ana López', 20, 'Ingeniería', ['Cálculo' => 95, 'Física' => 88, 'Programación' => 92]],
        [2, 'Juan Pérez', 22, 'Derecho', ['Derecho Civil' => 78, 'Derecho Penal' => 81, 'Romanas' => 74]],
        [3, 'María Gómez', 21, 'Medicina', ['Anatomía' => 89, 'Fisiología' => 93, 'Bioquímica' => 91]],
        [4, 'Pedro Ruiz', 19, 'Arquitectura', ['Dibujo' => 65, 'Historia' => 72, 'Taller' => 68]],
        [5, 'Laura Díaz', 23, 'Ingeniería', ['Cálculo' => 99, 'Física' => 96, 'Programación' => 97]],
        [6, 'Carlos Vega', 20, 'Psicología', ['Teoría' => 84, 'Estadística' => 88, 'Neuro' => 80]],
        [7, 'Sofía Méndez', 22, 'Medicina', ['Anatomía' => 76, 'Fisiología' => 82, 'Bioquímica' => 79]],
        [8, 'Diego Torres', 21, 'Administración', ['Finanzas' => 90, 'Marketing' => 87, 'Contabilidad' => 92]],
        [9, 'Elena Ríos', 20, 'Arquitectura', ['Dibujo' => 71, 'Historia' => 69, 'Taller' => 74]],
        [10, 'Pablo Navas', 24, 'Ingeniería', ['Cálculo' => 58, 'Física' => 62, 'Programación' => 55]],
    ];

    foreach ($datosDemo as [$id, $nombre, $edad, $carrera, $mats]) {
        $sistema->agregarEstudiante(new Estudiante($id, $nombre, $edad, $carrera, $mats));
    }

    echo "=== LISTA INICIAL ===\n";
    foreach ($sistema->listarEstudiantes() as $e) {
        echo $e . PHP_EOL;
    }
    echo PHP_EOL;

    echo "=== PROMEDIO GENERAL ===\n";
    echo number_format($sistema->calcularPromedioGeneral(), 2) . PHP_EOL . PHP_EOL;

    echo "=== MEJOR ESTUDIANTE ===\n";
    $best = $sistema->obtenerMejorEstudiante();
    echo ($best ? $best->__toString() : "N/A") . PHP_EOL . PHP_EOL;

    echo "=== BUSCAR 'ing'" . " ===\n";
    foreach ($sistema->buscar('ing') as $e) {
        echo $e . PHP_EOL;
    }
    echo PHP_EOL;

    echo "=== ESTUDIANTES POR CARRERA: Ingeniería ===\n";
    foreach ($sistema->obtenerEstudiantesPorCarrera('Ingeniería') as $e) {
        echo $e . PHP_EOL;
    }
    echo PHP_EOL;

    echo "=== RANKING (top 5) ===\n";
    $rank = $sistema->generarRanking();
    foreach (array_slice($rank, 0, 5) as $pos => $e) {
        printf("#%d %s\n", $pos + 1, $e->__toString());
    }
    echo PHP_EOL;

    echo "=== REPORTE POR MATERIA ===\n";
    foreach ($sistema->generarReporteRendimiento() as $materia => $info) {
        printf("%s -> Prom: %.2f | Max: %.2f | Min: %.2f\n", $materia, $info['promedio'], $info['max'], $info['min']);
    }
    echo PHP_EOL;

    echo "=== ESTADÍSTICAS POR CARRERA ===\n";
    foreach ($sistema->estadisticasPorCarrera() as $c => $s) {
        printf("%s -> Cantidad: %d | Promedio: %.2f | Mejor: %s\n", $c, $s['cantidad'], $s['promedio'], $s['mejor'] ?? 'N/A');
    }
    echo PHP_EOL;

    echo "=== GRADUAR ESTUDIANTE #4 ===\n";
    var_export($sistema->graduarEstudiante(4));
    echo PHP_EOL;
    echo "Graduados: " . count($sistema->listarGraduados()) . PHP_EOL . PHP_EOL;

    echo "=== PERSISTENCIA JSON (opcional) ===\n";
    $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'estudiantes_demo.json';
    $sistema->guardarEnJson($jsonPath);
    echo "Guardado en: $jsonPath\n";
    $nuevo = new SistemaGestionEstudiantes();
    $nuevo->cargarDesdeJson($jsonPath);
    echo "Cargados desde JSON: " . count($nuevo->listarEstudiantes()) . " estudiantes.\n";
}
