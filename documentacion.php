<?php
// Incluir archivos de configuración y funciones
require_once "config/db.php";
require_once "includes/functions.php";

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: index.php");
    exit;
}

// Incluir header y sidebar
include "includes/header.php";
?>

<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-book me-1"></i>Documentación</li>
                </ol>
            </nav>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Documentación del Sistema</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h3>Bienvenido a la Documentación del Sistema de Gestión de Empleados</h3>
                        <p>Esta documentación le ayudará a entender cómo utilizar todas las funciones del sistema.</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="list-group sticky-top" style="top: 2rem;">
                                <a class="list-group-item active" href="#intro"><i class="fas fa-info-circle me-2"></i>Introducción</a>
                                <a class="list-group-item" href="#usuarios"><i class="fas fa-users-cog me-2"></i>Gestión de Usuarios</a>
                                <a class="list-group-item" href="#empleados"><i class="fas fa-user-tie me-2"></i>Módulo de Empleados</a>
                                <a class="list-group-item" href="#departamentos"><i class="fas fa-building me-2"></i>Departamentos</a>
                                <a class="list-group-item" href="#cargos"><i class="fas fa-id-badge me-2"></i>Cargos</a>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <section id="intro" class="mb-5">
                                <h4 class="border-bottom pb-2 mb-3">Introducción</h4>
                                <p>El Sistema de Gestión de Empleados está diseñado para facilitar la administración de la información del personal de la empresa. Permite registrar y gestionar datos de empleados, departamentos, cargos y usuarios del sistema.</p>
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-lightbulb me-2"></i>Funcionalidades principales:</h5>
                                    <ul>
                                        <li>Gestión completa de empleados (registro, actualización, consulta)</li>
                                        <li>Administración de departamentos y cargos</li>
                                        <li>Control de acceso por roles (administradores y empleados)</li>
                                        <li>Generación de reportes</li>
                                    </ul>
                                </div>
                            </section>
                            
                            <section id="usuarios" class="mb-5">
                                <h4 class="border-bottom pb-2 mb-3">Gestión de Usuarios</h4>
                                <p>Este módulo permite administrar los usuarios que tienen acceso al sistema con privilegios de administrador.</p>
                                
                                <h5 class="mt-4"><i class="fas fa-plus-circle me-2"></i>Crear Usuario</h5>
                                <p>Para crear un nuevo usuario administrador:</p>
                                <ol>
                                    <li>Acceda a <strong>Usuarios</strong> en el menú lateral</li>
                                    <li>Haga clic en <strong>Nuevo Usuario</strong></li>
                                    <li>Complete el formulario con los datos requeridos</li>
                                    <li>Haga clic en <strong>Guardar</strong></li>
                                </ol>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Las contraseñas deben tener al menos 8 caracteres.
                                </div>
                            </section>
                            
                            <section id="empleados" class="mb-5">
                                <h4 class="border-bottom pb-2 mb-3">Módulo de Empleados</h4>
                                <p>Este módulo permite gestionar toda la información relacionada con los empleados de la empresa.</p>
                                
                                <h5 class="mt-4"><i class="fas fa-user-plus me-2"></i>Registrar Empleado</h5>
                                <p>Para registrar un nuevo empleado:</p>
                                <ol>
                                    <li>Acceda a <strong>Empleados</strong> en el menú lateral</li>
                                    <li>Haga clic en <strong>Nuevo Empleado</strong></li>
                                    <li>Complete todos los campos del formulario</li>
                                    <li>Haga clic en <strong>Guardar</strong></li>
                                </ol>
                                
                                <h5 class="mt-4"><i class="fas fa-search me-2"></i>Buscar Empleados</h5>
                                <p>Puede realizar búsquedas por cédula, nombre, apellido o departamento utilizando el campo de búsqueda en la lista de empleados.</p>
                            </section>
                            
                            <section id="departamentos" class="mb-5">
                                <h4 class="border-bottom pb-2 mb-3">Departamentos</h4>
                                <p>En este módulo se administran los departamentos de la organización.</p>
                                
                                <h5 class="mt-4"><i class="fas fa-folder-plus me-2"></i>Crear Departamento</h5>
                                <p>Para crear un nuevo departamento:</p>
                                <ol>
                                    <li>Acceda a <strong>Departamentos</strong> en el menú lateral</li>
                                    <li>Haga clic en <strong>Nuevo Departamento</strong></li>
                                    <li>Ingrese el nombre del departamento y descripción</li>
                                    <li>Haga clic en <strong>Guardar</strong></li>
                                </ol>
                            </section>
                            
                            <section id="cargos" class="mb-5">
                                <h4 class="border-bottom pb-2 mb-3">Cargos</h4>
                                <p>Este módulo permite gestionar los distintos cargos disponibles en los departamentos.</p>
                                
                                <h5 class="mt-4"><i class="fas fa-plus-square me-2"></i>Crear Cargo</h5>
                                <p>Para crear un nuevo cargo:</p>
                                <ol>
                                    <li>Acceda a <strong>Cargos</strong> en el menú lateral</li>
                                    <li>Haga clic en <strong>Nuevo Cargo</strong></li>
                                    <li>Seleccione el departamento al que pertenece</li>
                                    <li>Ingrese el nombre del cargo y descripción</li>
                                    <li>Haga clic en <strong>Guardar</strong></li>
                                </ol>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Un cargo siempre debe estar asociado a un departamento existente.
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Smooth scrolling para los links de la documentación
document.addEventListener('DOMContentLoaded', function() {
    var links = document.querySelectorAll('.list-group-item');
    
    links.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Eliminar la clase active de todos los links
            links.forEach(function(l) {
                l.classList.remove('active');
            });
            
            // Añadir la clase active al link clickeado
            this.classList.add('active');
            
            var targetId = this.getAttribute('href');
            var targetElement = document.querySelector(targetId);
            
            window.scrollTo({
                top: targetElement.offsetTop - 30,
                behavior: 'smooth'
            });
        });
    });
    
    // Actualizar active link al hacer scroll
    window.addEventListener('scroll', function() {
        var scrollPosition = window.scrollY + 100;
        
        document.querySelectorAll('section').forEach(function(section) {
            if (
                section.offsetTop <= scrollPosition &&
                section.offsetTop + section.offsetHeight > scrollPosition
            ) {
                links.forEach(function(link) {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + section.getAttribute('id')) {
                        link.classList.add('active');
                    }
                });
            }
        });
    });
});
</script>

<?php
// Incluir footer
include "includes/footer.php";
?>
