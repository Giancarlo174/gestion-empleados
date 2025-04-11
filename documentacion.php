<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

// Incluir header
include "includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Documentación del Sistema</h1>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Manual de Usuario</h4>
            </div>
            <div class="card-body">
                <h5>1. Introducción</h5>
                <p>
                    Este sistema de gestión de empleados permite administrar la información del personal de la empresa
                    de manera eficiente y segura. El sistema cuenta con dos tipos de usuarios: Administradores y Empleados regulares.
                </p>
                
                <h5>2. Acceso al Sistema</h5>
                <p>
                    Para acceder al sistema, el usuario debe ingresar su cédula y contraseña en la pantalla de inicio de sesión.
                    Si las credenciales son correctas, el sistema redirigirá al usuario al dashboard correspondiente según su rol.
                </p>
                
                <h5>3. Panel de Administrador</h5>
                <p>
                    Los administradores tienen acceso completo al sistema, incluyendo:
                </p>
                <ul>
                    <li>Ver estadísticas generales en el dashboard</li>
                    <li>Gestionar empleados (agregar, editar, eliminar)</li>
                    <li>Gestionar usuarios del sistema</li>
                    <li>Ver toda la información del personal</li>
                </ul>
                
                <h5>4. Panel de Empleado</h5>
                <p>
                    Los usuarios con rol de empleado solo pueden:
                </p>
                <ul>
                    <li>Ver su propia información personal</li>
                    <li>Actualizar ciertos datos como teléfono y correo (si el administrador lo permite)</li>
                </ul>
                
                <h5>5. Gestión de Empleados</h5>
                <p>
                    La sección de empleados permite realizar las siguientes acciones:
                </p>
                <ul>
                    <li>
                        <strong>Listar Empleados:</strong> Muestra todos los empleados registrados con opciones para filtrar por estado o buscar por nombre/cédula.
                    </li>
                    <li>
                        <strong>Agregar Empleado:</strong> Permite registrar un nuevo empleado con todos sus datos personales, de contacto y laborales.
                    </li>
                    <li>
                        <strong>Editar Empleado:</strong> Permite actualizar la información de un empleado existente.
                    </li>
                    <li>
                        <strong>Eliminar Empleado:</strong> Permite dar de baja a un empleado. Esta acción mueve los datos a una tabla de respaldo.
                    </li>
                </ul>
                
                <h5>6. Gestión de Usuarios</h5>
                <p>
                    La sección de usuarios permite administrar las cuentas de acceso al sistema:
                </p>
                <ul>
                    <li>
                        <strong>Listar Usuarios:</strong> Muestra todos los usuarios registrados en el sistema.
                    </li>
                    <li>
                        <strong>Agregar Usuario:</strong> Permite crear un nuevo usuario asociado a un empleado existente.
                    </li>
                    <li>
                        <strong>Editar Administrador:</strong> Permite actualizar la información de acceso de un usuario.
                    </li>
                    <li>
                        <strong>Eliminar Usuario:</strong> Permite eliminar una cuenta de usuario sin afectar los datos del empleado asociado.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Documentación Técnica</h4>
            </div>
            <div class="card-body">
                <h5>1. Arquitectura del Sistema</h5>
                <p>
                    El sistema sigue una arquitectura MVC (Modelo-Vista-Controlador) simplificada:
                </p>
                <ul>
                    <li>
                        <strong>Modelo:</strong> Interacción con la base de datos MySQL a través de consultas SQL y la API mysqli de PHP.
                    </li>
                    <li>
                        <strong>Vista:</strong> Archivos PHP con HTML que definen la interfaz de usuario, apoyados en Bootstrap 5 para el diseño responsive.
                    </li>
                    <li>
                        <strong>Controlador:</strong> Lógica de la aplicación incluida en los scripts PHP que procesan formularios y realizan operaciones CRUD.
                    </li>
                </ul>
                
                <h5>2. Tecnologías Utilizadas</h5>
                <ul>
                    <li><strong>Backend:</strong> PHP 7.4+</li>
                    <li><strong>Base de Datos:</strong> MySQL 5.7+</li>
                    <li><strong>Frontend:</strong> HTML5, CSS3, JavaScript</li>
                    <li><strong>Framework CSS:</strong> Bootstrap 5</li>
                    <li><strong>Iconos:</strong> Font Awesome 6</li>
                    <li><strong>JavaScript:</strong> jQuery para algunas funcionalidades dinámicas</li>
                </ul>
                
                <h5>3. Estructura de Directorios</h5>
                <pre>
/ds6
├── index.php              # Página de inicio/login
├── dashboard.php          # Panel principal
├── documentacion.php      # Documentación del sistema
├── /assets                # Recursos estáticos
│   ├── /css               # Estilos CSS
│   └── /js                # Scripts JavaScript
├── /config                # Configuraciones
│   └── db.php             # Conexión a base de datos
├── /includes              # Componentes reutilizables
│   ├── header.php         # Cabecera del sitio
│   ├── footer.php         # Pie de página
│   └── sidebar.php        # Menú lateral
└── /modules               # Módulos funcionales
    ├── /empleados         # Gestión de empleados
    │   ├── add.php        # Agregar empleado
    │   ├── edit.php       # Editar empleado
    │   ├── delete.php     # Eliminar empleado
    │   ├── list.php       # Listar empleados
    │   ├── view.php       # Ver detalles de un empleado
    │   └── ...            # Otros archivos auxiliares
    └── /usuarios          # Gestión de usuarios
        ├── add.php        # Agregar usuario
        ├── edit.php       # Editar usuario
        ├── delete.php     # Eliminar usuario
        ├── list.php       # Listar usuarios
        ├── logout.php     # Cerrar sesión
        └── ...            # Otros archivos auxiliares
</pre>
                
                <h5>4. Base de Datos</h5>
                <p>
                    El sistema utiliza una base de datos MySQL llamada "ds6" con tablas para:
                </p>
                <ul>
                    <li>Empleados y sus datos personales/laborales</li>
                    <li>Usuarios del sistema</li>
                    <li>Catálogos de provincias, distritos, corregimientos</li>
                    <li>Catálogos de departamentos y cargos</li>
                    <li>Historiales de empleados y usuarios eliminados</li>
                </ul>
                
                <h5>5. Seguridad</h5>
                <p>
                    El sistema implementa varias medidas de seguridad:
                </p>
                <ul>
                    <li>Autenticación de usuarios basada en sesiones PHP</li>
                    <li>Control de acceso basado en roles (Administrador/Empleado)</li>
                    <li>Protección contra inyección SQL mediante consultas preparadas</li>
                    <li>Validación de datos en cliente y servidor</li>
                    <li>Sanitización de salida para prevenir XSS</li>
                </ul>
                
                <h5>6. Requisitos del Servidor</h5>
                <ul>
                    <li>Servidor web con soporte PHP 7.4 o superior</li>
                    <li>MySQL 5.7 o superior</li>
                    <li>Extensión mysqli activada en PHP</li>
                    <li>Mínimo 256MB de memoria asignada a PHP</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php 
// Incluir footer
include "includes/footer.php"; 
?>
