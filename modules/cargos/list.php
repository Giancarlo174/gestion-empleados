<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /ds6/index.php");
    exit;
}

// Verificar si es administrador
if (!$_SESSION["is_admin"]) {
    header("location: /ds6/dashboard.php");
    exit;
}

// Incluir archivo de configuración
require_once "../../config/db.php";

// Configuración de paginación
$registros_por_pagina = 10;
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$inicio = ($pagina - 1) * $registros_por_pagina;

// Condición de búsqueda
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$departamento = isset($_GET['departamento']) ? trim($_GET['departamento']) : '';

$where = "";
$params = [];
$tipos = "";

if (!empty($busqueda)) {
    $where .= empty($where) ? "WHERE " : " AND ";
    $where .= "c.nombre LIKE ?";
    $params[] = "%$busqueda%";
    $tipos .= "s";
}

if (!empty($departamento)) {
    $where .= empty($where) ? "WHERE " : " AND ";
    $where .= "c.dep_codigo = ?";
    $params[] = $departamento;
    $tipos .= "s";
}

// Consultar total de registros con filtros
$sql_total = "SELECT COUNT(*) as total FROM cargo c $where";
if (!empty($params)) {
    $stmt_total = mysqli_prepare($conn, $sql_total);
    mysqli_stmt_bind_param($stmt_total, $tipos, ...$params);
    mysqli_stmt_execute($stmt_total);
    $result_total = mysqli_stmt_get_result($stmt_total);
    $row_total = mysqli_fetch_assoc($result_total);
    $total_registros = $row_total['total'];
    mysqli_stmt_close($stmt_total);
} else {
    $result_total = mysqli_query($conn, $sql_total);
    $row_total = mysqli_fetch_assoc($result_total);
    $total_registros = $row_total['total'];
}

$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consultar datos de cargos
$sql = "SELECT c.*, d.nombre as nombre_departamento 
        FROM cargo c 
        LEFT JOIN departamento d ON c.dep_codigo = d.codigo 
        $where 
        ORDER BY d.nombre, c.nombre ASC 
        LIMIT ?, ?";

$tipos .= "ii";
$params[] = $inicio;
$params[] = $registros_por_pagina;

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $tipos, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Obtener todos los departamentos para el filtro
$query_departamentos = "SELECT * FROM departamento ORDER BY nombre";
$result_departamentos = mysqli_query($conn, $query_departamentos);

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Lista de Cargos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="add.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-plus"></i> Nuevo Cargo
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-5">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Buscar por nombre" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select" name="departamento">
                    <option value="">Todos los departamentos</option>
                    <?php while ($row_dep = mysqli_fetch_assoc($result_departamentos)): ?>
                        <option value="<?php echo $row_dep['codigo']; ?>" <?php if ($departamento === $row_dep['codigo']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($row_dep['nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-grid gap-2 d-md-flex">
                    <button class="btn btn-primary flex-fill" type="submit">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="list.php" class="btn btn-secondary flex-fill">Limpiar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (mysqli_num_rows($result) > 0): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Código</th>
                <th>Nombre del Cargo</th>
                <th>Departamento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                <td><?php echo htmlspecialchars($row['nombre_departamento']); ?></td>
                <td>
                    <a href="edit.php?codigo=<?php echo $row['codigo']; ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="delete.php?codigo=<?php echo $row['codigo']; ?>" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<?php if ($total_paginas > 1): ?>
<nav aria-label="Navegación de páginas">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?pagina=1&busqueda=<?php echo urlencode($busqueda); ?>&departamento=<?php echo urlencode($departamento); ?>">Primero</a>
        </li>
        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&departamento=<?php echo urlencode($departamento); ?>">Anterior</a>
        </li>
        
        <?php
        $rango = 2;
        for ($i = max(1, $pagina - $rango); $i <= min($pagina + $rango, $total_paginas); $i++) {
            echo '<li class="page-item ' . (($i == $pagina) ? 'active' : '') . '">';
            echo '<a class="page-link" href="?pagina=' . $i . '&busqueda=' . urlencode($busqueda) . '&departamento=' . urlencode($departamento) . '">' . $i . '</a>';
            echo '</li>';
        }
        ?>
        
        <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&departamento=<?php echo urlencode($departamento); ?>">Siguiente</a>
        </li>
        <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?pagina=<?php echo $total_paginas; ?>&busqueda=<?php echo urlencode($busqueda); ?>&departamento=<?php echo urlencode($departamento); ?>">Último</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> No se encontraron cargos.
</div>
<?php endif; ?>

<div class="text-center mt-3">
    <p>Mostrando <?php echo mysqli_num_rows($result); ?> de <?php echo $total_registros; ?> registros</p>
</div>

<?php 
// Cerrar conexión
mysqli_stmt_close($stmt);
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php";
?>
