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

// Definir variables e inicializar con valores vacíos
$nombre = $departamento = "";
$nombre_err = $departamento_err = "";
$success = "";

// Verificar si existe el parámetro codigo en la URL
if (isset($_GET["codigo"]) && !empty(trim($_GET["codigo"]))) {
    // Obtener código del cargo
    $codigo = trim($_GET["codigo"]);
    
    // Preparar consulta para obtener los datos del cargo
    $sql = "SELECT * FROM cargo WHERE codigo = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Vincular variables a la consulta como parámetros
        mysqli_stmt_bind_param($stmt, "s", $codigo);
        
        // Ejecutar consulta
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                $nombre = $row["nombre"];
                $departamento = $row["dep_codigo"];
            } else {
                // URL no contiene un código válido
                header("location: list.php");
                exit();
            }
        } else {
            echo "Error al ejecutar la consulta: " . mysqli_error($conn);
            exit();
        }
        
        // Cerrar sentencia
        mysqli_stmt_close($stmt);
    }
} else {
    // URL no contiene parámetro codigo
    header("location: list.php");
    exit();
}

// Obtener los departamentos disponibles
$query_departamentos = "SELECT * FROM departamento ORDER BY nombre";
$result_departamentos = mysqli_query($conn, $query_departamentos);

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar nombre
    $nombre = trim($_POST["nombre"]);
    if (empty($nombre)) {
        $nombre_err = "Por favor ingrese el nombre del cargo.";
    }
    
    // Validar departamento
    $departamento = trim($_POST["departamento"]);
    if (empty($departamento)) {
        $departamento_err = "Por favor seleccione un departamento.";
    }
    
    // Verificar errores antes de actualizar en la base de datos
    if (empty($nombre_err) && empty($departamento_err)) {
        // Preparar consulta de actualización
        $sql = "UPDATE cargo SET nombre = ?, dep_codigo = ? WHERE codigo = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variables a la consulta como parámetros
            mysqli_stmt_bind_param($stmt, "sss", $param_nombre, $param_departamento, $param_codigo);
            
            // Asignar parámetros
            $param_nombre = $nombre;
            $param_departamento = $departamento;
            $param_codigo = $codigo;
            
            // Ejecutar consulta
            if (mysqli_stmt_execute($stmt)) {
                // Actualización exitosa, mostrar mensaje
                $success = "Cargo actualizado correctamente.";
            } else {
                echo "Error al ejecutar la consulta: " . mysqli_error($conn);
            }
            
            // Cerrar sentencia
            mysqli_stmt_close($stmt);
        }
    }
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Cargo</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="list.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver a la Lista
        </a>
    </div>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?codigo=' . $codigo); ?>" method="post">
            <div class="mb-3">
                <label for="codigo" class="form-label">Código de Cargo</label>
                <input type="text" class="form-control" id="codigo" value="<?php echo $codigo; ?>" disabled>
                <small class="form-text text-muted">El código no se puede cambiar.</small>
            </div>
            
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Cargo *</label>
                <input type="text" class="form-control <?php echo (!empty($nombre_err)) ? 'is-invalid' : ''; ?>" id="nombre" name="nombre" value="<?php echo $nombre; ?>" maxlength="50" required>
                <div class="invalid-feedback"><?php echo $nombre_err; ?></div>
            </div>
            
            <div class="mb-3">
                <label for="departamento" class="form-label">Departamento *</label>
                <select class="form-select <?php echo (!empty($departamento_err)) ? 'is-invalid' : ''; ?>" id="departamento" name="departamento" required>
                    <option value="">Seleccione un departamento...</option>
                    <?php
                    // Restablecer el puntero de resultados
                    if ($result_departamentos) {
                        mysqli_data_seek($result_departamentos, 0);
                        while ($row_dep = mysqli_fetch_assoc($result_departamentos)) {
                            $selected = ($departamento == $row_dep['codigo']) ? 'selected' : '';
                            echo "<option value='" . $row_dep['codigo'] . "' $selected>" . htmlspecialchars($row_dep['nombre']) . "</option>";
                        }
                    }
                    ?>
                </select>
                <div class="invalid-feedback"><?php echo $departamento_err; ?></div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar Cargo
                </button>
                <a href="list.php" class="btn btn-secondary ms-2">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php"; 
?>
