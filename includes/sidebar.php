<?php
// Asegurarse de que $isAdmin está definido (debería estarlo desde header.php)
if (!isset($isAdmin)) {
    $isAdmin = isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
}
if (!isset($isEmployee)) {
    $isEmployee = isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === false;
}
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <?php if ($isAdmin): ?>
                <!-- Menú Administrador -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($modulo == 'dashboard') ? 'active' : ''; ?>" aria-current="page" href="/ds6/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($modulo == 'empleados' && $current_script != 'profile.php' && $current_script != 'edit.php' && $current_script != 'change_password.php') ? 'active' : ''; ?>" href="/ds6/modules/empleados/list.php">
                        <i class="fas fa-users"></i> Empleados
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($modulo == 'departamentos') ? 'active' : ''; ?>" href="/ds6/modules/departamentos/list.php">
                        <i class="fas fa-building"></i> Departamentos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($modulo == 'cargos') ? 'active' : ''; ?>" href="/ds6/modules/cargos/list.php">
                        <i class="fas fa-briefcase"></i> Cargos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($modulo == 'usuarios') ? 'active' : ''; ?>" href="/ds6/modules/usuarios/list.php">
                        <i class="fas fa-user-shield"></i> Administradores
                    </a>
                </li>
            <?php elseif ($isEmployee): ?>
                <!-- Menú Empleado -->
                 <li class="nav-item">
                    <a class="nav-link <?php echo ($current_script == 'profile.php') ? 'active' : ''; ?>" href="/ds6/modules/empleados/profile.php">
                        <i class="fas fa-user"></i> Mi Perfil
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link <?php echo ($current_script == 'edit.php') ? 'active' : ''; ?>" href="/ds6/modules/empleados/edit.php?cedula=<?php echo urlencode($_SESSION['cedula']); ?>">
                        <i class="fas fa-edit"></i> Editar Información
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link <?php echo ($current_script == 'change_password.php') ? 'active' : ''; ?>" href="/ds6/modules/empleados/change_password.php">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <?php if ($isAdmin): // Mantener sección de administración solo para admins ?>
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Administración</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="/ds6/modules/empleados/add.php">
                    <i class="fas fa-user-plus"></i> Nuevo Empleado
                </a>
            </li>
             <li class="nav-item">
                <a class="nav-link" href="/ds6/modules/departamentos/add.php">
                    <i class="fas fa-plus-circle"></i> Nuevo Departamento
                </a>
            </li>
             <li class="nav-item">
                <a class="nav-link" href="/ds6/modules/cargos/add.php">
                    <i class="fas fa-plus-circle"></i> Nuevo Cargo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/ds6/modules/usuarios/add.php">
                    <i class="fas fa-user-cog"></i> Nuevo Administrador
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </div>
</nav>
