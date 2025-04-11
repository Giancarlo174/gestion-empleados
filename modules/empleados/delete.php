<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /ds6/index.php");
    exit;
}

// Verificar si es administrador
if (!$_SESSION["is_admin"]) {
    header("location: /ds6/dashboard.php");
    exit;
}

// Incluir archivo de configuración
require_once "../../config/db.php";

// Definir variables
$cedula = "";
$error = "";
$success = "";

// Verificar si existe el parámetro cedula en la URL
if (isset($_GET["cedula"]) && !empty(trim($_GET["cedula"]))) {
    // Obtener el parámetro de cedula
    $cedula = trim($_GET["cedula"]);
}

// Procesar la eliminación cuando se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se recibió la cedula
    if (isset($_POST["cedula"]) && !empty($_POST["cedula"])) {
        $cedula = $_POST["cedula"];
        
        // Iniciar una transacción
        mysqli_begin_transaction($conn);
        
        try {
            // Primero, obtener todos los datos del empleado
            $sql_select = "SELECT * FROM empleados WHERE cedula = ?";
            $stmt_select = mysqli_prepare($conn, $sql_select);
            mysqli_stmt_bind_param($stmt_select, "s", $cedula);
            mysqli_stmt_execute($stmt_select);
            $result = mysqli_stmt_get_result($stmt_select);
            
            if (mysqli_num_rows($result) == 1) {
                $empleado = mysqli_fetch_assoc($result);
                
                // Insertar en la tabla de empleados eliminados
                $sql_insert = "INSERT INTO e_eliminados 
                               (cedula, prefijo, tomo, asiento, nombre1, nombre2, apellido1, apellido2, apellidoc, 
                               genero, estado_civil, tipo_sangre, usa_ac, f_nacimiento, celular, telefono, correo, 
                               provincia, distrito, corregimiento, calle, casa, comunidad, nacionalidad, 
                               f_contra, cargo, departamento, estado, f_eliminacion) 
                               VALUES 
                               (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE())";
                
                $stmt_insert = mysqli_prepare($conn, $sql_insert);
                mysqli_stmt_bind_param($stmt_insert, "sssssssssiisisiiisssssssssi", 
                    $empleado['cedula'], $empleado['prefijo'], $empleado['tomo'], $empleado['asiento'], 
                    $empleado['nombre1'], $empleado['nombre2'], $empleado['apellido1'], $empleado['apellido2'], $empleado['apellidoc'], 
                    $empleado['genero'], $empleado['estado_civil'], $empleado['tipo_sangre'], $empleado['usa_ac'], $empleado['f_nacimiento'], 
                    $empleado['celular'], $empleado['telefono'], $empleado['correo'], 
                    $empleado['provincia'], $empleado['distrito'], $empleado['corregimiento'], $empleado['calle'], $empleado['casa'], $empleado['comunidad'], $empleado['nacionalidad'], 
                    $empleado['f_contra'], $empleado['cargo'], $empleado['departamento'], $empleado['estado']);
                
                mysqli_stmt_execute($stmt_insert);
                
                // Eliminar el empleado original
                $sql_delete = "DELETE FROM empleados WHERE cedula = ?";
                $stmt_delete = mysqli_prepare($conn, $sql_delete);
                mysqli_stmt_bind_param($stmt_delete, "s", $cedula);
                mysqli_stmt_execute($stmt_delete);
                
                // También eliminar el usuario asociado si existe
                $sql_usuario = "SELECT id FROM usuarios WHERE cedula = ?";
                $stmt_usuario = mysqli_prepare($conn, $sql_usuario);
                mysqli_stmt_bind_param($stmt_usuario, "s", $cedula);
                mysqli_stmt_execute($stmt_usuario);
                $result_usuario = mysqli_stmt_get_result($stmt_usuario);
                
                if (mysqli_num_rows($result_usuario) > 0) {
                    $usuario = mysqli_fetch_assoc($result_usuario);
                    
                    // Obtener datos del usuario antes de eliminarlo
                    $sql_usuario_data = "SELECT * FROM usuarios WHERE id = ?";
                    $stmt_usuario_data = mysqli_prepare($conn, $sql_usuario_data);
                    mysqli_stmt_bind_param($stmt_usuario_data, "i", $usuario['id']);
                    mysqli_stmt_execute($stmt_usuario_data);
                    $result_usuario_data = mysqli_stmt_get_result($stmt_usuario_data);
                    $usuario_data = mysqli_fetch_assoc($result_usuario_data);
                    
                    // Insertar en la tabla de usuarios eliminados
                    $sql_usuario_insert = "INSERT INTO u_eliminados (id, cedula, contraseña, correo_institucional, f_eliminacion) VALUES (?, ?, ?, ?, CURRENT_DATE())";
                    $stmt_usuario_insert = mysqli_prepare($conn, $sql_usuario_insert);
                    mysqli_stmt_bind_param($stmt_usuario_insert, "isss", 
                        $usuario_data['id'], $usuario_data['cedula'], $usuario_data['contraseña'], $usuario_data['correo_institucional']);
                    mysqli_stmt_execute($stmt_usuario_insert);
                    
                    // Eliminar el usuario
                    $sql_usuario_delete = "DELETE FROM usuarios WHERE id = ?";
                    $stmt_usuario_delete = mysqli_prepare($conn, $sql_usuario_delete);
                    mysqli_stmt_bind_param($stmt_usuario_delete, "i", $usuario['id']);
                    mysqli_stmt_execute($stmt_usuario_delete);
                }
                
                // Confirmar la transacción
                mysqli_commit($conn);
                
                $success = "El empleado ha sido eliminado correctamente.";
                
                // Redirigir después de 2 segundos
                header("refresh:2;url=list.php");
            } else {
                throw new Exception("No se encontró el empleado con la cédula proporcionada.");
            }
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            mysqli_rollback($conn);
            $error = "Error al eliminar el empleado: " . $e->getMessage();
        }
    } else {
        $error = "Parámetro de cédula faltante.";
    }
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Eliminar Empleado</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="list.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver a la Lista
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (empty($success)): ?>
<div class="card">
    <div class="card-body text-center">
        <h4 class="card-title mb-4">¿Está seguro que desea eliminar este empleado?</h4>
        
        <?php
        // Consultar información básica del empleado
        if (!empty($cedula)) {
            $sql = "SELECT cedula, nombre1, apellido1, departamento.nombre as departamento_nombre 
                    FROM empleados 
                    LEFT JOIN departamento ON empleados.departamento = departamento.codigo 
                    WHERE cedula = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $cedula);
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);
                    if (mysqli_num_rows($result) == 1) {
                        $row = mysqli_fetch_assoc($result);
                        ?>
                        <div class="alert alert-warning">
                            <p><strong>Cédula:</strong> <?php echo htmlspecialchars($row['cedula']); ?></p>
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($row['nombre1'] . ' ' . $row['apellido1']); ?></p>
                            <p><strong>Departamento:</strong> <?php echo htmlspecialchars($row['departamento_nombre']); ?></p>
                            <p class="mb-0"><strong>IMPORTANTE:</strong> Esta acción no se puede deshacer.</p>
                        </div>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="cedula" value="<?php echo $cedula; ?>">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-trash"></i> Eliminar Empleado
                            </button>
                            <a href="list.php" class="btn btn-secondary btn-lg ms-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </form>
                        <?php
                    } else {
                        echo '<div class="alert alert-danger">No se encontró el empleado con la cédula proporcionada.</div>';
                        echo '<a href="list.php" class="btn btn-primary">Volver a la Lista</a>';
                    }
                } else {
                    echo '<div class="alert alert-danger">Error al ejecutar la consulta: ' . mysqli_error($conn) . '</div>';
                    echo '<a href="list.php" class="btn btn-primary">Volver a la Lista</a>';
                }
                
                mysqli_stmt_close($stmt);
            }
        } else {
            echo '<div class="alert alert-danger">Parámetro de cédula faltante.</div>';
            echo '<a href="list.php" class="btn btn-primary">Volver a la Lista</a>';
        }
        ?>
    </div>
</div>
<?php endif; ?>

<?php 
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php"; 
?>
