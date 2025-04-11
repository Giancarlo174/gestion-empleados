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
$id = $cedula = $correo_institucional = "";
$success = $error = "";
$is_self_deletion = false;

// Verificar si existe el parámetro id en la URL
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
    
    // Verificar si el administrador está intentando eliminarse a sí mismo
    if ($_SESSION["id"] == $id) {
        $is_self_deletion = true;
    }
    
    // Preparar consulta para obtener los datos del usuario
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Vincular variables a la consulta como parámetros
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        // Ejecutar consulta
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                
                // Asignar valores
                $cedula = $row["cedula"];
                $correo_institucional = $row["correo_institucional"];
            } else {
                // URL no contiene un ID válido
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
} else {
    // URL no contiene parámetro ID
    header("location: list.php");
    exit();
}

// Procesar eliminación cuando se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Confirmar que se recibió el ID del usuario
    if (isset($_POST["id"]) && !empty($_POST["id"])) {
        $id = $_POST["id"];
        $is_self = (isset($_POST["is_self"]) && $_POST["is_self"] == "1");
        
        // Eliminar el usuario
        $sql_delete = "DELETE FROM usuarios WHERE id = ?";
        
        if ($stmt_delete = mysqli_prepare($conn, $sql_delete)) {
            // Vincular variables a la consulta como parámetros
            mysqli_stmt_bind_param($stmt_delete, "i", $id);
            
            // Ejecutar la consulta
            if (mysqli_stmt_execute($stmt_delete)) {
                if ($is_self) {
                    // Si es auto-eliminación, destruir la sesión y redirigir al login
                    session_start();
                    session_unset();
                    session_destroy();
                    
                    // Redirigir a la página de login con un mensaje
                    header("location: /ds6/index.php?account_deleted=1");
                    exit();
                } else {
                    $success = "Administrador eliminado correctamente.";
                    
                    // Redirigir después de 2 segundos
                    header("refresh:2;url=list.php?deleted=1");
                }
            } else {
                $error = "Error al eliminar el administrador: " . mysqli_error($conn);
            }
            
            // Cerrar sentencia
            mysqli_stmt_close($stmt_delete);
        } else {
            $error = "Error al preparar la consulta: " . mysqli_error($conn);
        }
    } else {
        $error = "ID de administrador no válido.";
    }
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Eliminar Administrador</h1>
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
</div>
<?php endif; ?>

<?php if (empty($success)): ?>
<div class="card">
    <div class="card-body text-center">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
            <h2 class="mt-3">¿Está seguro que desea eliminar este administrador?</h2>
            <?php if ($is_self_deletion): ?>
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>ADVERTENCIA:</strong> Está a punto de eliminar su propia cuenta de administrador.
                    <p class="mb-0 mt-2">Al confirmar, su sesión será cerrada y perderá acceso al sistema.</p>
                </div>
            <?php else: ?>
                <p class="lead text-muted">Esta acción no se puede deshacer.</p>
            <?php endif; ?>
        </div>
        
        <div class="alert alert-warning">
            <p><strong>ID:</strong> <?php echo $id; ?></p>
            <p><strong>Cédula:</strong> <?php echo htmlspecialchars($cedula); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($correo_institucional); ?></p>
            <p class="mb-0"><strong>IMPORTANTE:</strong> Esta acción no se puede deshacer.</p>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <?php if ($is_self_deletion): ?>
                <input type="hidden" name="is_self" value="1">
                <button type="submit" class="btn btn-danger btn-lg">
                    <i class="fas fa-trash"></i> Eliminar Mi Cuenta
                </button>
            <?php else: ?>
                <button type="submit" class="btn btn-danger btn-lg">
                    <i class="fas fa-trash"></i> Eliminar Administrador
                </button>
            <?php endif; ?>
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
