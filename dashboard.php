<?php
// Incluir archivo de configuración
require_once "config/db.php";

// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión, si no, redirigir a la página de login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

// Consultar estadísticas básicas
$totalEmpleados = 0;
$totalActivos = 0;
$totalInactivos = 0;
$totalDepartamentos = 0;

// Total de empleados
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM empleados");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalEmpleados = $row['total'];
}

// Empleados activos
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM empleados WHERE estado = 1");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalActivos = $row['total'];
}

// Empleados inactivos
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM empleados WHERE estado = 0");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalInactivos = $row['total'];
}

// Total de departamentos
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM departamento");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalDepartamentos = $row['total'];
}

// Verificar si es un empleado regular para mostrar solo su información
if (!$_SESSION["is_admin"]) {
    $cedula = $_SESSION["cedula"];
    header("location: modules/empleados/view.php?cedula=$cedula");
    exit;
}

// Incluir header
include "includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Empleados</h6>
                        <h2 class="card-text"><?php echo $totalEmpleados; ?></h2>
                    </div>
                    <i class="fas fa-users fa-3x"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="modules/empleados/list.php" class="text-white text-decoration-none">Ver detalles</a>
                <i class="fas fa-arrow-right"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Empleados Activos</h6>
                        <h2 class="card-text"><?php echo $totalActivos; ?></h2>
                    </div>
                    <i class="fas fa-user-check fa-3x"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="modules/empleados/list.php?estado=1" class="text-white text-decoration-none">Ver detalles</a>
                <i class="fas fa-arrow-right"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Empleados Inactivos</h6>
                        <h2 class="card-text"><?php echo $totalInactivos; ?></h2>
                    </div>
                    <i class="fas fa-user-times fa-3x"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="modules/empleados/list.php?estado=0" class="text-white text-decoration-none">Ver detalles</a>
                <i class="fas fa-arrow-right"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Departamentos</h6>
                        <h2 class="card-text"><?php echo $totalDepartamentos; ?></h2>
                    </div>
                    <i class="fas fa-building fa-3x"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <span class="text-white">Total departamentos</span>
                <i class="fas fa-building"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Últimos Empleados Agregados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Departamento</th>
                                <th>Fecha Contratación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Consultar los últimos 5 empleados agregados
                            $sql = "SELECT e.cedula, e.nombre1, e.apellido1, d.nombre as departamento, e.f_contra 
                                    FROM empleados e 
                                    LEFT JOIN departamento d ON e.departamento = d.codigo 
                                    ORDER BY e.f_contra DESC LIMIT 5";
                            $result = mysqli_query($conn, $sql);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['cedula'] . "</td>";
                                    echo "<td>" . $row['nombre1'] . " " . $row['apellido1'] . "</td>";
                                    echo "<td>" . $row['departamento'] . "</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['f_contra'])) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>No hay empleados registrados</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Distribución por Departamento</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Departamento</th>
                                <th>Cantidad de Empleados</th>
                                <th>Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Consultar distribución de empleados por departamento
                            $sql = "SELECT d.nombre, COUNT(*) as cantidad 
                                    FROM empleados e 
                                    LEFT JOIN departamento d ON e.departamento = d.codigo 
                                    GROUP BY e.departamento 
                                    ORDER BY cantidad DESC";
                            $result = mysqli_query($conn, $sql);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $porcentaje = ($totalEmpleados > 0) ? round(($row['cantidad'] / $totalEmpleados) * 100, 2) : 0;
                                    echo "<tr>";
                                    echo "<td>" . $row['nombre'] . "</td>";
                                    echo "<td>" . $row['cantidad'] . "</td>";
                                    echo "<td>" . $porcentaje . "%</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>No hay datos disponibles</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "includes/footer.php"; 
?>
