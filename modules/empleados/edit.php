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
    $cedula_session = $_SESSION["cedula"];
    $cedula_requested = isset($_GET["cedula"]) ? $_GET["cedula"] : "";
    
    // Si no es administrador, solo puede ver su propia información
    if ($cedula_session !== $cedula_requested) {
        header("location: /ds6/dashboard.php");
        exit;
    }
}

// Incluir archivo de configuración
require_once "../../config/db.php";

// Definir variables e inicializar con valores vacíos
$prefijo = $tomo = $asiento = $nombre1 = $nombre2 = $apellido1 = $apellido2 = $apellidoc = "";
$genero = $estado_civil = $tipo_sangre = $usa_ac = $f_nacimiento = $celular = $telefono = $correo = "";
$provincia = $distrito = $corregimiento = $calle = $casa = $comunidad = $nacionalidad = $f_contra = $cargo = $departamento = $estado = "";
$error = "";
$success = "";

// Verificar si existe el parámetro cedula en la URL
if (isset($_GET["cedula"]) && !empty(trim($_GET["cedula"]))) {
    // Obtener el parámetro de cedula
    $cedula = trim($_GET["cedula"]);
    
    // Preparar la consulta para obtener los datos del empleado
    $sql = "SELECT * FROM empleados WHERE cedula = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Vincular la cedula como parámetro
        mysqli_stmt_bind_param($stmt, "s", $cedula);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                // Obtener los datos del empleado
                $row = mysqli_fetch_assoc($result);
                
                // Asignar los valores a las variables
                $prefijo = $row["prefijo"];
                $tomo = $row["tomo"];
                $asiento = $row["asiento"];
                $nombre1 = $row["nombre1"];
                $nombre2 = $row["nombre2"];
                $apellido1 = $row["apellido1"];
                $apellido2 = $row["apellido2"];
                $apellidoc = $row["apellidoc"];
                $genero = $row["genero"];
                $estado_civil = $row["estado_civil"];
                $tipo_sangre = $row["tipo_sangre"];
                $usa_ac = $row["usa_ac"];
                $f_nacimiento = $row["f_nacimiento"];
                $celular = $row["celular"];
                $telefono = $row["telefono"];
                $correo = $row["correo"];
                $provincia = $row["provincia"];
                $distrito = $row["distrito"];
                $corregimiento = $row["corregimiento"];
                $calle = $row["calle"];
                $casa = $row["casa"];
                $comunidad = $row["comunidad"];
                $nacionalidad = $row["nacionalidad"];
                $f_contra = $row["f_contra"];
                $cargo = $row["cargo"];
                $departamento = $row["departamento"];
                $estado = $row["estado"];
            } else {
                // No se encontró el empleado
                header("location: list.php");
                exit();
            }
        } else {
            echo "Error al ejecutar la consulta: " . mysqli_error($conn);
            exit();
        }
        
        mysqli_stmt_close($stmt);
    }
} else {
    // No se proporcionó la cedula
    header("location: list.php");
    exit();
}

// Cargar listas necesarias
// Nacionalidades
$query_nacionalidades = "SELECT * FROM nacionalidad ORDER BY pais";
$result_nacionalidades = mysqli_query($conn, $query_nacionalidades);

// Provincias
$query_provincias = "SELECT * FROM provincia ORDER BY nombre_provincia";
$result_provincias = mysqli_query($conn, $query_provincias);

// Departamentos
$query_departamentos = "SELECT * FROM departamento ORDER BY nombre";
$result_departamentos = mysqli_query($conn, $query_departamentos);

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar datos del formulario
    // (Normalmente habría validación para cada campo)
    
    // Datos básicos
    $prefijo = trim($_POST["prefijo"]);
    $tomo = trim($_POST["tomo"]);
    $asiento = trim($_POST["asiento"]);
    $nombre1 = trim($_POST["nombre1"]);
    $nombre2 = trim($_POST["nombre2"]);
    $apellido1 = trim($_POST["apellido1"]);
    $apellido2 = trim($_POST["apellido2"]);
    $apellidoc = trim($_POST["apellidoc"]);
    $genero = $_POST["genero"];
    $estado_civil = $_POST["estado_civil"];
    $tipo_sangre = trim($_POST["tipo_sangre"]);
    $usa_ac = isset($_POST["usa_ac"]) ? 1 : 0;
    $f_nacimiento = trim($_POST["f_nacimiento"]);
    
    // Contacto
    $celular = trim($_POST["celular"]);
    $telefono = trim($_POST["telefono"]);
    $correo = trim($_POST["correo"]);
    
    // Ubicación
    $provincia = trim($_POST["provincia"]);
    $distrito = trim($_POST["distrito"]);
    $corregimiento = trim($_POST["corregimiento"]);
    $calle = trim($_POST["calle"]);
    $casa = trim($_POST["casa"]);
    $comunidad = trim($_POST["comunidad"]);
    $nacionalidad = trim($_POST["nacionalidad"]);
    
    // Información laboral
    $f_contra = trim($_POST["f_contra"]);
    $cargo = trim($_POST["cargo"]);
    $departamento = trim($_POST["departamento"]);
    $estado = $_POST["estado"];
    
    // Si no hay errores, actualizar el empleado
    if (empty($error)) {
        $sql = "UPDATE empleados SET 
                prefijo=?, tomo=?, asiento=?, nombre1=?, nombre2=?, apellido1=?, apellido2=?, apellidoc=?, 
                genero=?, estado_civil=?, tipo_sangre=?, usa_ac=?, f_nacimiento=?, celular=?, telefono=?, correo=?, 
                provincia=?, distrito=?, corregimiento=?, calle=?, casa=?, comunidad=?, nacionalidad=?, 
                f_contra=?, cargo=?, departamento=?, estado=? 
                WHERE cedula=?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssssiisisiiissssssssssiss", 
                $prefijo, $tomo, $asiento, $nombre1, $nombre2, $apellido1, $apellido2, $apellidoc, 
                $genero, $estado_civil, $tipo_sangre, $usa_ac, $f_nacimiento, $celular, $telefono, $correo, 
                $provincia, $distrito, $corregimiento, $calle, $casa, $comunidad, $nacionalidad, 
                $f_contra, $cargo, $departamento, $estado, $cedula);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Empleado actualizado correctamente.";
            } else {
                $error = "Error al actualizar el empleado: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Empleado</h1>
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

<div class="card">
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?cedula=' . $cedula; ?>" method="post" class="needs-validation" novalidate>
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="mb-3">Información Personal</h4>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="cedula" class="form-label">Cédula</label>
                    <input type="text" class="form-control" id="cedula" value="<?php echo $cedula; ?>" disabled>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="prefijo" class="form-label">Prefijo</label>
                    <input type="text" class="form-control" id="prefijo" name="prefijo" value="<?php echo $prefijo; ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="tomo" class="form-label">Tomo</label>
                    <input type="text" class="form-control" id="tomo" name="tomo" value="<?php echo $tomo; ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="asiento" class="form-label">Asiento</label>
                    <input type="text" class="form-control" id="asiento" name="asiento" value="<?php echo $asiento; ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nombre1" class="form-label">Primer Nombre *</label>
                    <input type="text" class="form-control" id="nombre1" name="nombre1" value="<?php echo $nombre1; ?>" required>
                    <div class="invalid-feedback">
                        Este campo es requerido.
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nombre2" class="form-label">Segundo Nombre</label>
                    <input type="text" class="form-control" id="nombre2" name="nombre2" value="<?php echo $nombre2; ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="apellido1" class="form-label">Primer Apellido *</label>
                    <input type="text" class="form-control" id="apellido1" name="apellido1" value="<?php echo $apellido1; ?>" required>
                    <div class="invalid-feedback">
                        Este campo es requerido.
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="apellido2" class="form-label">Segundo Apellido</label>
                    <input type="text" class="form-control" id="apellido2" name="apellido2" value="<?php echo $apellido2; ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="apellidoc" class="form-label">Apellido de Casado/a</label>
                    <input type="text" class="form-control" id="apellidoc" name="apellidoc" value="<?php echo $apellidoc; ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="genero" class="form-label">Género *</label>
                    <select class="form-select" id="genero" name="genero" required>
                        <option value="" disabled>Seleccione...</option>
                        <option value="1" <?php if ($genero == 1) echo "selected"; ?>>Masculino</option>
                        <option value="2" <?php if ($genero == 2) echo "selected"; ?>>Femenino</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="estado_civil" class="form-label">Estado Civil *</label>
                    <select class="form-select" id="estado_civil" name="estado_civil" required>
                        <option value="" disabled>Seleccione...</option>
                        <option value="1" <?php if ($estado_civil == 1) echo "selected"; ?>>Soltero/a</option>
                        <option value="2" <?php if ($estado_civil == 2) echo "selected"; ?>>Casado/a</option>
                        <option value="3" <?php if ($estado_civil == 3) echo "selected"; ?>>Divorciado/a</option>
                        <option value="4" <?php if ($estado_civil == 4) echo "selected"; ?>>Viudo/a</option>
                        <option value="5" <?php if ($estado_civil == 5) echo "selected"; ?>>Unión libre</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="nacionalidad" class="form-label">Nacionalidad *</label>
                    <select class="form-select select2-nacionalidad" id="nacionalidad" name="nacionalidad" required>
                        <?php if (!empty($nacionalidad)): 
                            // Si ya hay una nacionalidad seleccionada, obtener sus datos
                            $sql_nac = "SELECT codigo, pais FROM nacionalidad WHERE codigo = ?";
                            $stmt_nac = mysqli_prepare($conn, $sql_nac);
                            mysqli_stmt_bind_param($stmt_nac, "s", $nacionalidad);
                            mysqli_stmt_execute($stmt_nac);
                            $result_nac = mysqli_stmt_get_result($stmt_nac);
                            $nac_data = mysqli_fetch_assoc($result_nac);
                        ?>
                            <option value="<?php echo $nac_data['codigo']; ?>" selected>
                                <?php echo htmlspecialchars($nac_data['pais']); ?>
                            </option>
                        <?php else: ?>
                            <option value="">Seleccione o busque un país...</option>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback">
                        Seleccione una nacionalidad.
                    </div>
                </div>
                
                <!-- El resto del formulario es igual al de add.php -->
                <!-- Sigue el mismo patrón para los demás campos -->
                
                <!-- ... continúa con el resto del formulario ... -->
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Actualizar Empleado
                </button>
                <a href="list.php" class="btn btn-secondary btn-lg ms-2">
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
