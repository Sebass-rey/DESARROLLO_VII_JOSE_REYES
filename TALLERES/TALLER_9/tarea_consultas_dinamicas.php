<?php
require_once "config_pdo.php";

/* =====================================================
 * CLASES PARA CONSULTAS DINÁMICAS 
 * ===================================================== */

class QueryBuilder {
    private $pdo;
    private $table;
    private $conditions = [];
    private $parameters = [];
    private $orderBy = [];
    private $limit = null;
    private $offset = null;
    private $joins = [];
    private $groupBy = [];
    private $having = [];
    private $fields = ['*'];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function table($table) {
        $this->table = $table;
        return $this;
    }

    public function select($fields) {
        $this->fields = is_array($fields) ? $fields : func_get_args();
        return $this;
    }

    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $placeholder = ':' . str_replace('.', '_', $column) . count($this->parameters);
        $this->conditions[] = "$column $operator $placeholder";
        $this->parameters[$placeholder] = $value;

        return $this;
    }

    public function whereIn($column, array $values) {
        $placeholders = [];
        foreach ($values as $i => $value) {
            $placeholder = ':' . str_replace('.', '_', $column) . $i;
            $placeholders[] = $placeholder;
            $this->parameters[$placeholder] = $value;
        }

        $this->conditions[] = "$column IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }

    public function join($table, $first, $operator, $second, $type = 'INNER') {
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'conditions' => "$first $operator $second"
        ];
        return $this;
    }

    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function groupBy($columns) {
        $this->groupBy = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function having($condition, $value) {
        $placeholder = ':having' . count($this->parameters);
        $this->having[] = "$condition $placeholder";
        $this->parameters[$placeholder] = $value;
        return $this;
    }

    public function limit($limit, $offset = null) {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    public function buildQuery() {
        $sql = "SELECT " . implode(', ', $this->fields) . " FROM " . $this->table;

        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['conditions']}";
        }

        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(' AND ', $this->conditions);
        }

        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        if (!empty($this->having)) {
            $sql .= " HAVING " . implode(' AND ', $this->having);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
            if ($this->offset !== null) {
                $sql .= " OFFSET " . $this->offset;
            }
        }

        return $sql;
    }

    public function execute() {
        $sql = $this->buildQuery();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->parameters);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getParameters() {
        return $this->parameters;
    }
}

class InsertBuilder {
    private $pdo;
    private $table;
    private $data = [];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function into($table) {
        $this->table = $table;
        return $this;
    }

    public function values(array $data) {
        $this->data = $data;
        return $this;
    }

    public function execute() {
        $columns = array_keys($this->data);
        $placeholders = array_map(function($col) {
            return ':' . $col;
        }, $columns);

        $sql = "INSERT INTO {$this->table} (" .
               implode(', ', $columns) .
               ") VALUES (" .
               implode(', ', $placeholders) .
               ")";

        $stmt = $this->pdo->prepare($sql);
        $params = array_combine($placeholders, array_values($this->data));
        return $stmt->execute($params);
    }
}

class UpdateBuilder {
    private $pdo;
    private $table;
    private $data = [];
    private $conditions = [];
    private $parameters = [];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function table($table) {
        $this->table = $table;
        return $this;
    }

    public function set(array $data) {
        $this->data = $data;
        return $this;
    }

    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $placeholder = ':where_' . str_replace('.', '_', $column);
        $this->conditions[] = "$column $operator $placeholder";
        $this->parameters[$placeholder] = $value;

        return $this;
    }

    public function execute() {
        $setParts = [];
        foreach ($this->data as $column => $value) {
            $placeholder = ':set_' . $column;
            $setParts[] = "$column = $placeholder";
            $this->parameters[$placeholder] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts);

        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(' AND ', $this->conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($this->parameters);
    }
}

/* =====================================================
 * FUNCIONES DE LA TAREA
 * ===================================================== */

/**
 * 1) Sistema de filtrado de productos con múltiples criterios
 */
function filtrarProductos($pdo, $criterios = [])
{
    $qb = new QueryBuilder($pdo);

    $qb->table('productos p')
       ->select(['p.id', 'p.nombre', 'p.precio', 'p.stock', 'c.nombre AS categoria'])
       ->join('categorias c', 'p.categoria_id', '=', 'c.id');

    if (!empty($criterios['nombre'])) {
        $qb->where('p.nombre', 'LIKE', '%' . $criterios['nombre'] . '%');
    }

    if (isset($criterios['precio_min'])) {
        $qb->where('p.precio', '>=', $criterios['precio_min']);
    }

    if (isset($criterios['precio_max'])) {
        $qb->where('p.precio', '<=', $criterios['precio_max']);
    }

    if (!empty($criterios['categorias']) && is_array($criterios['categorias'])) {
        $qb->whereIn('c.id', $criterios['categorias']);
    }

    if (!empty($criterios['solo_disponibles'])) {
        $qb->where('p.stock', '>', 0);
    }

    // Orden seguro
    $columnaOrden = 'p.nombre';
    $columnasPermitidas = ['p.nombre', 'p.precio', 'p.stock'];

    if (!empty($criterios['ordenar_por']) && in_array($criterios['ordenar_por'], $columnasPermitidas)) {
        $columnaOrden = $criterios['ordenar_por'];
    }

    $direccion = (!empty($criterios['orden']) && strtoupper($criterios['orden']) === 'DESC')
        ? 'DESC'
        : 'ASC';

    $qb->orderBy($columnaOrden, $direccion);

    // Paginación
    $limite = isset($criterios['limite']) ? (int)$criterios['limite'] : 10;
    $offset = isset($criterios['offset']) ? (int)$criterios['offset'] : 0;
    $qb->limit($limite, $offset);

    return $qb->execute();
}

/**
 * 2) Generador de reportes dinámico de productos/categorías
 */
function generarReporteProductos($pdo, $opciones = [])
{
    $qb = new QueryBuilder($pdo);

    $camposPorDefecto = [
        'p.id',
        'p.nombre',
        'c.nombre AS categoria',
        'p.precio',
        'p.stock'
    ];

    $campos = !empty($opciones['campos']) && is_array($opciones['campos'])
        ? $opciones['campos']
        : $camposPorDefecto;

    $qb->table('productos p')
       ->select($campos)
       ->join('categorias c', 'p.categoria_id', '=', 'c.id');

    if (!empty($opciones['categoria_id'])) {
        $qb->where('c.id', $opciones['categoria_id']);
    }

    if (!empty($opciones['con_stock'])) {
        $qb->where('p.stock', '>', 0);
    }

    if (!empty($opciones['agrupar_por_categoria'])) {
        $qb->groupBy('c.id');
    }

    return $qb->execute();
}

/**
 * 3) Búsqueda de ventas con filtros
 */
function buscarVentasAvanzado($pdo, $filtros = [])
{
    $qb = new QueryBuilder($pdo);

    $qb->table('ventas v')
       ->select([
           'v.id',
           'v.fecha_venta',
           'v.total',
           'v.estado',
           'c.nombre AS cliente',
           'GROUP_CONCAT(p.nombre SEPARATOR \', \') AS productos'
       ])
       ->join('clientes c', 'v.cliente_id', '=', 'c.id')
       ->join('detalles_venta dv', 'v.id', '=', 'dv.venta_id')
       ->join('productos p', 'dv.producto_id', '=', 'p.id')
       ->groupBy(['v.id', 'v.fecha_venta', 'v.total', 'v.estado', 'c.nombre']);

    if (!empty($filtros['fecha_inicio'])) {
        // comparamos directamente con la columna, sin DATE()
        $qb->where('v.fecha_venta', '>=', $filtros['fecha_inicio']);
    }

    if (!empty($filtros['fecha_fin'])) {
        $qb->where('v.fecha_venta', '<=', $filtros['fecha_fin']);
    }


    if (!empty($filtros['cliente_id'])) {
        $qb->where('v.cliente_id', $filtros['cliente_id']);
    }

    if (isset($filtros['monto_min'])) {
        $qb->where('v.total', '>=', $filtros['monto_min']);
    }

    if (isset($filtros['monto_max'])) {
        $qb->where('v.total', '<=', $filtros['monto_max']);
    }

    $qb->limit(20, 0);

    return $qb->execute();
}

/**
 * 4) Actualización masiva de productos
 */
function actualizacionMasivaProductos($pdo, $criterios = [], $cambios = [])
{
    if (empty($cambios)) {
        return false;
    }

    $ub = new UpdateBuilder($pdo);
    $ub->table('productos')
       ->set($cambios);

    if (!empty($criterios['categoria_id'])) {
        $ub->where('categoria_id', $criterios['categoria_id']);
    }

    if (isset($criterios['precio_min'])) {
        $ub->where('precio', '>=', $criterios['precio_min']);
    }

    if (isset($criterios['precio_max'])) {
        $ub->where('precio', '<=', $criterios['precio_max']);
    }

    if (!empty($criterios['solo_disponibles'])) {
        $ub->where('stock', '>', 0);
    }

    return $ub->execute();
}

/* =====================================================
 * EJEMPLOS DE USO 
 * ===================================================== */

// 1) Filtrar productos
$criteriosProductos = [
    'nombre'          => 'Laptop',
    'precio_min'      => 100,
    'precio_max'      => 2000,
    'categorias'      => [1, 2],
    'solo_disponibles'=> true,
    'ordenar_por'     => 'p.precio',
    'orden'           => 'DESC',
    'limite'          => 10,
    'offset'          => 0
];

$resultadoProductos = filtrarProductos($pdo, $criteriosProductos);
echo "<h2>1) Productos filtrados</h2><pre>";
print_r($resultadoProductos);
echo "</pre>";

// 2) Reporte dinámico
$opcionesReporte = [
    'campos' => ['c.nombre AS categoria', 'COUNT(p.id) AS total_productos'],
    'agrupar_por_categoria' => true,
    'con_stock' => true
];

$reporte = generarReporteProductos($pdo, $opcionesReporte);
echo "<h2>2) Reporte de productos por categoría</h2><pre>";
print_r($reporte);
echo "</pre>";

// 3) Búsqueda avanzada de ventas
$filtrosVentas = [
    'fecha_inicio' => '2020-01-01',
    'fecha_fin'    => '2030-12-31',
    'cliente_id'   => 1,
    'monto_min'    => 100
];

$ventas = buscarVentasAvanzado($pdo, $filtrosVentas);
echo "<h2>3) Búsqueda avanzada de ventas</h2><pre>";
print_r($ventas);
echo "</pre>";

// 4) Actualización masiva de productos (ejemplo simple)
$criteriosUpdate = [
    'categoria_id'    => 1,
    'solo_disponibles'=> true
];

$cambios = [
    'stock' => 20  // ejemplo: dejar stock fijo en 20
];

$ok = actualizacionMasivaProductos($pdo, $criteriosUpdate, $cambios);
echo "<h2>4) Actualización masiva</h2>";
echo $ok ? "Actualización realizada.<br>" : "No se aplicó ningún cambio.<br>";

$pdo = null;
?>
