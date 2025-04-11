<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="/ds6/dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/ds6/modules/empleados/list.php">
                    <i class="fas fa-users"></i> Empleados
                </a>
            </li>
            <?php if ($isAdmin): ?>
            <li class="nav-item">
                <a class="nav-link" href="/ds6/modules/usuarios/list.php">
                    <i class="fas fa-user-shield"></i> Usuarios
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
                    <i class="fas fa-plus-circle"></i> Nuevo Usuario
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </div>
</nav>
