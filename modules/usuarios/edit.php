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
    
    // Preparar consulta - remover la unión con la tabla empleados
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
                // Hash de la contraseña antes de almacenarla
                $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($stmt, "ssi", $correo_institucional, $hashed_password, $id);
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
    <h1 class="h2">Editar Administrador</h1>
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
        <form id="formularioEditar" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" method="post" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="cedula" class="form-label">Cédula</label>
                <input type="text" class="form-control" id="cedula" name="cedula" value="<?php echo htmlspecialchars($cedula); ?>" disabled>
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
                <div class="input-group">
                    <input type="password" class="form-control <?php echo (!empty($contrasena_err)) ? 'is-invalid' : ''; ?>" id="contrasena" name="contrasena">
                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    <?php echo $contrasena_err; ?>
                </div>
                <div class="form-text">La contraseña debe tener al menos 6 caracteres.</div>
            </div>
            
            <div class="text-center mt-4">
                <button type="button" id="btnPreGuardar" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Actualizar Administrador
                </button>
                <a href="list.php" class="btn btn-secondary btn-lg ms-2">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmación para actualizar administrador -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalConfirmacionLabel">
                    <i class="fas fa-save me-2"></i> Confirmar Actualización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-user-edit fa-4x text-primary mb-3"></i>
                    <h4>¿Está seguro que desea actualizar este administrador?</h4>
                    <p class="text-muted">Al confirmar, los datos del administrador serán actualizados en el sistema.</p>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-id-card fa-2x text-secondary me-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">Cédula</h6>
                        <div class="fw-bold"><?php echo htmlspecialchars($cedula); ?></div>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-envelope fa-2x text-secondary me-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">Correo</h6>
                        <div id="confirmEmail" class="fw-bold"><?php echo htmlspecialchars($correo_institucional); ?></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" id="btnConfirmarGuardado" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const togglePassword = document.querySelector('.toggle-password');
    const passwordField = document.querySelector('#contrasena');
    
    togglePassword.addEventListener('click', function() {
        // Toggle the type attribute
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        // Toggle the eye / eye-slash icon
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    // Modal de confirmación para actualizar administrador
    const formulario = document.getElementById('formularioEditar');
    const btnPreGuardar = document.getElementById('btnPreGuardar');
    const btnConfirmarGuardado = document.getElementById('btnConfirmarGuardado');
    const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
    
    // Actualizar el correo en el modal de confirmación cuando cambia
    const correoInput = document.getElementById('correo_institucional');
    if (correoInput) {
        correoInput.addEventListener('input', function() {
            document.getElementById('confirmEmail').textContent = this.value;
        });
    }
    
    // Manejar el clic en el botón de pre-guardar
    btnPreGuardar.addEventListener('click', function() {
        // Validar formulario antes de mostrar modal
        if (formulario.checkValidity()) {
            // Mostrar el modal
            modalConfirmacion.show();
        } else {
            // Si no es válido, activar las validaciones visuales
            formulario.classList.add('was-validated');
        }
    });
    
    // Manejar el clic en el botón de confirmación
    btnConfirmarGuardado.addEventListener('click', function() {
        // Ocultar el modal y enviar el formulario
        modalConfirmacion.hide();
        formulario.submit();
    });
});
</script>

<?php 
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php"; 
?>
