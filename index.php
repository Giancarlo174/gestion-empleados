<?php
// Iniciar sesión
session_start();

// Si el usuario ya ha iniciado sesión, redirigir a su panel correspondiente
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
        header("location: dashboard.php");
    } else {
        header("location: modules/empleados/profile.php"); // Redirigir empleado a su perfil
    }
    exit;
}

// Incluir archivo de configuración
require_once "config/db.php";

// Definir variables e inicializar con valores vacíos
$correo = $contraseña = "";
$correo_err = $contraseña_err = $login_err = "";

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar correo electrónico
    if (empty(trim($_POST["correo"]))) {
        $correo_err = "Por favor ingrese su correo electrónico.";
    } else {
        $correo = trim($_POST["correo"]);
    }

    // Validar contraseña
    if (empty(trim($_POST["contraseña"]))) {
        $contraseña_err = "Por favor ingrese su contraseña.";
    } else {
        $contraseña = trim($_POST["contraseña"]);
    }

    // Validar credenciales si no hay errores
    if (empty($correo_err) && empty($contraseña_err)) {
        $login_successful = false;

        // 1. Intentar iniciar sesión como Administrador (tabla usuarios)
        $sql_admin = "SELECT id, cedula, correo_institucional, contraseña FROM usuarios WHERE correo_institucional = ?";
        if ($stmt_admin = mysqli_prepare($conn, $sql_admin)) {
            mysqli_stmt_bind_param($stmt_admin, "s", $correo);
            if (mysqli_stmt_execute($stmt_admin)) {
                mysqli_stmt_store_result($stmt_admin);
                if (mysqli_stmt_num_rows($stmt_admin) == 1) {
                    mysqli_stmt_bind_result($stmt_admin, $id, $cedula_admin, $correo_institucional_db, $hashed_password_admin);
                    if (mysqli_stmt_fetch($stmt_admin)) {
                        // Verificar contraseña (asumiendo que la contraseña en 'usuarios' también está hasheada)
                        // Si no está hasheada, usa: if ($contraseña === $hashed_password_admin)
                        if (password_verify($contraseña, $hashed_password_admin)) {
                            // Contraseña correcta, iniciar sesión como admin
                            session_start(); // Asegurar que la sesión esté iniciada
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id; // ID de la tabla usuarios
                            $_SESSION["username"] = $correo_institucional_db; // Correo del admin
                            $_SESSION["cedula"] = $cedula_admin; // Cedula del admin
                            $_SESSION["is_admin"] = true; // Marcar como administrador
                            $login_successful = true;
                            header("location: dashboard.php");
                            exit;
                        }
                    }
                }
            } else {
                $login_err = "Oops! Algo salió mal (Admin Check). Por favor intente de nuevo más tarde.";
            }
            mysqli_stmt_close($stmt_admin);
        }

        // 2. Si no se inició sesión como admin, intentar como Empleado (tabla empleados)
        if (!$login_successful && empty($login_err)) {
            $sql_empleado = "SELECT cedula, nombre1, apellido1, contraseña FROM empleados WHERE correo = ? AND estado = 1";
            if ($stmt_empleado = mysqli_prepare($conn, $sql_empleado)) {
                mysqli_stmt_bind_param($stmt_empleado, "s", $correo);
                if (mysqli_stmt_execute($stmt_empleado)) {
                    mysqli_stmt_store_result($stmt_empleado);
                    if (mysqli_stmt_num_rows($stmt_empleado) == 1) {
                        mysqli_stmt_bind_result($stmt_empleado, $cedula_empleado, $nombre1_empleado, $apellido1_empleado, $hashed_password_empleado);
                        if (mysqli_stmt_fetch($stmt_empleado)) {
                            if (password_verify($contraseña, $hashed_password_empleado)) {
                                // Contraseña correcta, iniciar sesión como empleado
                                session_start(); // Asegurar que la sesión esté iniciada
                                $_SESSION["loggedin"] = true;
                                $_SESSION["cedula"] = $cedula_empleado; // Cedula del empleado
                                $_SESSION["username"] = $nombre1_empleado . " " . $apellido1_empleado; // Nombre del empleado
                                $_SESSION["is_admin"] = false; // Marcar como NO administrador
                                $login_successful = true;
                                header("location: modules/empleados/profile.php"); // Redirigir al perfil del empleado
                                exit;
                            }
                        }
                    }
                } else {
                    $login_err = "Oops! Algo salió mal (Employee Check). Por favor intente de nuevo más tarde.";
                }
                mysqli_stmt_close($stmt_empleado);
            }
        }

        // 3. Si no se pudo iniciar sesión como ninguno
        if (!$login_successful && empty($login_err)) {
            $login_err = "Correo electrónico o contraseña inválidos, o la cuenta está inactiva.";
        }
    }

    // Cerrar conexión si aún está abierta
    if ($conn) {
        mysqli_close($conn);
    }
}

// Incluir header (sin sidebar para login)
include "includes/header.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión de Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-light">
    <div class="login-container">
        <div class="login-form">
            <div class="login-logo">
                <h2>Sistema de Gestión de Empleados</h2>
                <p>Inicie sesión para continuar</p>
            </div>
            
            <?php if (isset($_GET['account_deleted']) && $_GET['account_deleted'] == 1): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Cuenta eliminada:</strong> Su cuenta de administrador ha sido eliminada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            }        
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="correo" class="form-control <?php echo (!empty($correo_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $correo; ?>" placeholder="Ingrese su correo electrónico">
                    </div>
                    <span class="invalid-feedback"><?php echo $correo_err; ?></span>
                </div>
                
                <div class="mb-3">
                    <label for="contraseña" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="contraseña" id="contraseña" class="form-control <?php echo (!empty($contraseña_err)) ? 'is-invalid' : ''; ?>" placeholder="Ingrese su contraseña">
                        <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="invalid-feedback"><?php echo $contraseña_err; ?></span>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Iniciar Sesión</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password visibility toggle
        const togglePassword = document.querySelector('.toggle-password');
        const password = document.querySelector('#contraseña');

        togglePassword.addEventListener('click', function() {
            // Toggle the password field type
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle the icon
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
    </script>
</body>
</html>

<?php
// Incluir footer
include "includes/footer.php";
?>
