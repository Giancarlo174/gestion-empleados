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
$cedula = $contrasena = $correo_institucional = "";
$cedula_err = $contrasena_err = $correo_err = "";
$success = $error = "";

// Cargar lista de empleados que no tienen usuario
$sql_empleados = "SELECT e.cedula, e.nombre1, e.apellido1 
                 FROM empleados e 
                 LEFT JOIN usuarios u ON e.cedula = u.cedula 
                 WHERE u.id IS NULL 
                 ORDER BY e.nombre1, e.apellido1";
$result_empleados = mysqli_query($conn, $sql_empleados);

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar cédula
    if (empty(trim($_POST["cedula"]))) {
        $cedula_err = "Por favor seleccione un empleado.";
    } else {
        $cedula = trim($_POST["cedula"]);
        
        // Verificar si la cédula ya está registrada como usuario
        $sql_check = "SELECT id FROM usuarios WHERE cedula = ?";
        if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt_check, "s", $cedula);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $cedula_err = "Este empleado ya tiene un usuario registrado.";
            }
            mysqli_stmt_close($stmt_check);
        }
    }
    
    // Validar contraseña
    if (empty(trim($_POST["contrasena"]))) {
        $contrasena_err = "Por favor ingrese una contraseña.";
    } elseif (strlen(trim($_POST["contrasena"])) < 6) {
        $contrasena_err = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $contrasena = trim($_POST["contrasena"]);
    }
    
    // Validar correo institucional
    if (empty(trim($_POST["correo_institucional"]))) {
        $correo_err = "Por favor ingrese un correo institucional.";
    } else {
        $correo_institucional = trim($_POST["correo_institucional"]);
        
        // Verificar si el correo ya está en uso
        $sql_check = "SELECT id FROM usuarios WHERE correo_institucional = ?";
        if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt_check, "s", $correo_institucional);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $correo_err = "Este correo institucional ya está en uso.";
            }
            mysqli_stmt_close($stmt_check);
        }
    }
    
    // Verificar errores antes de insertar en la base de datos
    if (empty($cedula_err) && empty($contrasena_err) && empty($correo_err)) {
        // Preparar la sentencia de inserción
        $sql = "INSERT INTO usuarios (cedula, contraseña, correo_institucional) VALUES (?, ?, ?)";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular las variables a la sentencia preparada
            mysqli_stmt_bind_param($stmt, "sss", $param_cedula, $param_contrasena, $param_correo);
            
            // Establecer parámetros
            $param_cedula = $cedula;
            $param_contrasena = $contrasena; // En un sistema real, usar password_hash()
            $param_correo = $correo_institucional;
            
            // Intentar ejecutar la sentencia preparada
            if (mysqli_stmt_execute($stmt)) {
                $success = "Usuario creado exitosamente.";
                // Limpiar variables
                $cedula = $contrasena = $correo_institucional = "";
                // Recargar la lista de empleados disponibles
                $result_empleados = mysqli_query($conn, $sql_empleados);
            } else {
                $error = "Algo salió mal. Por favor, inténtelo más tarde.";
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
    <h1 class="h2">Agregar Nuevo Usuario</h1>
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
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="cedula" class="form-label">Empleado *</label>
                <select class="form-select <?php echo (!empty($cedula_err)) ? 'is-invalid' : ''; ?>" id="cedula" name="cedula" required>
                    <option value="" selected disabled>Seleccione un empleado...</option>
                    <?php while ($row = mysqli_fetch_assoc($result_empleados)): ?>
                    <option value="<?php echo $row['cedula']; ?>" <?php if ($cedula == $row['cedula']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($row['cedula'] . ' - ' . $row['nombre1'] . ' ' . $row['apellido1']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                <div class="invalid-feedback">
                    <?php echo $cedula_err; ?>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="correo_institucional" class="form-label">Correo Institucional *</label>
                <input type="email" class="form-control <?php echo (!empty($correo_err)) ? 'is-invalid' : ''; ?>" id="correo_institucional" name="correo_institucional" value="<?php echo $correo_institucional; ?>" required>
                <div class="invalid-feedback">
                    <?php echo $correo_err; ?>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña *</label>
                <input type="password" class="form-control <?php echo (!empty($contrasena_err)) ? 'is-invalid' : ''; ?>" id="contrasena" name="contrasena" required>
                <div class="invalid-feedback">
                    <?php echo $contrasena_err; ?>
                </div>
                <div class="form-text">La contraseña debe tener al menos 6 caracteres.</div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Usuario
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
