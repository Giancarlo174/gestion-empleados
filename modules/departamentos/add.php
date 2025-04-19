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
$codigo = $nombre = "";
$codigo_err = $nombre_err = "";
$success = "";

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar código
    $codigo = trim($_POST["codigo"]);
    if (empty($codigo)) {
        $codigo_err = "Por favor ingrese el código del departamento.";
    } else {
        // Verificar si el código ya existe
        $sql = "SELECT codigo FROM departamento WHERE codigo = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variables a la consulta como parámetros
            mysqli_stmt_bind_param($stmt, "s", $param_codigo);
            
            // Asignar parámetros
            $param_codigo = $codigo;
            
            // Ejecutar consulta
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $codigo_err = "Este código ya está en uso.";
                }
            } else {
                echo "Error al ejecutar la consulta: " . mysqli_error($conn);
            }
            
            // Cerrar sentencia
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validar nombre
    $nombre = trim($_POST["nombre"]);
    if (empty($nombre)) {
        $nombre_err = "Por favor ingrese el nombre del departamento.";
    }
    
    // Verificar errores antes de insertar en la base de datos
    if (empty($codigo_err) && empty($nombre_err)) {
        // Preparar consulta de inserción
        $sql = "INSERT INTO departamento (codigo, nombre) VALUES (?, ?)";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variables a la consulta como parámetros
            mysqli_stmt_bind_param($stmt, "ss", $param_codigo, $param_nombre);
            
            // Asignar parámetros
            $param_codigo = $codigo;
            $param_nombre = $nombre;
            
            // Ejecutar consulta
            if (mysqli_stmt_execute($stmt)) {
                // Registro exitoso, mostrar mensaje y limpiar campos
                $success = "Departamento agregado correctamente.";
                $codigo = $nombre = "";
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
    <h1 class="h2">Agregar Departamento</h1>
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
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="codigo" class="form-label">Código de Departamento *</label>
                <input type="text" class="form-control <?php echo (!empty($codigo_err)) ? 'is-invalid' : ''; ?>" id="codigo" name="codigo" value="<?php echo $codigo; ?>" maxlength="2">
                <div class="invalid-feedback"><?php echo $codigo_err; ?></div>
                <small class="form-text text-muted">Ingrese un código único de hasta 2 caracteres.</small>
            </div>
            
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre de Departamento *</label>
                <input type="text" class="form-control <?php echo (!empty($nombre_err)) ? 'is-invalid' : ''; ?>" id="nombre" name="nombre" value="<?php echo $nombre; ?>" maxlength="50">
                <div class="invalid-feedback"><?php echo $nombre_err; ?></div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Agregar Departamento
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
