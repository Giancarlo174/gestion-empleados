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
$id = $cedula = $contrasena = $correo_institucional = "";
$contrasena_err = $correo_err = "";
$success = $error = "";

// Verificar si existe el parámetro id en la URL
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
    
    // Preparar consulta
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

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar correo institucional
    if (empty(trim($_POST["correo_institucional"]))) {
        $correo_err = "Por favor ingrese un correo institucional.";
    } else {
        $correo_institucional = trim($_POST["correo_institucional"]);
        
        // Verificar si el correo ya está en uso por otro usuario
        $sql_check = "SELECT id FROM usuarios WHERE correo_institucional = ? AND id != ?";
        if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt_check, "si", $correo_institucional, $id);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $correo_err = "Este correo institucional ya está en uso.";
            }
            mysqli_stmt_close($stmt_check);
        }
    }
    
    // Validar contraseña (solo si se ha ingresado una nueva)
    $contrasena = trim($_POST["contrasena"]);
    if (!empty($contrasena) && strlen($contrasena) < 6) {
        $contrasena_err = "La contraseña debe tener al menos 6 caracteres.";
    }
    
    // Verificar errores antes de actualizar en la base de datos
    if (empty($contrasena_err) && empty($correo_err)) {
        // Si se proporcionó una nueva contraseña
        if (!empty($contrasena)) {
            $sql = "UPDATE usuarios SET correo_institucional = ?, contraseña = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssi", $correo_institucional, $contrasena, $id);
            }
        } else {
            // Si no se cambió la contraseña
            $sql = "UPDATE usuarios SET correo_institucional = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "si", $correo_institucional, $id);
            }
        }
        
        // Intentar ejecutar la sentencia preparada
        if (isset($stmt) && mysqli_stmt_execute($stmt)) {
            $success = "Usuario actualizado exitosamente.";
            // Limpiar contraseña
            $contrasena = "";
        } else {
            $error = "Algo salió mal. Por favor, inténtelo más tarde.";
        }
        
        // Cerrar sentencia
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
    }
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Usuario</h1>
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

<div class="card">
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" method="post" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="cedula" class="form-label">Empleado</label>
                <input type="text" class="form-control" id="cedula" name="cedula" value="<?php echo htmlspecialchars($cedula . ' - ' . $nombre_empleado); ?>" disabled>
            </div>
            
            <div class="mb-3">
                <label for="correo_institucional" class="form-label">Correo Institucional *</label>
                <input type="email" class="form-control <?php echo (!empty($correo_err)) ? 'is-invalid' : ''; ?>" id="correo_institucional" name="correo_institucional" value="<?php echo $correo_institucional; ?>" required>
                <div class="invalid-feedback">
                    <?php echo $correo_err; ?>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="contrasena" class="form-label">Nueva Contraseña (dejar en blanco para mantener la actual)</label>
                <input type="password" class="form-control <?php echo (!empty($contrasena_err)) ? 'is-invalid' : ''; ?>" id="contrasena" name="contrasena">
                <div class="invalid-feedback">
                    <?php echo $contrasena_err; ?>
                </div>
                <div class="form-text">La contraseña debe tener al menos 6 caracteres.</div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Actualizar Usuario
                </button>
                <a href="list.php" class="btn btn-secondary btn-lg ms-2">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php"; 
?>
