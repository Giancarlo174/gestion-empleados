<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /ds6/index.php");
    exit;
}

// Incluir archivo de configuración
require_once "../../config/db.php";

// Definir variables
$cedula = "";
$employee_data = [];
$error = "";

// Verificar si existe el parámetro cedula en la URL
if (isset($_GET["cedula"]) && !empty(trim($_GET["cedula"]))) {
    // Obtener el parámetro de cedula
    $cedula = trim($_GET["cedula"]);
    
    // Verificar si el usuario no es administrador
    if (!$_SESSION["is_admin"] && $_SESSION["cedula"] !== $cedula) {
        header("location: /ds6/dashboard.php");
        exit;
    }
    
    // Preparar la consulta para obtener los datos del empleado
    $sql = "SELECT e.*, 
            n.pais AS nacionalidad_nombre,
            p.nombre_provincia AS provincia_nombre,
            d.nombre_distrito AS distrito_nombre,
            c.nombre_corregimiento AS corregimiento_nombre,
            dep.nombre AS departamento_nombre,
            car.nombre AS cargo_nombre
            FROM empleados e
            LEFT JOIN nacionalidad n ON e.nacionalidad = n.codigo
            LEFT JOIN provincia p ON e.provincia = p.codigo_provincia
            LEFT JOIN distrito d ON e.provincia = d.codigo_provincia AND e.distrito = d.codigo_distrito
            LEFT JOIN corregimiento c ON e.provincia = c.codigo_provincia AND e.distrito = c.codigo_distrito AND e.corregimiento = c.codigo_corregimiento
            LEFT JOIN departamento dep ON e.departamento = dep.codigo
            LEFT JOIN cargo car ON e.departamento = car.dep_codigo AND e.cargo = car.codigo
            WHERE e.cedula = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Vincular la cedula como parámetro
        mysqli_stmt_bind_param($stmt, "s", $cedula);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                // Obtener los datos del empleado
                $employee_data = mysqli_fetch_assoc($result);
            } else {
                // No se encontró el empleado
                $error = "No se encontró el empleado solicitado.";
            }
        } else {
            $error = "Error al ejecutar la consulta: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
} else {
    // No se proporcionó la cedula
    header("location: list.php");
    exit();
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalles del Empleado</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($_SESSION["is_admin"]): ?>
        <div class="btn-group me-2">
            <a href="edit.php?cedula=<?php echo $cedula; ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="list.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a la Lista
            </a>
        </div>
        <?php else: ?>
        <a href="/ds6/dashboard.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
</div>
<?php else: ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar-container mb-3">
                    <i class="fas fa-user-circle fa-6x text-primary"></i>
                </div>
                <h3 class="card-title">
                    <?php echo htmlspecialchars($employee_data['nombre1']) . ' ' . htmlspecialchars($employee_data['apellido1']); ?>
                </h3>
                <p class="card-text">
                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars($employee_data['cargo_nombre']); ?></span>
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($employee_data['departamento_nombre']); ?></span>
                </p>
                <p class="card-text">
                    <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($employee_data['cedula']); ?>
                </p>
                <p class="card-text">
                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($employee_data['correo']); ?>
                </p>
                <p class="card-text">
                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($employee_data['celular']); ?>
                </p>
                <p class="card-text">
                    <?php if ($employee_data['estado'] == 1): ?>
                    <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                    <span class="badge bg-danger">Inactivo</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Información Personal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nombres:</label>
                        <p><?php echo htmlspecialchars($employee_data['nombre1']) . ' ' . htmlspecialchars($employee_data['nombre2']); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Apellidos:</label>
                        <p>
                            <?php 
                                echo htmlspecialchars($employee_data['apellido1']) . ' ' . 
                                     htmlspecialchars($employee_data['apellido2']);
                                if (!empty($employee_data['apellidoc'])) {
                                    echo ' de ' . htmlspecialchars($employee_data['apellidoc']);
                                }
                            ?>
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Género:</label>
                        <p><?php echo $employee_data['genero'] == 1 ? 'Masculino' : 'Femenino'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Estado Civil:</label>
                        <p>
                            <?php 
                                if ($employee_data['estado_civil'] === 0) {
                                    echo 'Soltero/a';
                                } elseif ($employee_data['estado_civil'] == 1) {
                                    echo 'Casado/a';
                                } elseif ($employee_data['estado_civil'] == 2) {
                                    echo 'Viudo/a'; 
                                } elseif ($employee_data['estado_civil'] == 3) {
                                    echo 'Divorciado/a';
                                } else {
                                    echo 'No especificado';
                                }
                            ?>
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Fecha de Nacimiento:</label>
                        <p><?php echo date('d/m/Y', strtotime($employee_data['f_nacimiento'])); ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Nacionalidad:</label>
                        <p><?php echo htmlspecialchars($employee_data['nacionalidad_nombre']); ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Tipo de Sangre:</label>
                        <p><?php echo !empty($employee_data['tipo_sangre']) ? htmlspecialchars($employee_data['tipo_sangre']) : 'No especificado'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-map-marked-alt"></i> Dirección y Contacto</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Dirección:</label>
                        <p>
                            <?php 
                                $direccion = [];
                                if (!empty($employee_data['calle'])) $direccion[] = 'Calle ' . htmlspecialchars($employee_data['calle']);
                                if (!empty($employee_data['casa'])) $direccion[] = 'Casa ' . htmlspecialchars($employee_data['casa']);
                                if (!empty($employee_data['comunidad'])) $direccion[] = htmlspecialchars($employee_data['comunidad']);
                                if (!empty($employee_data['corregimiento_nombre'])) $direccion[] = htmlspecialchars($employee_data['corregimiento_nombre']);
                                if (!empty($employee_data['distrito_nombre'])) $direccion[] = htmlspecialchars($employee_data['distrito_nombre']);
                                if (!empty($employee_data['provincia_nombre'])) $direccion[] = htmlspecialchars($employee_data['provincia_nombre']);
                                
                                echo !empty($direccion) ? implode(', ', $direccion) : 'No especificada';
                            ?>
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Celular:</label>
                        <p><?php echo htmlspecialchars($employee_data['celular']); ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Teléfono Fijo:</label>
                        <p><?php echo !empty($employee_data['telefono']) ? htmlspecialchars($employee_data['telefono']) : 'No especificado'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Correo Electrónico:</label>
                        <p><?php echo htmlspecialchars($employee_data['correo']); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-briefcase"></i> Información Laboral</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Cargo:</label>
                        <p><?php echo htmlspecialchars($employee_data['cargo_nombre']); ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Departamento:</label>
                        <p><?php echo htmlspecialchars($employee_data['departamento_nombre']); ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Fecha de Contratación:</label>
                        <p><?php echo date('d/m/Y', strtotime($employee_data['f_contra'])); ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Estado:</label>
                        <p>
                            <?php if ($employee_data['estado'] == 1): ?>
                            <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php 
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php"; 
?>
