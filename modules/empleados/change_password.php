<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión (puede ser admin o empleado)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /ds6/index.php");
    exit;
}

// Incluir archivo de configuración
require_once "../../config/db.php";

// Definir variables e inicializar con valores vacíos
$current_password = $new_password = $confirm_new_password = "";
$current_password_err = $new_password_err = $confirm_new_password_err = "";
$success = $error = "";

// Obtener cédula del usuario logueado
$cedula_user = $_SESSION["cedula"];
$is_admin = isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;

// Determinar tabla y columna de contraseña según el tipo de usuario
$table = $is_admin ? "usuarios" : "empleados";
$password_column = $is_admin ? "contraseña" : "contraseña"; // Ajustar si los nombres de columna son diferentes
$id_column = $is_admin ? "cedula" : "cedula"; // Usar cédula como identificador para ambos

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar contraseña actual
    if (empty(trim($_POST["current_password"]))) {
        $current_password_err = "Por favor ingrese su contraseña actual.";
    } else {
        $current_password = trim($_POST["current_password"]);
    }

    // Validar nueva contraseña
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Por favor ingrese la nueva contraseña.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validar confirmación de nueva contraseña
    if (empty(trim($_POST["confirm_new_password"]))) {
        $confirm_new_password_err = "Por favor confirme la nueva contraseña.";
    } else {
        $confirm_new_password = trim($_POST["confirm_new_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_new_password)) {
            $confirm_new_password_err = "Las nuevas contraseñas no coinciden.";
        }
    }

    // Verificar errores antes de actualizar la contraseña
    if (empty($current_password_err) && empty($new_password_err) && empty($confirm_new_password_err)) {
        // Preparar consulta para obtener la contraseña actual hasheada
        $sql_select = "SELECT $password_column FROM $table WHERE $id_column = ?";

        if ($stmt_select = mysqli_prepare($conn, $sql_select)) {
            mysqli_stmt_bind_param($stmt_select, "s", $cedula_user);

            if (mysqli_stmt_execute($stmt_select)) {
                mysqli_stmt_store_result($stmt_select);

                if (mysqli_stmt_num_rows($stmt_select) == 1) {
                    mysqli_stmt_bind_result($stmt_select, $hashed_password_db);
                    if (mysqli_stmt_fetch($stmt_select)) {
                        // Verificar si la contraseña actual coincide
                        if (password_verify($current_password, $hashed_password_db)) {
                            // La contraseña actual es correcta, proceder a hashear y actualizar la nueva
                            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                            // Preparar consulta de actualización
                            $sql_update = "UPDATE $table SET $password_column = ? WHERE $id_column = ?";
                            if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                                mysqli_stmt_bind_param($stmt_update, "ss", $new_hashed_password, $cedula_user);

                                if (mysqli_stmt_execute($stmt_update)) {
                                    $success = "Contraseña actualizada correctamente.";
                                    // Opcional: Forzar cierre de sesión para que reingrese con nueva contraseña
                                    // session_destroy();
                                    // header("location: /ds6/index.php?password_changed=1");
                                    // exit;
                                } else {
                                    $error = "Error al actualizar la contraseña.";
                                }
                                mysqli_stmt_close($stmt_update);
                            }
                        } else {
                            // La contraseña actual no es válida
                            $current_password_err = "La contraseña actual ingresada no es correcta.";
                        }
                    }
                } else {
                    $error = "No se encontró la cuenta del usuario.";
                }
            } else {
                $error = "Error al verificar la contraseña actual.";
            }
            mysqli_stmt_close($stmt_select);
        }
    }
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Cambiar Contraseña</h1>
     <div class="btn-toolbar mb-2 mb-md-0">
         <a href="<?php echo $is_admin ? '/ds6/dashboard.php' : 'profile.php'; ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver <?php echo $is_admin ? 'al Dashboard' : 'a Mi Perfil'; ?>
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

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña Actual *</label>
                        <input type="password" name="current_password" id="current_password" class="form-control <?php echo (!empty($current_password_err)) ? 'is-invalid' : ''; ?>" required>
                        <div class="invalid-feedback"><?php echo $current_password_err; ?></div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña *</label>
                        <input type="password" name="new_password" id="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" required minlength="6">
                        <div class="invalid-feedback"><?php echo $new_password_err; ?></div>
                        <small class="form-text text-muted">Debe tener al menos 6 caracteres.</small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_new_password" class="form-label">Confirmar Nueva Contraseña *</label>
                        <input type="password" name="confirm_new_password" id="confirm_new_password" class="form-control <?php echo (!empty($confirm_new_password_err)) ? 'is-invalid' : ''; ?>" required minlength="6">
                        <div class="invalid-feedback"><?php echo $confirm_new_password_err; ?></div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php";
?>
