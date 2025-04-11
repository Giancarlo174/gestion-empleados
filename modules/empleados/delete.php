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

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Eliminar el empleado después de la confirmación
    try {
        // Iniciar una transacción para garantizar integridad de datos
        mysqli_begin_transaction($conn);
        
        // Obtener todos los datos del empleado antes de eliminarlo
        $sql_select = "SELECT *, NOW() AS f_eliminacion FROM empleados WHERE cedula = ?";
        $stmt_select = mysqli_prepare($conn, $sql_select);
        mysqli_stmt_bind_param($stmt_select, "s", $param_cedula);
        $param_cedula = trim($_POST["cedula"]);
        mysqli_stmt_execute($stmt_select);
        $result = mysqli_stmt_get_result($stmt_select);
        
        // Verificar si se encontró el empleado
        if ($empleado = mysqli_fetch_assoc($result)) {
            // Insertar en la tabla e_eliminados
            $sql_insert = "INSERT INTO e_eliminados (
                cedula, prefijo, tomo, asiento, nombre1, nombre2, apellido1, apellido2, 
                apellidoc, genero, estado_civil, tipo_sangre, usa_ac, f_nacimiento, 
                celular, telefono, correo, provincia, distrito, corregimiento, 
                calle, casa, comunidad, nacionalidad, f_contra, cargo, departamento, estado, f_eliminacion
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )";
            
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            
            mysqli_stmt_bind_param($stmt_insert, "sssssssssiisissssssssssssssi", 
                $empleado['cedula'], $empleado['prefijo'], $empleado['tomo'], $empleado['asiento'],
                $empleado['nombre1'], $empleado['nombre2'], $empleado['apellido1'], $empleado['apellido2'],
                $empleado['apellidoc'], $empleado['genero'], $empleado['estado_civil'], $empleado['tipo_sangre'],
                $empleado['usa_ac'], $empleado['f_nacimiento'], $empleado['celular'], $empleado['telefono'],
                $empleado['correo'], $empleado['provincia'], $empleado['distrito'], $empleado['corregimiento'],
                $empleado['calle'], $empleado['casa'], $empleado['comunidad'], $empleado['nacionalidad'],
                $empleado['f_contra'], $empleado['cargo'], $empleado['departamento'], $empleado['estado']
            );
            
            // Ejecutar la inserción
            if (!mysqli_stmt_execute($stmt_insert)) {
                throw new Exception("Error al archivar el empleado: " . mysqli_stmt_error($stmt_insert));
            }
            mysqli_stmt_close($stmt_insert);
            
            // Ahora eliminar el registro de la tabla original
            $sql = "DELETE FROM empleados WHERE cedula = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $param_cedula);
            
            // Intentar ejecutar la consulta preparada
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error al eliminar el empleado: " . mysqli_stmt_error($stmt));
            }
            
            // Confirmar la transacción si todo fue exitoso
            mysqli_commit($conn);
            
            // Redireccionar a la página de listado con mensaje de éxito
            header("location: list.php?deleted=1");
            exit();
        } else {
            throw new Exception("No se encontró el empleado a eliminar.");
        }
    } catch (Exception $e) {
        // Deshacer la transacción si hubo algún problema
        mysqli_rollback($conn);
        
        // Redirigir con mensaje de error
        header("location: list.php?error=1&message=" . urlencode("Error: " . $e->getMessage()));
        exit();
    } finally {
        // Cerrar sentencias si no se cerraron antes
        if (isset($stmt) && $stmt) mysqli_stmt_close($stmt);
        if (isset($stmt_select) && $stmt_select) mysqli_stmt_close($stmt_select);
    }
} else {
    // Verificar si existe el parámetro cedula en la URL
    if (isset($_GET["cedula"]) && !empty(trim($_GET["cedula"]))) {
        // Obtener el parámetro cedula
        $cedula = trim($_GET["cedula"]);
        
        // Preparar la consulta para obtener información del empleado
        $sql = "SELECT cedula, nombre1, apellido1 FROM empleados WHERE cedula = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular la cedula como parámetro
            mysqli_stmt_bind_param($stmt, "s", $cedula);
            
            // Ejecutar la consulta
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) == 1) {
                    // Obtener los datos del empleado
                    $row = mysqli_fetch_assoc($result);
                } else {
                    // No se encontró el empleado
                    header("location: list.php?error=1&message=" . urlencode("No se encontró el empleado con la cédula especificada"));
                    exit();
                }
            } else {
                header("location: list.php?error=1&message=" . urlencode("Error al consultar el empleado: " . mysqli_error($conn)));
                exit();
            }
            
            mysqli_stmt_close($stmt);
        }
    } else {
        // Si no se proporcionó una cédula
        header("location: list.php?error=1&message=" . urlencode("Se requiere una cédula para eliminar un empleado"));
        exit();
    }
}

// Incluir header
include "../../includes/header.php";
?>

<!-- Mostrar formulario de confirmación solo si estamos en GET y tenemos datos del empleado -->
<?php if ($_SERVER["REQUEST_METHOD"] != "POST" && isset($row)): ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Eliminar Empleado</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="list.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver a la Lista
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body text-center">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
            <h2 class="mt-3">¿Está seguro que desea eliminar este empleado?</h2>
            <p class="lead text-muted">Esta acción no se puede deshacer.</p>
        </div>
        
        <div class="card mb-4 mx-auto" style="max-width: 30rem;">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Información del empleado a eliminar</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Cédula:</strong> <?php echo htmlspecialchars($row["cedula"]); ?></p>
                <p class="mb-0"><strong>Nombre:</strong> <?php echo htmlspecialchars($row["nombre1"] . " " . $row["apellido1"]); ?></p>
            </div>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="cedula" value="<?php echo $row["cedula"]; ?>">
            <div class="d-grid gap-2 col-6 mx-auto">
                <button type="submit" class="btn btn-danger btn-lg">
                    <i class="fas fa-trash-alt me-2"></i> Sí, Eliminar Empleado
                </button>
                <a href="list.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times me-2"></i> No, Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php";
?>
