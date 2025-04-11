<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ya está logueado
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

// Incluir archivo de configuración
require_once "config/db.php";

// Definir variables e inicializar con valores vacíos
$username = $password = $email = "";
$username_err = $password_err = $email_err = $login_err = "";

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar nombre de usuario (cédula)
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor ingrese su cédula.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Validar contraseña
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingrese su contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validar correo institucional
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor ingrese su correo institucional.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validar credenciales
    if (empty($username_err) && empty($password_err) && empty($email_err)) {
        // Preparar declaración select
        $sql = "SELECT id, cedula, contraseña, correo_institucional FROM usuarios WHERE cedula = ? AND correo_institucional = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variables a la declaración preparada como parámetros
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_email);
            
            // Establecer parámetros
            $param_username = $username;
            $param_email = $email;
            
            // Intentar ejecutar la declaración preparada
            if (mysqli_stmt_execute($stmt)) {
                // Almacenar resultado
                mysqli_stmt_store_result($stmt);
                
                // Verificar si existe el usuario con la cédula y correo especificados
                if (mysqli_stmt_num_rows($stmt) == 1) {                    
                    // Vincular variables a los resultados
                    mysqli_stmt_bind_result($stmt, $id, $cedula, $hashed_password, $correo_institucional);
                    if (mysqli_stmt_fetch($stmt)) {
                        if ($password == $hashed_password) { // En un entorno real, usar password_verify()
                            // La contraseña es correcta, iniciar nueva sesión
                            session_start();
                            
                            // Verificar si es admin - Suponemos que administradores tienen ID 1-5
                            $admin_check = mysqli_query($conn, "SELECT id FROM usuarios WHERE cedula = '$cedula' AND id <= 5");
                            $is_admin = (mysqli_num_rows($admin_check) > 0);
                            
                            // Almacenar datos en variables de sesión
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["cedula"] = $cedula;
                            $_SESSION["correo_institucional"] = $correo_institucional;
                            $_SESSION["is_admin"] = $is_admin;
                            
                            // Redirigir al usuario a la página de bienvenida
                            header("location: dashboard.php");
                        } else {
                            // La contraseña no es válida
                            $login_err = "Credenciales incorrectas. Por favor, verifique sus datos.";
                        }
                    }
                } else {
                    // El usuario no existe o el correo no coincide
                    $login_err = "Credenciales incorrectas. Por favor, verifique sus datos.";
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtelo más tarde.";
            }

            // Cerrar declaración
            mysqli_stmt_close($stmt);
        }
    }
    
    // Cerrar conexión
    mysqli_close($conn);
}
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
            
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            }        
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Cédula</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Ingrese su cédula">
                    </div>
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Institucional</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" placeholder="Ingrese su correo institucional">
                    </div>
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Ingrese su contraseña">
                    </div>
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Iniciar Sesión</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
