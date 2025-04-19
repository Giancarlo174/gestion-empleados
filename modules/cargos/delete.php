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

// Definir variables e inicializar con valores vacíos
$codigo = $nombre = $departamento_nombre = "";
$success = $error = "";

// Verificar si existe el parámetro codigo en la URL
if (isset($_GET["codigo"]) && !empty(trim($_GET["codigo"]))) {
    // Obtener código del cargo
    $codigo = trim($_GET["codigo"]);
    
    // Verificar dependencias antes de intentar eliminar
    $sql_check_empleados = "SELECT COUNT(*) as total FROM empleados WHERE cargo = ?";
    if ($stmt_check = mysqli_prepare($conn, $sql_check_empleados)) {
        mysqli_stmt_bind_param($stmt_check, "s", $codigo);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $row_check = mysqli_fetch_assoc($result_check);
        
        if ($row_check['total'] > 0) {
            $error = "No se puede eliminar este cargo porque tiene empleados asociados.";
        } else {
            // Preparar consulta para obtener los datos del cargo
            $sql = "SELECT c.*, d.nombre as departamento_nombre 
                   FROM cargo c 
                   LEFT JOIN departamento d ON c.dep_codigo = d.codigo 
                   WHERE c.codigo = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Vincular variables a la consulta como parámetros
                mysqli_stmt_bind_param($stmt, "s", $codigo);
                
                // Ejecutar consulta
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if (mysqli_num_rows($result) == 1) {
                        $row = mysqli_fetch_assoc($result);
                        $nombre = $row["nombre"];
                        $departamento_nombre = $row["departamento_nombre"];
                    } else {
                        // URL no contiene un código válido
                        header("location: list.php");
                        exit();
                    }
                } else {
                    echo "Error al ejecutar la consulta: " . mysqli_error($conn);
                    exit();
                }
                
                // Cerrar sentencia
                mysqli_stmt_close($stmt);
            }
        }
        
        mysqli_stmt_close($stmt_check);
    }
} else {
    // URL no contiene parámetro codigo
    header("location: list.php");
    exit();
}

// Procesar eliminación cuando se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {
    // Confirmar que se recibió el código del cargo
    if (isset($_POST["codigo"]) && !empty($_POST["codigo"])) {
        // Preparar consulta de eliminación
        $sql = "DELETE FROM cargo WHERE codigo = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variables a la consulta como parámetros
            mysqli_stmt_bind_param($stmt, "s", $param_codigo);
            
            // Asignar parámetros
            $param_codigo = $_POST["codigo"];
            
            // Ejecutar consulta
            if (mysqli_stmt_execute($stmt)) {
                $success = "Cargo eliminado correctamente.";
                
                // Redirigir después de 2 segundos
                header("refresh:2;url=list.php");
            } else {
                $error = "Error al eliminar el cargo: " . mysqli_error($conn);
            }
            
            // Cerrar sentencia
            mysqli_stmt_close($stmt);
        }
    }
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Eliminar Cargo</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="list.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver a la Lista
        </a>
    </div>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <div class="mt-3">
        <a href="list.php" class="btn btn-primary">Volver a la lista de cargos</a>
    </div>
</div>
<?php endif; ?>

<?php if (empty($success) && empty($error)): ?>
<div class="card">
    <div class="card-body text-center">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
            <h2 class="mt-3">¿Está seguro que desea eliminar este cargo?</h2>
            <p class="lead text-muted">Esta acción no se puede deshacer.</p>
        </div>
        
        <div class="alert alert-warning">
            <p><strong>Código:</strong> <?php echo htmlspecialchars($codigo); ?></p>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></p>
            <p><strong>Departamento:</strong> <?php echo htmlspecialchars($departamento_nombre); ?></p>
            <p class="mb-0"><strong>IMPORTANTE:</strong> Al eliminar este cargo se perderá permanentemente.</p>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?codigo=' . $codigo); ?>" method="post">
            <input type="hidden" name="codigo" value="<?php echo $codigo; ?>">
            <button type="submit" class="btn btn-danger btn-lg">
                <i class="fas fa-trash"></i> Eliminar Cargo
            </button>
            <a href="list.php" class="btn btn-secondary btn-lg ms-2">
                <i class="fas fa-times"></i> Cancelar
            </a>
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
