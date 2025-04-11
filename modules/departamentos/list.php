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
$where = "";

if (!empty($busqueda)) {
    $busqueda = mysqli_real_escape_string($conn, $busqueda);
    $where = "WHERE nombre LIKE '%$busqueda%' OR codigo LIKE '%$busqueda%'";
}

// Consultar total de registros con filtros
$sql_total = "SELECT COUNT(*) as total FROM departamento $where";
$result_total = mysqli_query($conn, $sql_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_registros = $row_total['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consultar datos de departamentos
$sql = "SELECT * FROM departamento $where ORDER BY nombre ASC LIMIT $inicio, $registros_por_pagina";
$result = mysqli_query($conn, $sql);

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Lista de Departamentos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="add.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-plus"></i> Nuevo Departamento
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Buscar por nombre o código" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <a href="list.php" class="btn btn-secondary w-100">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<?php if ($result && mysqli_num_rows($result) > 0): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Código</th>
                <th>Nombre de Departamento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
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
            <a class="page-link" href="?pagina=1&busqueda=<?php echo urlencode($busqueda); ?>">Primero</a>
        </li>
        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>">Anterior</a>
        </li>
        
        <?php
        $rango = 2;
        for ($i = max(1, $pagina - $rango); $i <= min($pagina + $rango, $total_paginas); $i++) {
            echo '<li class="page-item ' . (($i == $pagina) ? 'active' : '') . '">';
            echo '<a class="page-link" href="?pagina=' . $i . '&busqueda=' . urlencode($busqueda) . '">' . $i . '</a>';
            echo '</li>';
        }
        ?>
        
        <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>">Siguiente</a>
        </li>
        <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?pagina=<?php echo $total_paginas; ?>&busqueda=<?php echo urlencode($busqueda); ?>">Último</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> No se encontraron departamentos.
</div>
<?php endif; ?>

<div class="text-center mt-3">
    <p>Mostrando <?php echo ($result) ? mysqli_num_rows($result) : 0; ?> de <?php echo $total_registros; ?> registros</p>
</div>

<?php 
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php";
?>
