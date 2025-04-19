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
$nombre_err = "";
$success = "";

// Verificar si existe el parámetro codigo en la URL
if (isset($_GET["codigo"]) && !empty(trim($_GET["codigo"]))) {
    // Obtener código del departamento
    $codigo = trim($_GET["codigo"]);
    
    // Preparar consulta para obtener los datos del departamento
    $sql = "SELECT * FROM departamento WHERE codigo = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Vincular variables a la consulta como parámetros
        mysqli_stmt_bind_param($stmt, "s", $codigo);
        
        // Ejecutar consulta
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                $nombre = $row["nombre"];
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

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar nombre
    $nombre = trim($_POST["nombre"]);
    if (empty($nombre)) {
        $nombre_err = "Por favor ingrese el nombre del departamento.";
    }
    
    // Verificar errores antes de actualizar
    if (empty($nombre_err)) {
        // Preparar consulta de actualización
        $sql = "UPDATE departamento SET nombre = ? WHERE codigo = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variables a la consulta como parámetros
            mysqli_stmt_bind_param($stmt, "ss", $param_nombre, $param_codigo);
            
            // Asignar parámetros
            $param_nombre = $nombre;
            $param_codigo = $codigo;
            
            // Ejecutar consulta
            if (mysqli_stmt_execute($stmt)) {
                $success = "Departamento actualizado correctamente.";
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
    <h1 class="h2">Editar Departamento</h1>
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
                <label for="codigo" class="form-label">Código de Departamento</label>
                <input type="text" class="form-control" id="codigo" value="<?php echo $codigo; ?>" disabled>
                <small class="form-text text-muted">El código no se puede cambiar.</small>
            </div>
            
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre de Departamento *</label>
                <input type="text" class="form-control <?php echo (!empty($nombre_err)) ? 'is-invalid' : ''; ?>" id="nombre" name="nombre" value="<?php echo $nombre; ?>" maxlength="50">
                <div class="invalid-feedback"><?php echo $nombre_err; ?></div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar Departamento
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
