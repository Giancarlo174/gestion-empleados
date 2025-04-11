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

// Verificar si existe el parámetro id en la URL
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    // Obtener el parámetro id
    $id = trim($_GET["id"]);
    
    // Preparar la consulta para obtener información del usuario
    $sql = "SELECT id, cedula, contraseña, correo_institucional FROM usuarios WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Vincular el id como parámetro
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                // Obtener los datos del usuario
                $row = mysqli_fetch_assoc($result);
                $cedula = $row["cedula"];
                $correo_institucional = $row["correo_institucional"];
            } else {
                // No se encontró el usuario
                header("location: list.php");
                exit();
            }
        } else {
            echo "Error al ejecutar la consulta: " . mysqli_error($conn);
            exit();
        }
        
        mysqli_stmt_close($stmt);
    }
} else {
    // Si no se proporcionó un id
    header("location: list.php");
    exit();
}

// Procesar eliminación cuando se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Comprobar si se recibió el ID
    if (isset($_POST["id"]) && !empty($_POST["id"])) {
        try {
            // Iniciar una transacción
            mysqli_begin_transaction($conn);
            
            // Obtener datos completos del usuario
            $sql_select = "SELECT *, NOW() AS f_eliminacion FROM usuarios WHERE id = ?";
            $stmt_select = mysqli_prepare($conn, $sql_select);
            mysqli_stmt_bind_param($stmt_select, "i", $_POST["id"]);
            mysqli_stmt_execute($stmt_select);
            $result = mysqli_stmt_get_result($stmt_select);
            
            if ($usuario = mysqli_fetch_assoc($result)) {
                // Insertar en la tabla u_eliminados
                $sql_insert = "INSERT INTO u_eliminados (id, cedula, contraseña, correo_institucional, f_eliminacion) 
                              VALUES (?, ?, ?, ?, NOW())";
                
                $stmt_insert = mysqli_prepare($conn, $sql_insert);
                mysqli_stmt_bind_param($stmt_insert, "isss", 
                    $usuario['id'], $usuario['cedula'], $usuario['contraseña'], $usuario['correo_institucional']
                );
                
                // Ejecutar la inserción
                if (!mysqli_stmt_execute($stmt_insert)) {
                    throw new Exception("Error al archivar el usuario: " . mysqli_stmt_error($stmt_insert));
                }
                mysqli_stmt_close($stmt_insert);
                
                // Comprobar si el usuario está intentando eliminarse a sí mismo
                $es_usuario_actual = ($_POST["id"] == $_SESSION["id"]);
                
                // Eliminar de la tabla usuarios
                $sql_delete = "DELETE FROM usuarios WHERE id = ?";
                $stmt_delete = mysqli_prepare($conn, $sql_delete);
                mysqli_stmt_bind_param($stmt_delete, "i", $_POST["id"]);
                
                if (!mysqli_stmt_execute($stmt_delete)) {
                    throw new Exception("Error al eliminar el usuario: " . mysqli_stmt_error($stmt_delete));
                }
                mysqli_stmt_close($stmt_delete);
                
                // Confirmar la transacción
                mysqli_commit($conn);
                
                // Si el usuario eliminó su propia cuenta, cerrar sesión
                if ($es_usuario_actual) {
                    session_unset();
                    session_destroy();
                    header("location: /ds6/index.php?cuenta_eliminada=1");
                    exit();
                } else {
                    // Redireccionar a la lista con mensaje de éxito
                    header("location: list.php?deleted=1");
                    exit();
                }
            } else {
                throw new Exception("No se encontró el usuario a eliminar.");
            }
        } catch (Exception $e) {
            // Deshacer la transacción si hubo algún problema
            mysqli_rollback($conn);
            
            $error = $e->getMessage();
        } finally {
            // Cerrar sentencias si no se cerraron antes
            if (isset($stmt_select) && $stmt_select) mysqli_stmt_close($stmt_select);
            if (isset($stmt_delete) && $stmt_delete) mysqli_stmt_close($stmt_delete);
        }
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
            <p class="lead text-muted">Esta acción no se puede deshacer.</p>
        </div>
        
        <div class="alert alert-warning">
            <p><strong>ID:</strong> <?php echo $id; ?></p>
            <p><strong>Cédula:</strong> <?php echo htmlspecialchars($cedula); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($correo_institucional); ?></p>
            <p class="mb-0"><strong>IMPORTANTE:</strong> Esta acción no se puede deshacer.</p>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <button type="submit" class="btn btn-danger btn-lg">
                <i class="fas fa-trash"></i> Eliminar Administrador
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
