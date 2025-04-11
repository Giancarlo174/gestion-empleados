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

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Eliminar el empleado después de la confirmación
    $sql = "DELETE FROM empleados WHERE cedula = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Vincular las variables a la consulta preparada como parámetros
        mysqli_stmt_bind_param($stmt, "s", $param_cedula);
        
        // Asignar parámetros
        $param_cedula = trim($_POST["cedula"]);
        
        // Intentar ejecutar la consulta preparada
        if (mysqli_stmt_execute($stmt)) {
            // Redireccionar a la página de listado con mensaje de éxito
            header("location: list.php?deleted=1");
            exit();
        } else {
            // Si hay un error en la eliminación
            header("location: list.php?error=1&message=" . urlencode("Error al eliminar el empleado: " . mysqli_error($conn)));
            exit();
        }
        
        // Cerrar sentencia
        mysqli_stmt_close($stmt);
    }
} else {
    // Verificar si existe el parámetro cedula en la URL
    if (isset($_GET["cedula"]) && !empty(trim($_GET["cedula"]))) {
        // Obtener el parámetro cedula
        $cedula = trim($_GET["cedula"]);
        
        // Preparar la consulta para obtener información del empleado
        $sql = "SELECT cedula, nombre1, apellido1 FROM empleados WHERE cedula = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular la cedula como parámetro
            mysqli_stmt_bind_param($stmt, "s", $cedula);
            
            // Ejecutar la consulta
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) == 1) {
                    // Obtener los datos del empleado
                    $row = mysqli_fetch_assoc($result);
                } else {
                    // No se encontró el empleado
                    header("location: list.php?error=1&message=" . urlencode("No se encontró el empleado con la cédula especificada"));
                    exit();
                }
            } else {
                header("location: list.php?error=1&message=" . urlencode("Error al consultar el empleado: " . mysqli_error($conn)));
                exit();
            }
            
            mysqli_stmt_close($stmt);
        }
    } else {
        // Si no se proporcionó una cédula
        header("location: list.php?error=1&message=" . urlencode("Se requiere una cédula para eliminar un empleado"));
        exit();
    }
}

// Incluir header
include "../../includes/header.php";
?>

<!-- Mostrar formulario de confirmación solo si estamos en GET y tenemos datos del empleado -->
<?php if ($_SERVER["REQUEST_METHOD"] != "POST" && isset($row)): ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Eliminar Empleado</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="list.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver a la Lista
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body text-center">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
            <h2 class="mt-3">¿Está seguro que desea eliminar este empleado?</h2>
            <p class="lead text-muted">Esta acción no se puede deshacer.</p>
        </div>
        
        <div class="card mb-4 mx-auto" style="max-width: 30rem;">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Información del empleado a eliminar</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Cédula:</strong> <?php echo htmlspecialchars($row["cedula"]); ?></p>
                <p class="mb-0"><strong>Nombre:</strong> <?php echo htmlspecialchars($row["nombre1"] . " " . $row["apellido1"]); ?></p>
            </div>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="cedula" value="<?php echo $row["cedula"]; ?>">
            <div class="d-grid gap-2 col-6 mx-auto">
                <button type="submit" class="btn btn-danger btn-lg">
                    <i class="fas fa-trash-alt me-2"></i> Sí, Eliminar Empleado
                </button>
                <a href="list.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times me-2"></i> No, Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php";
?>
