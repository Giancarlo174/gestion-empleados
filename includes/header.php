<?php
// Iniciar sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
$loggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$isAdmin = $loggedIn && isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Empleados</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/ds6/assets/css/styles.css">
</head>
<body>
    <?php if ($loggedIn): ?>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="/ds6/dashboard.php">Gestión de Empleados</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="/ds6/modules/usuarios/logout.php">Cerrar sesión</a>
            </div>
        </div>
    </header>
    <?php endif; ?>
    <div class="container-fluid">
        <div class="row">
            <?php if ($loggedIn): include 'sidebar.php'; endif; ?>
            <main class="<?php echo $loggedIn ? 'col-md-9 ms-sm-auto col-lg-10 px-md-4' : 'col-12'; ?>">
