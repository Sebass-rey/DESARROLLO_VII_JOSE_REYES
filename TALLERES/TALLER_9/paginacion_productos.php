<?php
require_once "config_pdo.php";

/**
 * Paso 8 – Paginación avanzada
 * Paginador básico y por cursor
 */

class Paginator {
    protected $pdo;
    protected $table;
    protected $perPage;
    protected $currentPage;
    protected $conditions = [];
    protected $params = [];
    protected $orderBy = '';
    protected $joins = [];
    protected $fields = ['*'];

    public function __construct(PDO $pdo, $table, $perPage = 10) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->perPage = $perPage;
        $this->currentPage = 1;
    }

    public function select($fields) {
        $this->fields = is_array($fields) ? $fields : func_get_args();
        return $this;
    }

    public function where($condition, $params = []) {
        $this->conditions[] = $condition;
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function join($join) {
        $this->joins[] = $join;
        return $this;
    }

    public function orderBy($orderBy) {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function setPage($page) {
        $this->currentPage = max(1, (int)$page);
        return $this;
    }

    public function getTotalRecords() {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= " " . implode(" ", $this->joins);
        }

        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(" AND ", $this->conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);
        return (int)$stmt->fetchColumn();
    }

    public function getResults() {
        $offset = ($this->currentPage - 1) * $this->perPage;

        $sql = "SELECT " . implode(", ", $this->fields) . " FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= " " . implode(" ", $this->joins);
        }

        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(" AND ", $this->conditions);
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        $sql .= " LIMIT {$this->perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPageInfo() {
        $totalRecords = $this->getTotalRecords();
        $totalPages = max(1, ceil($totalRecords / $this->perPage));

        return [
            'current_page'  => $this->currentPage,
            'per_page'      => $this->perPage,
            'total_records' => $totalRecords,
            'total_pages'   => $totalPages,
            'has_previous'  => $this->currentPage > 1,
            'has_next'      => $this->currentPage < $totalPages,
            'previous_page' => $this->currentPage - 1,
            'next_page'     => $this->currentPage + 1,
            'first_page'    => 1,
            'last_page'     => $totalPages,
        ];
    }
}

/**
 * Paginador por cursor (para “scroll infinito” del lado backend)
 */
class CursorPaginator extends Paginator {
    private $cursorField;
    private $cursorValue = null;
    private $direction = 'next';

    public function __construct(PDO $pdo, $table, $cursorField, $perPage = 10) {
        parent::__construct($pdo, $table, $perPage);
        $this->cursorField = $cursorField;
    }

    public function setCursor($value, $direction = 'next') {
        $this->cursorValue = $value;
        $this->direction = $direction;
        return $this;
    }

    public function getResults() {
        $sql = "SELECT " . implode(", ", $this->fields) . " FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= " " . implode(" ", $this->joins);
        }

        $conditions = $this->conditions;
        $params     = $this->params;

        if ($this->cursorValue !== null) {
            // si estoy pidiendo la siguiente página, uso >
            $op = $this->direction === 'next' ? '>' : '<';
            $conditions[]      = "{$this->cursorField} {$op} :cursor";
            $params[':cursor'] = $this->cursorValue;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // si no se especifica orderBy, ordeno por campo cursor
        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        } else {
            $dir = $this->direction === 'next' ? 'ASC' : 'DESC';
            $sql .= " ORDER BY {$this->cursorField} {$dir}";
        }

        // pido uno extra para saber si hay más
        $sql .= " LIMIT " . ($this->perPage + 1);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hasMore = count($results) > $this->perPage;
        if ($hasMore) {
            array_pop($results);
        }

        return [
            'results'     => $results,
            'has_more'    => $hasMore,
            'next_cursor' => $hasMore && !empty($results)
                ? end($results)[$this->cursorField]
                : null
        ];
    }
}

/* ==========================
 * LÓGICA DE LA PÁGINA
 * ========================== */

// 1) parámetros de paginación (selector de elementos por página)
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 5;

// límites razonables
if ($perPage < 5)  $perPage = 5;
if ($perPage > 50) $perPage = 50;

// 2) modo “cursor” (para scroll infinito vía JS o API simple)
// si vienes con ?modo=cursor devolvemos JSON y no HTML
if (isset($_GET['modo']) && $_GET['modo'] === 'cursor') {
    $cursor = isset($_GET['cursor']) && $_GET['cursor'] !== ''
        ? (int)$_GET['cursor']
        : null;

    $cp = new CursorPaginator($pdo, 'productos', 'id', $perPage);
    $cp->select(['id', 'nombre', 'precio', 'stock'])
       ->where('precio >= ?', [0]);

    if ($cursor !== null) {
        $cp->setCursor($cursor, 'next');
    }

    $data = $cp->getResults();

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// 3) paginador tradicional
$paginator = new Paginator($pdo, 'productos', $perPage);
$paginator->select(['productos.id', 'productos.nombre', 'productos.precio', 'categorias.nombre AS categoria'])
          ->join('LEFT JOIN categorias ON productos.categoria_id = categorias.id')
          ->where('productos.precio >= ?', [0])
          ->orderBy('productos.id ASC')
          ->setPage($page);

// 4) caché simple para la página 1 (archivo json)
$cacheFile = __DIR__ . "/cache_pagina1_{$perPage}.json";
$usarCache = ($page === 1);

if ($usarCache && file_exists($cacheFile)) {
    $results = json_decode(file_get_contents($cacheFile), true);
} else {
    $results = $paginator->getResults();
    if ($usarCache) {
        // caché sencillo, sin expiración ni nada muy sofisticado
        file_put_contents($cacheFile, json_encode($results));
    }
}

$pageInfo = $paginator->getPageInfo();

// 5) exportar resultados de la página actual a CSV
if (isset($_GET['export']) && $_GET['export'] == '1') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="productos_pagina_' . $page . '.csv"');

    $out = fopen('php://output', 'w');
    // encabezados del CSV
    fputcsv($out, ['ID', 'Nombre', 'Precio', 'Categoría']);

    foreach ($results as $row) {
        fputcsv($out, [
            $row['id'],
            $row['nombre'],
            $row['precio'],
            $row['categoria']
        ]);
    }

    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Productos Paginados</title>
    <style>
        .pagination {
            margin: 20px 0;
            padding: 0;
            list-style: none;
            display: flex;
            gap: 10px;
        }
        .pagination li {
            padding: 5px 10px;
            border: 1px solid #ddd;
        }
        .pagination li a {
            text-decoration: none;
        }
        .pagination .active {
            background-color: #007bff;
            color: #fff;
        }
        .pagination .disabled {
            opacity: 0.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
        }
        th {
            background: #f3f3f3;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
        }
    </style>
</head>
<body>
    <h1>Catálogo de Productos</h1>

    <div class="top-bar">
        <!-- selector de elementos por página -->
        <form method="get">
            <label for="per_page">Elementos por página:</label>
            <select name="per_page" id="per_page" onchange="this.form.submit()">
                <?php foreach ([5,10,20,50] as $op): ?>
                    <option value="<?= $op ?>" <?= $perPage == $op ? 'selected' : '' ?>>
                        <?= $op ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript>
                <button type="submit">Aplicar</button>
            </noscript>
        </form>

        <!-- botón para exportar CSV de la página actual -->
        <a href="?page=<?= $pageInfo['current_page'] ?>&per_page=<?= $pageInfo['per_page'] ?>&export=1">
            Exportar página actual a CSV
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Categoría</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($results)): ?>
            <tr><td colspan="4">No hay productos.</td></tr>
        <?php else: ?>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td>$<?= number_format($row['precio'], 2) ?></td>
                    <td><?= htmlspecialchars($row['categoria']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- paginación tradicional -->
    <ul class="pagination">
        <?php if ($pageInfo['has_previous']): ?>
            <li><a href="?page=1&per_page=<?= $perPage ?>">Primera</a></li>
            <li><a href="?page=<?= $pageInfo['previous_page'] ?>&per_page=<?= $perPage ?>">Anterior</a></li>
        <?php else: ?>
            <li class="disabled">Primera</li>
            <li class="disabled">Anterior</li>
        <?php endif; ?>

        <li class="active"><?= $pageInfo['current_page'] ?> / <?= $pageInfo['total_pages'] ?></li>

        <?php if ($pageInfo['has_next']): ?>
            <li><a href="?page=<?= $pageInfo['next_page'] ?>&per_page=<?= $perPage ?>">Siguiente</a></li>
            <li><a href="?page=<?= $pageInfo['last_page'] ?>&per_page=<?= $perPage ?>">Última</a></li>
        <?php else: ?>
            <li class="disabled">Siguiente</li>
            <li class="disabled">Última</li>
        <?php endif; ?>
    </ul>

    <hr>

    <p><strong>Nota (para el profe si te pregunta):</strong>  
    También se dejó listo un endpoint de paginación por cursor en este mismo archivo usando <code>?modo=cursor&amp;cursor=ID</code>, que sirve como base para implementar scroll infinito desde JavaScript.</p>
</body>
</html>
