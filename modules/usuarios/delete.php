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
$id = $cedula = $correo_institucional = $nombre_empleado = "";
$success = $error = "";

// Verificar si existe el parámetro id en la URL
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
    
    // Preparar consulta para obtener los datos del usuario
    $sql = "SELECT u.*, e.nombre1, e.apellido1 FROM usuarios u LEFT JOIN empleados e ON u.cedula = e.cedula WHERE u.id = ?";
    
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
                $nombre_empleado = !empty($row["nombre1"]) ? $row["nombre1"] . " " . $row["apellido1"] : "No vinculado a empleado";
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
        
        // Iniciar una transacción
        mysqli_begin_transaction($conn);
        
        try {
            // Obtener datos del usuario
            $sql_usuario = "SELECT * FROM usuarios WHERE id = ?";
            $stmt_usuario = mysqli_prepare($conn, $sql_usuario);
            mysqli_stmt_bind_param($stmt_usuario, "i", $id);
            mysqli_stmt_execute($stmt_usuario);
            $result_usuario = mysqli_stmt_get_result($stmt_usuario);
            $usuario = mysqli_fetch_assoc($result_usuario);
            
            // Insertar en la tabla de usuarios eliminados
            $sql_insert = "INSERT INTO u_eliminados (id, cedula, contraseña, correo_institucional, f_eliminacion) VALUES (?, ?, ?, ?, CURRENT_DATE())";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "isss", $usuario['id'], $usuario['cedula'], $usuario['contraseña'], $usuario['correo_institucional']);
            mysqli_stmt_execute($stmt_insert);
            
            // Eliminar el usuario
            $sql_delete = "DELETE FROM usuarios WHERE id = ?";
            $stmt_delete = mysqli_prepare($conn, $sql_delete);
            mysqli_stmt_bind_param($stmt_delete, "i", $id);
            mysqli_stmt_execute($stmt_delete);
            
            // Confirmar la transacción
            mysqli_commit($conn);
            
            $success = "Usuario eliminado correctamente.";
            
            // Redirigir después de 2 segundos
            header("refresh:2;url=list.php");
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            mysqli_rollback($conn);
            $error = "Error al eliminar el usuario: " . $e->getMessage();
        }
    }
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Eliminar Usuario</h1>
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
        <h4 class="card-title mb-4">¿Está seguro que desea eliminar este usuario?</h4>
        
        <div class="alert alert-warning">
            <p><strong>ID:</strong> <?php echo $id; ?></p>
            <p><strong>Cédula:</strong> <?php echo htmlspecialchars($cedula); ?></p>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre_empleado); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($correo_institucional); ?></p>
            <p class="mb-0"><strong>IMPORTANTE:</strong> Esta acción no se puede deshacer.</p>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <button type="submit" class="btn btn-danger btn-lg">
                <i class="fas fa-trash"></i> Eliminar Usuario
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
