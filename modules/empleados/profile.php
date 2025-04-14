<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión y NO es administrador
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true)) {
    // Si es admin o no está logueado, redirigir (admin al dashboard, no logueado al index)
    if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
        header("location: /ds6/dashboard.php");
    } else {
        header("location: /ds6/index.php");
    }
    exit;
}

// Incluir archivo de configuración y funciones
require_once "../../config/db.php";
require_once "load_nacionalidades.php"; // Para mostrar nombre de nacionalidad

// Obtener cédula del empleado logueado
$cedula_empleado = $_SESSION["cedula"];
$empleado = null;
$error = "";

// Consultar datos del empleado
$sql = "SELECT e.*,
               p.nombre_provincia,
               d.nombre_distrito,
               c.nombre_corregimiento,
               n.pais as nombre_nacionalidad,
               dep.nombre as nombre_departamento,
               car.nombre as nombre_cargo
        FROM empleados e
        LEFT JOIN provincia p ON e.provincia = p.codigo_provincia
        LEFT JOIN distrito d ON e.distrito = d.codigo_distrito AND e.provincia = d.codigo_provincia -- Corrected: d.codigo_provincia
        LEFT JOIN corregimiento c ON e.corregimiento = c.codigo_corregimiento AND e.distrito = c.codigo_distrito AND e.provincia = c.codigo_provincia -- Corrected: c.codigo_distrito and c.codigo_provincia
        LEFT JOIN nacionalidad n ON e.nacionalidad = n.codigo
        LEFT JOIN departamento dep ON e.departamento = dep.codigo
        LEFT JOIN cargo car ON e.cargo = car.codigo AND e.departamento = car.dep_codigo
        WHERE e.cedula = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $cedula_empleado);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 1) {
            $empleado = mysqli_fetch_assoc($result);
        } else {
            $error = "No se encontró la información del empleado.";
        }
    } else {
        $error = "Error al ejecutar la consulta: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    $error = "Error al preparar la consulta: " . mysqli_error($conn);
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Mi Perfil</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="edit.php?cedula=<?php echo urlencode($cedula_empleado); ?>" class="btn btn-sm btn-outline-primary me-2">
            <i class="fas fa-edit"></i> Editar Información
        </a>
        <a href="change_password.php" class="btn btn-sm btn-outline-warning">
            <i class="fas fa-key"></i> Cambiar Contraseña
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php elseif ($empleado): ?>
    <div class="card">
        <div class="card-header">
            Información Personal
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Cédula:</strong> <?php echo htmlspecialchars($empleado['cedula']); ?>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Nombre Completo:</strong>
                    <?php
                    echo htmlspecialchars($empleado['nombre1'] . ' ' . $empleado['nombre2'] . ' ' . $empleado['apellido1'] . ' ' . $empleado['apellido2']);
                    if ($empleado['usa_ac'] && !empty($empleado['apellidoc'])) {
                        echo ' (de ' . htmlspecialchars($empleado['apellidoc']) . ')';
                    }
                    ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Género:</strong> <?php echo ($empleado['genero'] == 1) ? 'Masculino' : 'Femenino'; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Estado Civil:</strong>
                    <?php
                    switch ($empleado['estado_civil']) {
                        case 0: echo 'Soltero/a'; break;
                        case 1: echo 'Casado/a'; break;
                        case 2: echo 'Viudo/a'; break;
                        case 3: echo 'Divorciado/a'; break;
                        default: echo 'No especificado';
                    }
                    ?>
                </div>
                 <div class="col-md-4 mb-3">
                    <strong>Tipo de Sangre:</strong> <?php echo htmlspecialchars($empleado['tipo_sangre']); ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Fecha de Nacimiento:</strong> <?php echo date("d/m/Y", strtotime($empleado['f_nacimiento'])); ?>
                </div>
                 <div class="col-md-4 mb-3">
                    <strong>Nacionalidad:</strong> <?php echo htmlspecialchars($empleado['nombre_nacionalidad'] ?? $empleado['nacionalidad']); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            Información de Contacto
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Celular:</strong> <?php echo htmlspecialchars($empleado['celular']); ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Teléfono Fijo:</strong> <?php echo htmlspecialchars($empleado['telefono'] ?? 'N/A'); ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($empleado['correo']); ?>
                </div>
            </div>
        </div>
    </div>

     <div class="card mt-4">
        <div class="card-header">
            Información de Ubicación
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Provincia:</strong> <?php echo htmlspecialchars($empleado['nombre_provincia'] ?? $empleado['provincia']); ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Distrito:</strong> <?php echo htmlspecialchars($empleado['nombre_distrito'] ?? $empleado['distrito']); ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Corregimiento:</strong> <?php echo htmlspecialchars($empleado['nombre_corregimiento'] ?? $empleado['corregimiento']); ?>
                </div>
                 <div class="col-md-4 mb-3">
                    <strong>Calle:</strong> <?php echo htmlspecialchars($empleado['calle'] ?? 'N/A'); ?>
                </div>
                 <div class="col-md-4 mb-3">
                    <strong>Casa/Apto:</strong> <?php echo htmlspecialchars($empleado['casa'] ?? 'N/A'); ?>
                </div>
                 <div class="col-md-4 mb-3">
                    <strong>Comunidad/Urbanización:</strong> <?php echo htmlspecialchars($empleado['comunidad'] ?? 'N/A'); ?>
                </div>
            </div>
        </div>
    </div>

     <div class="card mt-4">
        <div class="card-header">
            Información Laboral
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Fecha de Contratación:</strong> <?php echo date("d/m/Y", strtotime($empleado['f_contra'])); ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Departamento:</strong> <?php echo htmlspecialchars($empleado['nombre_departamento'] ?? $empleado['departamento']); ?>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Cargo:</strong> <?php echo htmlspecialchars($empleado['nombre_cargo'] ?? $empleado['cargo']); ?>
                </div>
                 <div class="col-md-4 mb-3">
                    <strong>Estado:</strong> <?php echo ($empleado['estado'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="alert alert-warning">No se pudo cargar la información del perfil.</div>
<?php endif; ?>

<?php
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php";
?>
