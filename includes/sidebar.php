<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($modulo == 'dashboard') ? 'active' : ''; ?>" aria-current="page" href="/ds6/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($modulo == 'empleados') ? 'active' : ''; ?>" href="/ds6/modules/empleados/list.php">
                    <i class="fas fa-users"></i>
                    Empleados
                </a>
            </li>
            <?php if ($_SESSION["is_admin"]): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($modulo == 'departamentos') ? 'active' : ''; ?>" href="/ds6/modules/departamentos/list.php">
                        <i class="fas fa-building"></i>
                        Departamentos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($modulo == 'cargos') ? 'active' : ''; ?>" href="/ds6/modules/cargos/list.php">
                        <i class="fas fa-briefcase"></i>
                        Cargos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($modulo == 'usuarios') ? 'active' : ''; ?>" href="/ds6/modules/usuarios/list.php">
                        <i class="fas fa-user-shield"></i>
                        Administradores
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <?php if ($isAdmin): ?>
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
                <a class="nav-link" href="/ds6/modules/usuarios/add.php">
                    <i class="fas fa-plus-circle"></i> Nuevo Administrador
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </div>
</nav>
