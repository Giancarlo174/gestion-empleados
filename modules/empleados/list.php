<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /ds6/index.php");
    exit;
}

// Incluir archivo de configuración
require_once "../../config/db.php";

// Verificar si es un empleado regular para mostrar solo su información
if (!$_SESSION["is_admin"]) {
    $cedula = $_SESSION["cedula"];
    header("location: view.php?cedula=$cedula");
    exit;
}

// Configuración de paginación
$registros_por_pagina = 10;
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
$inicio = ($pagina - 1) * $registros_por_pagina;

// Condición de búsqueda
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$where = " WHERE 1=1";
if (!empty($busqueda)) {
    $where .= " AND (e.cedula LIKE '%$busqueda%' OR e.nombre1 LIKE '%$busqueda%' OR e.apellido1 LIKE '%$busqueda%')";
}
if ($estado !== '') {
    $where .= " AND e.estado = '$estado'";
}

// Consultar total de registros con filtros
$sql_total = "SELECT COUNT(*) as total FROM empleados e $where";
$result_total = mysqli_query($conn, $sql_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_registros = $row_total['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consultar datos de empleados
$sql = "SELECT e.*, d.nombre as nombre_departamento 
        FROM empleados e 
        LEFT JOIN departamento d ON e.departamento = d.codigo 
        $where 
        ORDER BY e.nombre1 ASC 
        LIMIT $inicio, $registros_por_pagina";
$result = mysqli_query($conn, $sql);

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Lista de Empleados</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="add.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-user-plus"></i> Nuevo Empleado
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Buscar por cédula o nombre" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <select name="estado" class="form-select" onchange="this.form.submit()">
                    <option value="" <?php if($estado === '') echo 'selected'; ?>>Todos los estados</option>
                    <option value="1" <?php if($estado === '1') echo 'selected'; ?>>Activos</option>
                    <option value="0" <?php if($estado === '0') echo 'selected'; ?>>Inactivos</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="list.php" class="btn btn-secondary w-100">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<?php if (mysqli_num_rows($result) > 0): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Departamento</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                <td><?php echo htmlspecialchars($row['nombre1']); ?></td>
                <td><?php echo htmlspecialchars($row['apellido1']); ?></td>
                <td><?php echo htmlspecialchars($row['nombre_departamento']); ?></td>
                <td>
                    <?php if ($row['estado'] == 1): ?>
                    <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                    <span class="badge bg-danger">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="view.php?cedula=<?php echo $row['cedula']; ?>" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="edit.php?cedula=<?php echo $row['cedula']; ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="delete.php?cedula=<?php echo $row['cedula']; ?>" class="btn btn-danger btn-sm delete-confirm" data-bs-toggle="tooltip" title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<nav aria-label="Navegación de páginas">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?pagina=1&busqueda=<?php echo urlencode($busqueda); ?>&estado=<?php echo $estado; ?>">Primero</a>
        </li>
        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&estado=<?php echo $estado; ?>">Anterior</a>
        </li>
        
        <?php
        $rango = 2;
        for ($i = max(1, $pagina - $rango); $i <= min($pagina + $rango, $total_paginas); $i++) {
            echo '<li class="page-item ' . (($i == $pagina) ? 'active' : '') . '">';
            echo '<a class="page-link" href="?pagina=' . $i . '&busqueda=' . urlencode($busqueda) . '&estado=' . $estado . '">' . $i . '</a>';
            echo '</li>';
        }
        ?>
        
        <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&estado=<?php echo $estado; ?>">Siguiente</a>
        </li>
        <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?pagina=<?php echo $total_paginas; ?>&busqueda=<?php echo urlencode($busqueda); ?>&estado=<?php echo $estado; ?>">Último</a>
        </li>
    </ul>
</nav>

<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> No se encontraron registros.
</div>
<?php endif; ?>

<div class="text-center mt-3">
    <p>Mostrando <?php echo mysqli_num_rows($result); ?> de <?php echo $total_registros; ?> registros</p>
</div>

<?php 
// Cerrar conexión a la base de datos
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php"; 
?>
w