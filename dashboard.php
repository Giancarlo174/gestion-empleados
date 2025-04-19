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

// Verificar si es un empleado regular para mostrar solo su información
if (!$_SESSION["is_admin"]) {
    $cedula = $_SESSION["cedula"];
    header("location: modules/empleados/view.php?cedula=$cedula");
    exit;
}

// Incluir header
include "includes/header.php";
?>

<!-- Encabezado del Dashboard -->
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-4">
    <h1 class="h2 fw-bold text-primary">
        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
    </h1>
</div>

<!-- Tarjetas de estadísticas -->
<div class="row g-4 mb-5">
    <!-- Total Empleados -->
    <div class="col-12 col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total Empleados</h6>
                        <h2 class="card-title fw-bold text-primary mb-0"><?php echo $totalEmpleados; ?></h2>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer border-0 bg-white py-3">
                <a href="modules/empleados/list.php" class="text-decoration-none d-flex align-items-center">
                    <span class="small fw-semibold">Ver todos los empleados</span>
                    <i class="fas fa-arrow-right ms-auto small"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Empleados Activos -->
    <div class="col-12 col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Empleados Activos</h6>
                        <h2 class="card-title fw-bold text-success mb-0"><?php echo $totalActivos; ?></h2>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="fas fa-user-check fa-2x text-success"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer border-0 bg-white py-3">
                <a href="modules/empleados/list.php?estado=1" class="text-decoration-none d-flex align-items-center">
                    <span class="small fw-semibold">Ver empleados activos</span>
                    <i class="fas fa-arrow-right ms-auto small"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Empleados Inactivos -->
    <div class="col-12 col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Empleados Inactivos</h6>
                        <h2 class="card-title fw-bold text-warning mb-0"><?php echo $totalInactivos; ?></h2>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="fas fa-user-times fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer border-0 bg-white py-3">
                <a href="modules/empleados/list.php?estado=0" class="text-decoration-none d-flex align-items-center">
                    <span class="small fw-semibold">Ver empleados inactivos</span>
                    <i class="fas fa-arrow-right ms-auto small"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Distribución por departamento -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-chart-pie text-primary me-2"></i>
                    <h5 class="fw-bold mb-0">Distribución por Departamento</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 ps-4">Departamento</th>
                                <th class="border-0 text-center">Empleados</th>
                                <th class="border-0 text-center pe-4">Porcentaje</th>
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
                                    echo "<td class='ps-4'><span class='fw-medium'>" . htmlspecialchars($row['nombre']) . "</span></td>";
                                    echo "<td class='text-center'>" . $row['cantidad'] . "</td>";
                                    echo "<td class='pe-4 text-center'>";
                                    echo "<div class='d-flex align-items-center justify-content-center'>";
                                    echo "<div class='progress flex-grow-1 me-2' style='height: 6px;'>";
                                    echo "<div class='progress-bar bg-primary' role='progressbar' style='width: {$porcentaje}%'></div>";
                                    echo "</div>";
                                    echo "<span class='small fw-medium'>{$porcentaje}%</span>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center py-4'>No hay datos disponibles</td></tr>";
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
