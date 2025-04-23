<?php
// Iniciar sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
$loggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$isAdmin = $loggedIn && isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true; 
$isEmployee = $loggedIn && isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === false; 

// Determinar el módulo actual para el sidebar activo
$current_script = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$modulo = '';

if ($loggedIn) {
    if ($current_script == 'dashboard.php') {
        $modulo = 'dashboard';
    } elseif ($current_dir == 'empleados') {
        $modulo = 'empleados';
    } elseif ($current_dir == 'departamentos') {
        $modulo = 'departamentos';
    } elseif ($current_dir == 'cargos') {
        $modulo = 'cargos';
    } elseif ($current_dir == 'usuarios') {
        $modulo = 'usuarios';
    } elseif ($current_script == 'documentacion.php') {
        $modulo = 'documentacion';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestión de Empleados">
    <title>Sistema de Gestión de Empleados</title>
    <!-- Bootstrap CSS (Local) -->
    <link href="/ds6/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos (Local) -->
    <link rel="stylesheet" href="/ds6/assets/vendor/fontawesome/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/ds6/assets/css/styles.css">
    <link rel="stylesheet" href="/ds6/assets/css/flags.css">
    <!-- Favicon -->
    <link rel="shortcut icon" href="/ds6/assets/img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php if ($loggedIn): ?>
    <header class="navbar navbar-dark sticky-top flex-md-nowrap shadow" style="background-color: var(--secondary-color);">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="<?php echo $isAdmin ? '/ds6/dashboard.php' : '/ds6/modules/empleados/profile.php'; ?>">
            <i class="fas me-2"></i>Dashboard
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="w-100 d-none d-md-block"></div>
        
        <!-- Perfil y logout -->
        <div class="dropdown text-end me-2">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle me-2"></i>
                <span class="d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?></span>
            </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item" href="<?php echo $isAdmin ? '/ds6/modules/usuarios/edit.php?id='.$_SESSION['id'] : '/ds6/modules/empleados/profile.php'; ?>">
                            <i class="fas fa-user me-2"></i> Mi perfil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/ds6/modules/usuarios/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                        </a></li>
                    </ul>
        </div>
    </header>
    <?php endif; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php if ($loggedIn): include 'sidebar.php'; endif; ?>
            <main class="<?php echo $loggedIn ? 'col-md-9 ms-sm-auto col-lg-10 px-md-4' : 'col-12'; ?>">
