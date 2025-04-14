<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /ds6/index.php");
    exit;
}

// Incluir archivo de configuración y funciones
require_once "../../config/db.php";
require_once "load_nacionalidades.php"; // Para cargar nacionalidades

// Determinar si el usuario es admin
$is_admin = isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
$cedula_session = $_SESSION["cedula"]; // Cédula del usuario logueado

// Obtener la cédula del empleado a editar desde GET
$cedula_original_get = isset($_GET["cedula"]) ? trim($_GET["cedula"]) : '';

// Verificar permisos: Admin puede editar a cualquiera, empleado solo a sí mismo
if (!$is_admin && $cedula_session !== $cedula_original_get) {
    // Si no es admin y la cédula no coincide, redirigir o mostrar error
    header("location: /ds6/modules/empleados/profile.php?error=permission_denied");
    exit;
}

// Definir variables e inicializar con valores vacíos
$prefijo = $tomo = $asiento = $nombre1 = $nombre2 = $apellido1 = $apellido2 = $apellidoc = "";
$genero = $estado_civil = $tipo_sangre = $usa_ac = $f_nacimiento = $celular = $telefono = $correo = "";
$provincia = $distrito = $corregimiento = $calle = $casa = $comunidad = $nacionalidad = $f_contra = $cargo = $departamento = $estado = "";
$error = "";
$success = "";

// Variables para guardar los datos originales
$original_prefijo = $original_tomo = $original_asiento = $original_nombre1 = $original_nombre2 = $original_apellido1 = $original_apellido2 = $original_apellidoc = "";
$original_genero = $original_estado_civil = $original_tipo_sangre = $original_usa_ac = $original_f_nacimiento = $original_celular = $original_telefono = $original_correo = "";
$original_provincia = $original_distrito = $original_corregimiento = $original_calle = $original_casa = $original_comunidad = $original_nacionalidad = $original_f_contra = $original_cargo = $original_departamento = $original_estado = "";
$cedula = $cedula_original_get; // Usar la cédula de GET como base

// Verificar si existe el parámetro cedula en la URL y no está vacío
if (!empty($cedula_original_get)) {
    // Preparar la consulta para obtener los datos del empleado
    $sql = "SELECT * FROM empleados WHERE cedula = ?";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $cedula_original_get);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                // Asignar valores a las variables y a las originales
                $prefijo = $original_prefijo = $row['prefijo'];
                $tomo = $original_tomo = $row['tomo'];
                $asiento = $original_asiento = $row['asiento'];
                $nombre1 = $original_nombre1 = $row['nombre1'];
                $nombre2 = $original_nombre2 = $row['nombre2'];
                $apellido1 = $original_apellido1 = $row['apellido1'];
                $apellido2 = $original_apellido2 = $row['apellido2'];
                $apellidoc = $original_apellidoc = $row['apellidoc'];
                $genero = $original_genero = $row['genero'];
                $estado_civil = $original_estado_civil = $row['estado_civil'];
                $tipo_sangre = $original_tipo_sangre = $row['tipo_sangre'];
                $usa_ac = $original_usa_ac = $row['usa_ac'];
                $f_nacimiento = $original_f_nacimiento = $row['f_nacimiento'];
                $celular = $original_celular = $row['celular'];
                $telefono = $original_telefono = $row['telefono'];
                $correo = $original_correo = $row['correo'];
                $provincia = $original_provincia = $row['provincia'];
                $distrito = $original_distrito = $row['distrito'];
                $corregimiento = $original_corregimiento = $row['corregimiento'];
                $calle = $original_calle = $row['calle'];
                $casa = $original_casa = $row['casa'];
                $comunidad = $original_comunidad = $row['comunidad'];
                $nacionalidad = $original_nacionalidad = $row['nacionalidad'];
                $f_contra = $original_f_contra = $row['f_contra'];
                $cargo = $original_cargo = $row['cargo'];
                $departamento = $original_departamento = $row['departamento'];
                $estado = $original_estado = $row['estado'];
            } else {
                // No se encontró el empleado
                $error = "No se encontró un empleado con la cédula proporcionada.";
            }
        } else {
            $error = "Error al ejecutar la consulta: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
         $error = "Error al preparar la consulta: " . mysqli_error($conn);
    }
} else {
    // No se proporcionó la cedula o está vacía
    $error = "No se especificó la cédula del empleado a editar.";
}

// Calcular fecha máxima para ser mayor de edad (18 años atrás desde hoy)
$maxDate = date('Y-m-d', strtotime('-18 years'));

// Cargar listas necesarias (Nacionalidades, Provincias, Departamentos)
$query_nacionalidades = "SELECT * FROM nacionalidad ORDER BY pais";
$result_nacionalidades = mysqli_query($conn, $query_nacionalidades);
$query_provincias = "SELECT * FROM provincia ORDER BY nombre_provincia";
$result_provincias = mysqli_query($conn, $query_provincias);
$query_departamentos = "SELECT * FROM departamento ORDER BY nombre";
$result_departamentos = mysqli_query($conn, $query_departamentos);

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {

    // 1. Recuperar la cédula original (guardada en hidden)
    $cedula_original_post = $_POST['cedula_original'];

    // Variable para almacenar los campos modificados para auditoría
    $campos_modificados = array();

    // --- RECAPTURA DE DATOS CON FALLBACK A ORIGINALES ---
    // Solo recapturar campos que el usuario puede editar

    // Datos de cédula (Solo admin puede cambiar)
    if ($is_admin) {
        $prefijo = isset($_POST['prefijo']) && trim($_POST['prefijo']) !== '' ? trim($_POST['prefijo']) : $original_prefijo;
        $tomo    = isset($_POST['tomo']) && trim($_POST['tomo']) !== '' ? trim($_POST['tomo']) : $original_tomo;
        $asiento = isset($_POST['asiento']) && trim($_POST['asiento']) !== '' ? trim($_POST['asiento']) : $original_asiento;
        $cedula_nueva = $prefijo . "-" . $tomo . "-" . $asiento;
        
    
    } else {
        $prefijo = $original_prefijo;
        $tomo = $original_tomo;
        $asiento = $original_asiento;
        $cedula_nueva = $cedula_original_post; // Cédula no cambia para empleado
    }

    // Datos básicos (Todos pueden editar)
    $nombre1 = isset($_POST["nombre1"]) && trim($_POST["nombre1"]) !== '' ? trim($_POST["nombre1"]) : $original_nombre1;
    if ($nombre1 !== $original_nombre1) {
        $campos_modificados['nombre1'] = $original_nombre1;
    }
    
    $nombre2 = isset($_POST["nombre2"]) ? trim($_POST["nombre2"]) : $original_nombre2; // Puede ser vacío
    if ($nombre2 !== $original_nombre2) {
        $campos_modificados['nombre2'] = $original_nombre2;
    }
    
    $apellido1 = isset($_POST["apellido1"]) && trim($_POST["apellido1"]) !== '' ? trim($_POST["apellido1"]) : $original_apellido1;
    if ($apellido1 !== $original_apellido1) {
        $campos_modificados['apellido1'] = $original_apellido1;
    }
    
    $apellido2 = isset($_POST["apellido2"]) ? trim($_POST["apellido2"]) : $original_apellido2; // Puede ser vacío
    if ($apellido2 !== $original_apellido2) {
        $campos_modificados['apellido2'] = $original_apellido2;
    }

    // Campos que influyen en lógica condicional (Todos pueden editar)
    $genero = isset($_POST["genero"]) && $_POST["genero"] !== '' ? (int)$_POST["genero"] : $original_genero;
    if ($genero !== $original_genero) {
        $campos_modificados['genero'] = $original_genero;
    }
    
    $estado_civil = isset($_POST["estado_civil"]) && $_POST["estado_civil"] !== '' ? (int)$_POST["estado_civil"] : $original_estado_civil;
    if ($estado_civil !== $original_estado_civil) {
        $campos_modificados['estado_civil'] = $original_estado_civil;
    }
    
    $usa_ac = isset($_POST["usa_ac"]) ? 1 : 0;
    if ($usa_ac !== $original_usa_ac) {
        $campos_modificados['usa_ac'] = $original_usa_ac;
    }

    // Apellido de casada (depende de genero, estado civil, usa_ac)
    $temp_apellidoc = isset($_POST['apellidoc']) ? trim($_POST['apellidoc']) : $original_apellidoc;
    if ($genero == 0 && $estado_civil == 1 && $usa_ac == 1) {
        $apellidoc = $temp_apellidoc;
        if ($apellidoc !== $original_apellidoc) {
            $campos_modificados['apellidoc'] = $original_apellidoc;
        }
    } else {
        $apellidoc = ""; // Limpiar si no aplica
        if ($original_apellidoc !== "") {
            $campos_modificados['apellidoc'] = $original_apellidoc;
        }
    }

    // tipo_sangre (Todos pueden editar)
    $tipo_sangre = isset($_POST['tipo_sangre']) && $_POST['tipo_sangre'] !== '' ? trim($_POST['tipo_sangre']) : $original_tipo_sangre;
    if ($tipo_sangre !== $original_tipo_sangre) {
        $campos_modificados['tipo_sangre'] = $original_tipo_sangre;
    }
    
    if (empty($tipo_sangre)) { $error = "Por favor seleccione un tipo de sangre."; }

    // f_nacimiento (Todos pueden editar)
    $f_nacimiento_input = isset($_POST['f_nacimiento']) ? trim($_POST['f_nacimiento']) : '';
    if (!empty($f_nacimiento_input)) {
        $f_nacimiento = $f_nacimiento_input;
        if ($f_nacimiento !== $original_f_nacimiento) {
            $campos_modificados['f_nacimiento'] = $original_f_nacimiento;
        }
        
        // Validar mayoría de edad
        $birthDate = new DateTime($f_nacimiento);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        if ($age < 18) { $error = "El empleado debe ser mayor de edad (al menos 18 años)."; }
    } else {
        $f_nacimiento = $original_f_nacimiento; // Mantener original si no se envía
    }

    // Contacto (Todos pueden editar, excepto correo para empleados)
    $celular_post = isset($_POST["celular"]) ? trim($_POST["celular"]) : '';

    // Check if there's a real change
    if (($celular_post === '' && empty($original_celular)) || $celular_post == $original_celular) {
        // No change detected
        $celular = $original_celular;
    } else {
        // Change detected
        $celular = $celular_post;
        $campos_modificados['celular'] = $original_celular;
    }
    
    if (!empty($celular) && (!is_numeric($celular) || strlen($celular) > 8)) { $error = "El celular debe ser un número de máximo 8 dígitos."; }

    $telefono_post = isset($_POST["telefono"]) ? trim($_POST["telefono"]) : '';

    // Check if there's a real change
    if (($telefono_post === '' && empty($original_telefono)) || $telefono_post == $original_telefono) {
        // No change detected
        $telefono = $original_telefono;
    } else {
        // Change detected
        $telefono = $telefono_post;
        $campos_modificados['telefono'] = $original_telefono;
    }
    
    if (!empty($telefono) && (!is_numeric($telefono) || strlen($telefono) > 7)) { $error = "El teléfono debe ser un número de máximo 7 dígitos."; }

    // Correo (Solo admin puede editar)
    if ($is_admin) {
        $correo = isset($_POST["correo"]) && trim($_POST["correo"]) !== '' ? trim($_POST["correo"]) : $original_correo;
        if ($correo !== $original_correo) {
            $campos_modificados['correo'] = $original_correo;
        }
        
        if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) { $error = "Por favor ingrese un correo electrónico válido."; }
    } else {
        $correo = $original_correo; // Mantener correo original si no es admin
    }

    // Ubicación (Todos pueden editar)
    $provincia = isset($_POST["provincia"]) && $_POST["provincia"] !== '' ? trim($_POST["provincia"]) : $original_provincia;
    if ($provincia !== $original_provincia) {
        $campos_modificados['provincia'] = $original_provincia;
    }
    
    $distrito = isset($_POST["distrito"]) && $_POST["distrito"] !== '' ? trim($_POST["distrito"]) : $original_distrito;
    if ($distrito !== $original_distrito) {
        $campos_modificados['distrito'] = $original_distrito;
    }
    
    $corregimiento = isset($_POST["corregimiento"]) && $_POST["corregimiento"] !== '' ? trim($_POST["corregimiento"]) : $original_corregimiento;
    if ($corregimiento !== $original_corregimiento) {
        $campos_modificados['corregimiento'] = $original_corregimiento;
    }
    
    $calle = isset($_POST["calle"]) ? trim($_POST["calle"]) : $original_calle; // Puede ser vacío
    if ($calle !== $original_calle) {
        $campos_modificados['calle'] = $original_calle;
    }
    
    $casa = isset($_POST["casa"]) ? trim($_POST["casa"]) : $original_casa; // Puede ser vacío
    if ($casa !== $original_casa) {
        $campos_modificados['casa'] = $original_casa;
    }
    
    $comunidad = isset($_POST["comunidad"]) ? trim($_POST["comunidad"]) : $original_comunidad; // Puede ser vacío
    if ($comunidad !== $original_comunidad) {
        $campos_modificados['comunidad'] = $original_comunidad;
    }
    
    $nacionalidad = isset($_POST["nacionalidad"]) && $_POST["nacionalidad"] !== '' ? trim($_POST["nacionalidad"]) : $original_nacionalidad;
    if ($nacionalidad !== $original_nacionalidad) {
        $campos_modificados['nacionalidad'] = $original_nacionalidad;
    }

    // Información laboral (Solo admin puede editar)
    if ($is_admin) {
        $f_contra = isset($_POST["f_contra"]) && trim($_POST["f_contra"]) !== '' ? trim($_POST["f_contra"]) : $original_f_contra;
        if ($f_contra !== $original_f_contra) {
            $campos_modificados['f_contra'] = $original_f_contra;
        }
        
        $departamento = isset($_POST['departamento']) && $_POST['departamento'] !== '' ? str_pad(trim($_POST['departamento']), 2, '0', STR_PAD_LEFT) : $original_departamento;
        if ($departamento !== $original_departamento) {
            $campos_modificados['departamento'] = $original_departamento;
        }
        
        $cargo = isset($_POST['cargo']) && $_POST['cargo'] !== '' ? str_pad(trim($_POST['cargo']), 2, '0', STR_PAD_LEFT) : $original_cargo;
        if ($cargo !== $original_cargo) {
            $campos_modificados['cargo'] = $original_cargo;
        }
        
        $estado = isset($_POST["estado"]) && $_POST["estado"] !== '' ? $_POST["estado"] : $original_estado;
        if ($estado !== $original_estado) {
            $campos_modificados['estado'] = $original_estado;
        }
    } else {
        // Mantener valores originales si no es admin
        $f_contra = $original_f_contra;
        $departamento = $original_departamento;
        $cargo = $original_cargo;
        $estado = $original_estado;
    }

    // Verificar si la cédula nueva ya existe (solo si cambió y es admin)
    if ($is_admin && $cedula_nueva !== $cedula_original_post) {
        $sql_check = "SELECT cedula FROM empleados WHERE cedula = ?";
        if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt_check, "s", $cedula_nueva);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $error = "El nuevo número de cédula ('$cedula_nueva') ya existe en el sistema.";
            }
            mysqli_stmt_close($stmt_check);
        }
    }

    // Si no hay errores, actualizar el empleado
    if (empty($error)) {
        $sql_update = "UPDATE empleados SET
                        cedula=?, prefijo=?, tomo=?, asiento=?, nombre1=?, nombre2=?, apellido1=?, apellido2=?, apellidoc=?,
                        genero=?, estado_civil=?, tipo_sangre=?, usa_ac=?, f_nacimiento=?, celular=?, telefono=?, correo=?,
                        provincia=?, distrito=?, corregimiento=?, calle=?, casa=?, comunidad=?, nacionalidad=?,
                        f_contra=?, cargo=?, departamento=?, estado=?
                       WHERE cedula=?";

        if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
             mysqli_stmt_bind_param($stmt_update, "sssssssssiisissssssssssssssis",
                $cedula_nueva, $prefijo, $tomo, $asiento, $nombre1, $nombre2, $apellido1, $apellido2, $apellidoc,
                $genero, $estado_civil, $tipo_sangre, $usa_ac, $f_nacimiento, $celular, $telefono, $correo,
                $provincia, $distrito, $corregimiento, $calle, $casa, $comunidad, $nacionalidad,
                $f_contra, $cargo, $departamento, $estado,
                $cedula_original_post); // Condición WHERE usa la cédula original

            if (mysqli_stmt_execute($stmt_update)) {
                $success = "Información del empleado actualizada correctamente.";
                
                // Registrar auditoría si hay campos modificados
                if (!empty($campos_modificados) && !$is_admin) {
                    // Convertir el array de campos modificados a JSON
                    $json_modificados = json_encode($campos_modificados, JSON_UNESCAPED_UNICODE);
                    
                    // Insertar registro en la tabla de auditoría
                    $sql_audit = "INSERT INTO e_auditoria (cedula, editado, fecha) VALUES (?, ?, NOW())";
                    if ($stmt_audit = mysqli_prepare($conn, $sql_audit)) {
                        mysqli_stmt_bind_param($stmt_audit, "ss", $cedula_session, $json_modificados);
                        
                        if (!mysqli_stmt_execute($stmt_audit)) {
                            // No mostramos error al usuario, pero podemos registrarlo en un log
                            error_log("Error al guardar auditoría: " . mysqli_error($conn));
                        }
                        
                        mysqli_stmt_close($stmt_audit);
                    }
                }
                
                // Actualizar la cédula en la variable $cedula si cambió (para mostrar en el form)
                $cedula = $cedula_nueva;
                // Actualizar también las variables originales para que el form muestre los datos guardados
                $original_prefijo = $prefijo; $original_tomo = $tomo; $original_asiento = $asiento;
                $original_nombre1 = $nombre1; $original_nombre2 = $nombre2; $original_apellido1 = $apellido1; $original_apellido2 = $apellido2; $original_apellidoc = $apellidoc;
                $original_genero = $genero; $original_estado_civil = $estado_civil; $original_tipo_sangre = $tipo_sangre; $original_usa_ac = $usa_ac; $original_f_nacimiento = $f_nacimiento;
                $original_celular = $celular; $original_telefono = $telefono; $original_correo = $correo;
                $original_provincia = $provincia; $original_distrito = $distrito; $original_corregimiento = $corregimiento; $original_calle = $calle; $original_casa = $casa; $original_comunidad = $comunidad; $original_nacionalidad = $nacionalidad;
                $original_f_contra = $f_contra; $original_cargo = $cargo; $original_departamento = $departamento; $original_estado = $estado;

            } else {
                $error = "Error al actualizar el empleado: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_update);
        } else {
             $error = "Error al preparar la consulta de actualización: " . mysqli_error($conn);
        }
    }
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
     <h1 class="h2">Editar <?php echo $is_admin ? 'Empleado' : '(Mi Perfil)'; ?></h1>
     <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($is_admin): ?>
            <a href="list.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a la Lista
            </a>
        <?php else: ?>
             <a href="profile.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Mi Perfil
            </a>
        <?php endif; ?>
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

<?php if (!empty($cedula)): // Solo mostrar el formulario si se cargó un empleado ?>
<div class="card">
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?cedula=' . urlencode($cedula_original_get)); ?>" method="post" class="needs-validation" id="formularioEmpleado" novalidate>
            <!-- Campo oculto para guardar la cédula original -->
            <input type="hidden" name="cedula_original" value="<?php echo htmlspecialchars($cedula_original_get); ?>">

            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="mb-3">Información Personal</h4>
                </div>

                <!-- Datos de cédula (deshabilitado si no es admin) -->
                <div class="col-md-6 mb-3">
                    <label for="prefijo" class="form-label">Cédula *</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="prefijo" name="prefijo" value="<?php echo htmlspecialchars($prefijo); ?>" maxlength="6" required placeholder="8" <?php echo !$is_admin ? 'disabled' : ''; ?>>
                        <span class="input-group-text">-</span>
                        <input type="text" class="form-control" id="tomo" name="tomo" value="<?php echo htmlspecialchars($tomo); ?>" maxlength="4" required placeholder="1234" <?php echo !$is_admin ? 'disabled' : ''; ?>>
                        <span class="input-group-text">-</span>
                        <input type="text" class="form-control" id="asiento" name="asiento" value="<?php echo htmlspecialchars($asiento); ?>" maxlength="6" required placeholder="123456" <?php echo !$is_admin ? 'disabled' : ''; ?>>
                    </div>
                    <?php if (!$is_admin): ?>
                        <small class="form-text text-muted">La cédula no puede ser modificada.</small>
                    <?php else: ?>
                        <div class="form-text text-muted">Ingrese el número de cédula completo con el formato correcto.</div>
                        <div id="cedula-error" class="text-danger"></div> <!-- Error message container -->
                    <?php endif; ?>
                </div>

                <!-- Resto de campos de Información Personal (habilitados) -->
                 <div class="col-md-6 mb-3">
                    <label for="nombre1" class="form-label">Primer Nombre *</label>
                    <input type="text" class="form-control" id="nombre1" name="nombre1" value="<?php echo htmlspecialchars($nombre1); ?>" maxlength="25" required>
                    <div class="invalid-feedback">Este campo es requerido y solo debe contener letras, espacios o un guión.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nombre2" class="form-label">Segundo Nombre</label>
                    <input type="text" class="form-control" id="nombre2" name="nombre2" value="<?php echo htmlspecialchars($nombre2); ?>" maxlength="25">
                     <div class="invalid-feedback">Solo debe contener letras, espacios o un guión.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido1" class="form-label">Primer Apellido *</label>
                    <input type="text" class="form-control" id="apellido1" name="apellido1" value="<?php echo htmlspecialchars($apellido1); ?>" maxlength="25" required>
                     <div class="invalid-feedback">Este campo es requerido y solo debe contener letras, espacios o un guión.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido2" class="form-label">Segundo Apellido</label>
                    <input type="text" class="form-control" id="apellido2" name="apellido2" value="<?php echo htmlspecialchars($apellido2); ?>" maxlength="25">
                     <div class="invalid-feedback">Solo debe contener letras, espacios o un guión.</div>
                </div>
                 <div class="col-md-3 mb-3">
                    <label for="genero" class="form-label">Género *</label>
                    <select class="form-select" id="genero" name="genero" required onchange="mostrarApellidoCasada()">
                        <option value="" disabled>Seleccione...</option>
                        <option value="1" <?php if ($genero == 1) echo "selected"; ?>>Masculino</option>
                        <option value="0" <?php if ($genero == 0) echo "selected"; ?>>Femenino</option>
                    </select>
                    <div class="invalid-feedback">Seleccione un género.</div>
                </div>
                 <div class="col-md-3 mb-3">
                    <label for="estado_civil" class="form-label">Estado Civil *</label>
                    <select class="form-select" id="estado_civil" name="estado_civil" required onchange="mostrarApellidoCasada()">
                        <option value="" disabled>Seleccione...</option>
                        <option value="0" <?php if ($estado_civil == 0) echo "selected"; ?>>Soltero/a</option>
                        <option value="1" <?php if ($estado_civil == 1) echo "selected"; ?>>Casado/a</option>
                        <option value="2" <?php if ($estado_civil == 2) echo "selected"; ?>>Viudo/a</option>
                        <option value="3" <?php if ($estado_civil == 3) echo "selected"; ?>>Divorciado/a</option>
                    </select>
                    <div class="invalid-feedback">Seleccione un estado civil.</div>
                </div>
                 <!-- Campo de apellido de casada (inicialmente oculto) -->
                <div class="col-md-6" id="seccion_apellido_casada" style="display: <?php echo ($genero == 0 && $estado_civil == 1) ? 'block' : 'none'; ?>;">
                    <div class="mb-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="usa_ac" name="usa_ac" value="1" <?php if ($usa_ac == 1) echo "checked"; ?> onchange="toggleApellidoCasada()">
                            <label class="form-check-label" for="usa_ac">¿Usa apellido de casada?</label>
                        </div>
                        <div id="campo_apellido_casada" style="display: <?php echo ($usa_ac == 1) ? 'block' : 'none'; ?>;">
                            <label for="apellidoc" class="form-label">Apellido de Casada</label>
                            <input type="text" class="form-control" id="apellidoc" name="apellidoc" value="<?php echo htmlspecialchars($apellidoc); ?>" maxlength="25">
                            <small class="form-text text-muted">Ingrese el apellido del esposo</small>
                            <div class="invalid-feedback">Solo debe contener letras, espacios o un guión.</div>
                        </div>
                    </div>
                </div>
                 <div class="col-md-3 mb-3">
                    <label for="tipo_sangre" class="form-label">Tipo de Sangre *</label>
                    <select class="form-select" id="tipo_sangre" name="tipo_sangre" required>
                        <option value="" disabled>Seleccione...</option>
                        <option value="A+" <?php if ($tipo_sangre == "A+") echo "selected"; ?>>A+</option>
                        <option value="A-" <?php if ($tipo_sangre == "A-") echo "selected"; ?>>A-</option>
                        <option value="B+" <?php if ($tipo_sangre == "B+") echo "selected"; ?>>B+</option>
                        <option value="B-" <?php if ($tipo_sangre == "B-") echo "selected"; ?>>B-</option>
                        <option value="AB+" <?php if ($tipo_sangre == "AB+") echo "selected"; ?>>AB+</option>
                        <option value="AB-" <?php if ($tipo_sangre == "AB-") echo "selected"; ?>>AB-</option>
                        <option value="O+" <?php if ($tipo_sangre == "O+") echo "selected"; ?>>O+</option>
                        <option value="O-" <?php if ($tipo_sangre == "O-") echo "selected"; ?>>O-</option>
                    </select>
                    <div class="invalid-feedback">Seleccione un tipo de sangre.</div>
                </div>
                 <div class="col-md-3 mb-3">
                    <label for="f_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                    <input type="date" class="form-control" id="f_nacimiento" name="f_nacimiento" value="<?php echo htmlspecialchars($f_nacimiento); ?>" max="<?php echo $maxDate; ?>" required>
                    <div class="invalid-feedback">Debe ser mayor de edad (18 años mínimo).</div>
                </div>
                 <div class="col-md-3 mb-3">
                    <label for="nacionalidad" class="form-label">Nacionalidad *</label>
                    <select class="form-select nacionalidad-select" id="nacionalidad" name="nacionalidad" required>
                        <?php echo loadNacionalidades($conn, $nacionalidad); // Asegúrate que esta función exista y funcione ?>
                    </select>
                    <div class="invalid-feedback">Seleccione una nacionalidad.</div>
                     <div id="flag-container-nacionalidad" class="flag-container"></div> <!-- Contenedor para la bandera -->
                </div>
            </div>

            <!-- Información de Contacto (habilitados, excepto correo para empleados) -->
            <div class="row mb-4">
                 <div class="col-12"><h4 class="mb-3">Información de Contacto</h4></div>
                 <div class="col-md-4 mb-3">
                    <label for="celular" class="form-label">Celular *</label>
                    <input type="text" class="form-control" id="celular" name="celular" value="<?php echo htmlspecialchars($celular); ?>" maxlength="8" required>
                    <div class="invalid-feedback">Ingrese un número de celular válido (máximo 8 dígitos).</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="telefono" class="form-label">Teléfono Fijo</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" maxlength="7">
                    <div class="invalid-feedback">Ingrese un número de teléfono válido (máximo 7 dígitos).</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="correo" class="form-label">Correo Electrónico *</label>
                    <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" maxlength="40" required <?php echo !$is_admin ? 'disabled' : ''; ?>>
                    <?php if (!$is_admin): ?>
                        <small class="form-text text-muted">El correo electrónico no puede ser modificado.</small>
                    <?php else: ?>
                        <div class="invalid-feedback">Ingrese un correo electrónico válido.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información de Ubicación (habilitados) -->
            <div class="row mb-4">
                 <div class="col-12"><h4 class="mb-3">Información de Ubicación</h4></div>
                 <div class="col-md-4 mb-3">
                    <label for="provincia" class="form-label">Provincia *</label>
                    <select class="form-select" id="provincia" name="provincia" required>
                        <option value="">Seleccione una provincia...</option>
                        <?php
                        if (isset($result_provincias) && $result_provincias) {
                            mysqli_data_seek($result_provincias, 0);
                            while ($row_p = mysqli_fetch_assoc($result_provincias)) {
                                $selected = ($provincia == $row_p['codigo_provincia']) ? 'selected' : '';
                                echo "<option value='" . $row_p['codigo_provincia'] . "' $selected>" . htmlspecialchars($row_p['nombre_provincia']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <div class="invalid-feedback">Por favor seleccione una provincia.</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="distrito" class="form-label">Distrito *</label>
                    <select class="form-select" id="distrito" name="distrito" required <?php echo empty($provincia) ? 'disabled' : ''; ?>>
                        <option value="">Seleccione un distrito...</option>
                        <!-- Opciones se cargan con JS -->
                    </select>
                    <div class="invalid-feedback">Por favor seleccione un distrito.</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="corregimiento" class="form-label">Corregimiento *</label>
                    <select class="form-select" id="corregimiento" name="corregimiento" required <?php echo empty($distrito) ? 'disabled' : ''; ?>>
                        <option value="">Seleccione un corregimiento...</option>
                         <!-- Opciones se cargan con JS -->
                    </select>
                    <div class="invalid-feedback">Por favor seleccione un corregimiento.</div>
                </div>
                 <div class="col-md-4 mb-3">
                    <label for="calle" class="form-label">Calle</label>
                    <input type="text" class="form-control" id="calle" name="calle" value="<?php echo htmlspecialchars($calle); ?>" maxlength="30">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="casa" class="form-label">Casa/Apto</label>
                    <input type="text" class="form-control" id="casa" name="casa" value="<?php echo htmlspecialchars($casa); ?>" maxlength="10">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="comunidad" class="form-label">Comunidad/Urbanización</label>
                    <input type="text" class="form-control" id="comunidad" name="comunidad" value="<?php echo htmlspecialchars($comunidad); ?>" maxlength="25">
                </div>
            </div>

            <!-- Información Laboral (deshabilitado si no es admin) -->
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="mb-3">Información Laboral</h4>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="f_contra" class="form-label">Fecha de Contratación *</label>
                    <input type="date" class="form-control" id="f_contra" name="f_contra" value="<?php echo htmlspecialchars($f_contra); ?>" required <?php echo !$is_admin ? 'disabled' : ''; ?>>
                     <?php if (!$is_admin): ?>
                        <small class="form-text text-muted">Campo no modificable.</small>
                    <?php else: ?>
                        <div class="invalid-feedback">Este campo es requerido.</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="departamento" class="form-label">Departamento *</label>
                    <select class="form-select" id="departamento" name="departamento" required <?php echo !$is_admin ? 'disabled' : ''; ?>>
                        <option value="">Seleccione...</option>
                         <?php
                        if (isset($result_departamentos) && $result_departamentos) {
                            mysqli_data_seek($result_departamentos, 0);
                            while ($row_d = mysqli_fetch_assoc($result_departamentos)) {
                                $selected = ($departamento == $row_d['codigo']) ? 'selected' : '';
                                echo "<option value='" . $row_d['codigo'] . "' $selected>" . htmlspecialchars($row_d['nombre']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                     <?php if (!$is_admin): ?>
                        <small class="form-text text-muted">Campo no modificable.</small>
                    <?php else: ?>
                        <div class="invalid-feedback">Este campo es requerido.</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="cargo" class="form-label">Cargo *</label>
                    <select class="form-select" id="cargo" name="cargo" required <?php echo (empty($departamento) || !$is_admin) ? 'disabled' : ''; ?>>
                        <option value="">Seleccione un departamento primero...</option>
                         <!-- Opciones se cargan con JS -->
                    </select>
                     <?php if (!$is_admin): ?>
                        <small class="form-text text-muted">Campo no modificable.</small>
                    <?php else: ?>
                        <div class="invalid-feedback">Este campo es requerido.</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="estado" class="form-label">Estado *</label>
                    <select class="form-select" id="estado" name="estado" required <?php echo !$is_admin ? 'disabled' : ''; ?>>
                        <option value="" disabled>Seleccione...</option>
                        <option value="1" <?php if ($estado == 1) echo "selected"; ?>>Activo</option>
                        <option value="0" <?php if ($estado == 0) echo "selected"; ?>>Inactivo</option>
                    </select>
                     <?php if (!$is_admin): ?>
                        <small class="form-text text-muted">Campo no modificable.</small>
                    <?php else: ?>
                        <div class="invalid-feedback">Este campo es requerido.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- NO incluir campos de contraseña aquí -->

            <div class="text-center mt-4">
                <button type="button" id="btnPreGuardar" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                 <a href="<?php echo $is_admin ? 'list.php' : 'profile.php'; ?>" class="btn btn-secondary btn-lg ms-2">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalConfirmacionLabel">
                    <i class="fas fa-save me-2"></i> Confirmar Cambios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-user-edit fa-4x text-primary mb-3"></i>
                    <h4>¿Está seguro que desea guardar los cambios realizados?</h4>
                    <p class="text-muted">Al confirmar, los datos del empleado serán actualizados en el sistema.</p>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-id-card fa-2x text-secondary me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Cédula</h6>
                                <div id="confirmCedula" class="fw-bold"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-user fa-2x text-secondary me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Nombre</h6>
                                <div id="confirmNombre" class="fw-bold"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" id="btnConfirmarGuardado" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Confirmar Guardado
                </button>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
    <?php if (empty($error)): // Si no hay error pero tampoco cédula ?>
        <div class="alert alert-warning">No se ha especificado un empleado para editar.</div>
    <?php endif; ?>
<?php endif; ?>


<script>
// Función para mostrar/ocultar la sección de apellido de casada
function mostrarApellidoCasada() {
    const genero = document.getElementById('genero').value;
    const estadoCivil = document.getElementById('estado_civil').value;
    const seccionApellidoCasada = document.getElementById('seccion_apellido_casada');
    const campoApellidoCasada = document.getElementById('campo_apellido_casada');
    const inputApellidoCasada = document.getElementById('apellidoc');
    const checkboxUsaAc = document.getElementById('usa_ac');

    if (genero === '0' && estadoCivil === '1') {
        seccionApellidoCasada.style.display = 'block';
        toggleApellidoCasada(); // Llama a la función para mostrar/ocultar el input basado en el checkbox
    } else {
        seccionApellidoCasada.style.display = 'none';
        if (campoApellidoCasada) campoApellidoCasada.style.display = 'none';
        if (inputApellidoCasada) inputApellidoCasada.value = ''; // Limpiar valor
        if (checkboxUsaAc) checkboxUsaAc.checked = false; // Desmarcar checkbox
    }
}

// Función para mostrar/ocultar el campo de apellido de casada basado en el checkbox
function toggleApellidoCasada() {
    const usaApellidoCasada = document.getElementById('usa_ac').checked;
    const campoApellidoCasada = document.getElementById('campo_apellido_casada');
    const inputApellidoCasada = document.getElementById('apellidoc');

    if (campoApellidoCasada) {
        if (usaApellidoCasada) {
            campoApellidoCasada.style.display = 'block';
        } else {
            campoApellidoCasada.style.display = 'none';
            if (inputApellidoCasada) inputApellidoCasada.value = ''; // Limpiar valor si se desmarca
        }
    }
}

// Función para mostrar mensajes de error en campos específicos
function mostrarErrorCampo(campo, mensaje) {
    campo.setCustomValidity(mensaje);
    
    // Mostrar mensaje de error debajo del campo
    let errorElement = campo.nextElementSibling;
    
    // Si el siguiente elemento no es un div de mensaje de error, crear uno nuevo
    if (!errorElement || !errorElement.classList.contains('invalid-feedback')) {
        errorElement = document.createElement('div');
        errorElement.classList.add('invalid-feedback');
        campo.parentNode.insertBefore(errorElement, campo.nextSibling);
    }
    
    errorElement.textContent = mensaje;
    campo.classList.add('is-invalid');
    
    // Limpiar el error después de 3 segundos
    setTimeout(function() {
        campo.classList.remove('is-invalid');
        campo.setCustomValidity('');
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    const isAdmin = <?php echo json_encode($is_admin); ?>;

    // --- Validaciones de Cédula (Solo para Admin) ---
    if (isAdmin) {
        const prefijoInput = document.getElementById('prefijo');
        const tomoInput = document.getElementById('tomo');
        const asientoInput = document.getElementById('asiento');
        const cedulaError = document.getElementById('cedula-error');

        if (prefijoInput && tomoInput && asientoInput && cedulaError) {
            prefijoInput.addEventListener('keydown', function(e) {
                // Permitir teclas de navegación y edición
                if (e.key === 'Backspace' || e.key === 'Delete' || 
                    e.key === 'ArrowLeft' || e.key === 'ArrowRight' || 
                    e.key === 'Tab' || e.key === 'Enter' || 
                    e.ctrlKey || e.metaKey) {
                    return;
                }
                
                // Bloquear espacio y caracteres especiales
                if (e.key === ' ' || 
                    e.key === '+' || e.key === '´' || e.key === '`' || 
                    e.key === '¨' || e.key === '^' || e.key === '~' || 
                    e.key === '!' || e.key === '@' || e.key === '#' || 
                    e.key === '$' || e.key === '%' || e.key === '&' || 
                    e.key === '*' || e.key === '(' || e.key === ')' || 
                    e.key === '-' || e.key === '_' || e.key === '=' || 
                    e.key === '[' || e.key === ']' || e.key === '{' || 
                    e.key === '}' || e.key === '|' || e.key === '\\' || 
                    e.key === ';' || e.key === ':' || e.key === '\'' || 
                    e.key === '"' || e.key === ',' || e.key === '.' || 
                    e.key === '<' || e.key === '>' || e.key === '/' || 
                    e.key === '?') {
                    e.preventDefault();
                    cedulaError.textContent = 'Carácter no permitido en la cédula';
                    return;
                }
                
                // Verificar si es una letra minúscula y convertirla a mayúscula
                let tecla = e.key;
                if (/^[a-z]$/.test(tecla)) {
                    e.preventDefault();
                    tecla = tecla.toUpperCase();
                    
                    // Añadir la letra mayúscula al valor actual
                    const currentValue = this.value;
                    const newValueWithUppercase = currentValue + tecla;
                    
                    // Expresiones regulares para validar el prefijo
                    const validPrefixes = [
                        /^[PE]$/, // P, PE
                        /^E$/, // E
                        /^N$/, // N
                        /^[23456789]$/, // Dígitos del 2-9
                        /^[23456789][AP]$/, // Dígito 2-9 seguido de A o P
                        /^1[0123]?$/, // Número del 1-13
                        /^1[0123]?[AP]$/, // 1-13 seguido de A o P
                        /^[23456789](AV|PI)?$/, // 2-9 seguido opcionalmente de AV o PI
                        /^1[0123]?(AV|PI)?$/ // 1-13 seguido opcionalmente de AV o PI
                    ];
                    
                    // Verificar si el nuevo valor con mayúscula es válido
                    const isValidUppercase = validPrefixes.some(regex => regex.test(newValueWithUppercase));
                    
                    if (isValidUppercase) {
                        // Si es válido, actualizar el campo con la letra mayúscula
                        this.value = newValueWithUppercase;
                        cedulaError.textContent = '';
                    } else {
                        cedulaError.textContent = 'Prefijo de cédula inválido. Ejemplos válidos: 8, PE, E, N, 8A, 8AV, 13, etc.';
                    }
                    return;
                }
                
                const currentValue = this.value;
                const newValue = currentValue + e.key;
                
                // Expresiones regulares para validar el prefijo según las reglas panameñas
                const validPrefixes = [
                    /^[PE]$/, // P, PE
                    /^E$/, // E
                    /^N$/, // N
                    /^[23456789]$/, // Dígitos del 2-9
                    /^[23456789][AP]$/, // Dígito 2-9 seguido de A o P
                    /^1[0123]?$/, // Número del 1-13
                    /^1[0123]?[AP]$/, // 1-13 seguido de A o P
                    /^[23456789](AV|PI)?$/, // 2-9 seguido opcionalmente de AV o PI
                    /^1[0123]?(AV|PI)?$/ // 1-13 seguido opcionalmente de AV o PI
                ];
                
                // Verificar si el nuevo valor cumple con alguna de las expresiones válidas
                const isValid = validPrefixes.some(regex => regex.test(newValue));
                
                if (!isValid) {
                    e.preventDefault();
                    cedulaError.textContent = 'Prefijo de cédula inválido. Ejemplos válidos: 8, PE, E, N, 8A, 8AV, 13, etc.';
                } else {
                    cedulaError.textContent = '';
                }
            });
            
            // Segunda parte de la cédula (tomo)
            tomoInput.addEventListener('keydown', function(e) {
                // Permitir teclas de navegación y edición
                if (e.key === 'Backspace' || e.key === 'Delete' || 
                    e.key === 'ArrowLeft' || e.key === 'ArrowRight' || 
                    e.key === 'Tab' || e.key === 'Enter' || 
                    e.ctrlKey || e.metaKey) {
                    return;
                }
                
                // Bloquear cualquier caracter que no sea un dígito
                if (!/^\d$/.test(e.key) || (this.value.length >= 4) || 
                    e.key === ' ' || e.key === '+' || e.key === '´' || 
                    e.key === '`' || e.key === '¨' || e.key === '^' || 
                    e.key === '~' || e.key.length > 1) {
                    e.preventDefault();
                    cedulaError.textContent = 'El tomo debe contener solo dígitos numéricos (1-4 dígitos)';
                    return;
                }
                
                cedulaError.textContent = '';
            });
            
            // Tercera parte de la cédula (asiento)
            asientoInput.addEventListener('keydown', function(e) {
                // Permitir teclas de navegación y edición
                if (e.key === 'Backspace' || e.key === 'Delete' || 
                    e.key === 'ArrowLeft' || e.key === 'ArrowRight' || 
                    e.key === 'Tab' || e.key === 'Enter' || 
                    e.ctrlKey || e.metaKey) {
                    return;
                }
                
                // Bloquear cualquier caracter que no sea un dígito
                if (!/^\d$/.test(e.key) || (this.value.length >= 6) || 
                    e.key === ' ' || e.key === '+' || e.key === '´' || 
                    e.key === '`' || e.key === '¨' || e.key === '^' || 
                    e.key === '~' || e.key.length > 1) {
                    e.preventDefault();
                    cedulaError.textContent = 'El asiento debe contener solo dígitos numéricos (1-6 dígitos)';
                    return;
                }
                
                cedulaError.textContent = '';
            });
            
            // Agregar validación en el evento input para limpiar caracteres no válidos
            prefijoInput.addEventListener('input', function() {
                // Limpiar caracteres no válidos si se pegaron
                const cleanValue = this.value.replace(/[^A-Z0-9]/g, '');
                if (cleanValue !== this.value) {
                    this.value = cleanValue;
                    cedulaError.textContent = 'Se han eliminado caracteres no permitidos';
                }
            });
            
            tomoInput.addEventListener('input', function() {
                // Limpiar caracteres no válidos si se pegaron
                const cleanValue = this.value.replace(/\D/g, '');
                if (cleanValue !== this.value) {
                    this.value = cleanValue;
                    cedulaError.textContent = 'El tomo debe contener solo dígitos';
                }
            });
            
            asientoInput.addEventListener('input', function() {
                // Limpiar caracteres no válidos si se pegaron
                const cleanValue = this.value.replace(/\D/g, '');
                if (cleanValue !== this.value) {
                    this.value = cleanValue;
                    cedulaError.textContent = 'El asiento debe contener solo dígitos';
                }
            });
        }
    }

    // --- Validación para campos de nombres y apellidos (Siempre aplica) ---
    const camposTexto = document.querySelectorAll('#nombre1, #nombre2, #apellido1, #apellido2, #apellidoc');
    camposTexto.forEach(function(campo) {
        // Validación al presionar una tecla
        campo.addEventListener('keydown', function(e) {
            // Permitir teclas de navegación y edición
            if (e.key === 'Backspace' || e.key === 'Delete' || 
                e.key === 'ArrowLeft' || e.key === 'ArrowRight' || 
                e.key === 'Tab' || e.key === 'Enter' || 
                e.ctrlKey || e.metaKey) {
                return;
            }
            
            // Validar guión: solo permitir si ya hay al menos una letra
            if (e.key === '-') {
                if (this.value.length === 0 || !/[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]/.test(this.value)) {
                    e.preventDefault();
                    mostrarErrorCampo(this, 'El guión solo puede usarse después de una letra');
                    return;
                }
                
                // Verificar que no haya otro guión ya
                if (this.value.includes('-')) {
                    e.preventDefault();
                    mostrarErrorCampo(this, 'Solo se permite un guión');
                    return;
                }
                
                // Si pasó las validaciones anteriores, permitir el guión
                return;
            }
            
            // Solo permitir letras y espacios (no números ni caracteres especiales)
            if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]$/.test(e.key)) {
                e.preventDefault();
                mostrarErrorCampo(this, 'Solo se permiten letras, espacios y un guión');
                return;
            }
            
            // Si llega aquí, es un carácter válido
            this.setCustomValidity('');
        });
        
        // Validar después de pegar contenido
        campo.addEventListener('input', function() {
            // Reemplazar cualquier caracter no permitido excepto letras, espacios y guiones
            const valorLimpio = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]/g, '');
            
            // Si había caracteres no permitidos, limpiarlos
            if (valorLimpio !== this.value) {
                this.value = valorLimpio;
                mostrarErrorCampo(this, 'Se han eliminado caracteres no permitidos');
            }
            
            // Validar que el guión solo aparezca después de letras
            if (this.value.startsWith('-')) {
                this.value = this.value.substring(1);
                mostrarErrorCampo(this, 'El guión solo puede usarse después de una letra');
            }
            
            // Verificar que no haya más de un guión
            const guiones = (this.value.match(/-/g) || []).length;
            if (guiones > 1) {
                // Dejar solo el primer guión
                const partes = this.value.split('-');
                this.value = partes[0] + '-' + partes.slice(1).join('');
                mostrarErrorCampo(this, 'Solo se permite un guión');
            }
        });
    });

    // --- Validación para campos de solo números (celular y teléfono) (Siempre aplica) ---
    const camposNumeros = document.querySelectorAll('#celular, #telefono');
    camposNumeros.forEach(function(campo) {
        // Validación al presionar una tecla
        campo.addEventListener('keydown', function(e) {
            // Permitir teclas de navegación y edición
            if (e.key === 'Backspace' || e.key === 'Delete' || 
                e.key === 'ArrowLeft' || e.key === 'ArrowRight' || 
                e.key === 'Tab' || e.key === 'Enter' || 
                e.ctrlKey || e.metaKey) {
                return;
            }
            
            // Solo permitir dígitos
            if (!/^\d$/.test(e.key)) {
                e.preventDefault();
                mostrarErrorCampo(this, 'Solo se permiten números');
                return;
            }
            
            // Validar longitud máxima
            const maxLength = this.id === 'celular' ? 8 : 7;
            if (this.value.length >= maxLength) {
                e.preventDefault();
                mostrarErrorCampo(this, `Máximo ${maxLength} dígitos`);
                return;
            }
        });
        
        // Validar después de pegar contenido
        campo.addEventListener('input', function() {
            // Limpiar caracteres no numéricos
            const valorLimpio = this.value.replace(/\D/g, '');
            
            // Si había caracteres no numéricos, limpiarlos
            if (valorLimpio !== this.value) {
                this.value = valorLimpio;
                mostrarErrorCampo(this, 'Solo se permiten números');
            }
            
            // Validar longitud máxima
            const maxLength = this.id === 'celular' ? 8 : 7;
            if (this.value.length > maxLength) {
                this.value = this.value.substring(0, maxLength);
                mostrarErrorCampo(this, `Máximo ${maxLength} dígitos`);
            }
        });
    });

    // --- Carga dinámica de Selects (Siempre aplica) ---
    const provinciaSelect = document.getElementById('provincia');
    const distritoSelect = document.getElementById('distrito');
    const corregimientoSelect = document.getElementById('corregimiento');
    const departamentoSelect = document.getElementById('departamento');
    const cargoSelect = document.getElementById('cargo');

    // Función para cargar distritos (adaptada para edit.php)
    function cargarDistritos(provinciaId, distritoSeleccionado = null) {
        // Mostrar indicador de carga
        distritoSelect.innerHTML = '<option value="">Cargando distritos...</option>';
        
        // Hacer la petición AJAX
        fetch('get_distritos.php?provincia=' + provinciaId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error de red al solicitar distritos');
                }
                return response.json();
            })
            .then(data => {
                // Resetear contenido
                distritoSelect.innerHTML = '<option value="">Seleccione un distrito...</option>';
                
                // Añadir opciones al selector
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(distrito => {
                        const option = document.createElement('option');
                        option.value = distrito.codigo_distrito;
                        option.textContent = distrito.nombre_distrito;
                        distritoSelect.appendChild(option);
                    });
                    
                    // Habilitar el selector
                    distritoSelect.disabled = false;
                    
                    // Si hay un valor previamente seleccionado
                    if (distritoSeleccionado) {
                        distritoSelect.value = distritoSeleccionado;
                        cargarCorregimientos(provinciaId, distritoSeleccionado, '<?php echo $corregimiento; ?>');
                    }
                } else {
                    console.log('No se encontraron distritos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                distritoSelect.innerHTML = '<option value="">Error al cargar distritos</option>';
            });
    }

    // Función para cargar corregimientos (adaptada para edit.php)
    function cargarCorregimientos(provinciaId, distritoId, corregimientoSeleccionado = null) {
        // Mostrar indicador de carga
        corregimientoSelect.innerHTML = '<option value="">Cargando corregimientos...</option>';
        
        // Hacer la petición AJAX
        fetch('get_corregimientos.php?provincia=' + provinciaId + '&distrito=' + distritoId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error de red al solicitar corregimientos');
                }
                return response.json();
            })
            .then(data => {
                // Resetear contenido
                corregimientoSelect.innerHTML = '<option value="">Seleccione un corregimiento...</option>';
                
                // Añadir opciones al selector
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(corregimiento => {
                        const option = document.createElement('option');
                        option.value = corregimiento.codigo_corregimiento;
                        option.textContent = corregimiento.nombre_corregimiento;
                        corregimientoSelect.appendChild(option);
                    });
                    
                    // Habilitar el selector
                    corregimientoSelect.disabled = false;
                    
                    // Si hay un valor previamente seleccionado
                    if (corregimientoSeleccionado) {
                        corregimientoSelect.value = corregimientoSeleccionado;
                    }
                } else {
                    console.log('No se encontraron corregimientos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                corregimientoSelect.innerHTML = '<option value="">Error al cargar corregimientos</option>';
            });
    }

    // Función para cargar cargos (adaptada para edit.php)
    function cargarCargos(departamentoId, cargoSeleccionado = null) {
        // Mostrar indicador de carga
        cargoSelect.innerHTML = '<option value="">Cargando cargos...</option>';
        
        // Hacer la petición AJAX
        fetch('get_cargos.php?departamento=' + departamentoId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error de red al solicitar cargos');
                }
                return response.json();
            })
            .then(data => {
                // Resetear contenido
                cargoSelect.innerHTML = '<option value="">Seleccione un cargo...</option>';
                
                // Añadir opciones al selector
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(cargo => {
                        const option = document.createElement('option');
                        option.value = cargo.codigo;
                        option.textContent = cargo.nombre;
                        cargoSelect.appendChild(option);
                    });
                    
                    // Habilitar el selector sólo si es admin
                    cargoSelect.disabled = !isAdmin;
                    
                    // Si hay un valor previamente seleccionado
                    if (cargoSeleccionado) {
                        cargoSelect.value = cargoSeleccionado;
                    }
                } else {
                    console.log('No se encontraron cargos para este departamento');
                    cargoSelect.innerHTML = '<option value="">No hay cargos disponibles</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                cargoSelect.innerHTML = '<option value="">Error al cargar cargos</option>';
            });
    }

    // Event Listeners para selects dependientes (Siempre aplica)
    if (provinciaSelect) {
        provinciaSelect.addEventListener('change', function() {
            // Resetear y deshabilitar selectores dependientes
            distritoSelect.innerHTML = '<option value="">Seleccione un distrito...</option>';
            corregimientoSelect.innerHTML = '<option value="">Seleccione un corregimiento...</option>';
            distritoSelect.disabled = true;
            corregimientoSelect.disabled = true;
            
            // Si hay un valor seleccionado, cargar distritos
            if (this.value) {
                cargarDistritos(this.value);
            }
        });
    }
    
    if (distritoSelect) {
        distritoSelect.addEventListener('change', function() {
            // Resetear y deshabilitar selector de corregimiento
            corregimientoSelect.innerHTML = '<option value="">Seleccione un corregimiento...</option>';
            corregimientoSelect.disabled = true;
            
            // Si hay un valor seleccionado, cargar corregimientos
            if (this.value) {
                cargarCorregimientos(provinciaSelect.value, this.value);
            }
        });
    }
    
    if (departamentoSelect) {
        departamentoSelect.addEventListener('change', function() {
            if (isAdmin) { // Solo cargar si es admin
                // Resetear y deshabilitar selector de cargo
                cargoSelect.innerHTML = '<option value="">Seleccione un cargo...</option>';
                cargoSelect.disabled = true;
                
                // Si hay un valor seleccionado, cargar cargos
                if (this.value) {
                    cargarCargos(this.value);
                }
            }
        });
    }

    // Carga inicial de datos dependientes si existen valores preseleccionados (Siempre aplica)
    const provinciaInicial = '<?php echo $provincia; ?>';
    const distritoInicial = '<?php echo $distrito; ?>';
    const corregimientoInicial = '<?php echo $corregimiento; ?>';
    const departamentoInicial = '<?php echo $departamento; ?>';
    const cargoInicial = '<?php echo $cargo; ?>';

    if (provinciaInicial && provinciaSelect) {
        cargarDistritos(provinciaInicial, distritoInicial);
    }
    if (departamentoInicial && departamentoSelect) {
        cargarCargos(departamentoInicial, cargoInicial);
    }

    // --- Modal de Confirmación (Siempre aplica) ---
    const formulario = document.getElementById('formularioEmpleado');
    const btnPreGuardar = document.getElementById('btnPreGuardar');
    const btnConfirmarGuardado = document.getElementById('btnConfirmarGuardado');
    const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmacion'));

    if(btnPreGuardar && formulario && modalConfirmacion && btnConfirmarGuardado) {
        btnPreGuardar.addEventListener('click', function() {
            // Validar formulario antes de mostrar modal
            if (formulario.checkValidity()) {
                // Preparar el resumen para el modal (si lo necesitas)
                if (document.getElementById('confirmCedula') && document.getElementById('confirmNombre')) {
                    const prefijo = document.getElementById('prefijo').value;
                    const tomo = document.getElementById('tomo').value;
                    const asiento = document.getElementById('asiento').value;
                    const cedula = `${prefijo}-${tomo}-${asiento}`;
                    
                    const nombre1 = document.getElementById('nombre1').value;
                    const apellido1 = document.getElementById('apellido1').value;
                    
                    document.getElementById('confirmCedula').textContent = cedula;
                    document.getElementById('confirmNombre').textContent = `${nombre1} ${apellido1}`;
                }
                
                modalConfirmacion.show();
            } else {
                // Si no es válido, activar las validaciones visuales
                formulario.classList.add('was-validated');
            }
        });

        btnConfirmarGuardado.addEventListener('click', function() {
            modalConfirmacion.hide();
            // Re-enable fields before submit ONLY IF backend doesn't handle disabled fields
            // if (!isAdmin) {
            //     document.getElementById('correo').disabled = false;
            //     document.getElementById('f_contra').disabled = false;
            //     // etc. for other disabled fields
            // }
            formulario.submit();
        });
    }

    // Inicializar visualización de apellido de casada (Siempre aplica)
    mostrarApellidoCasada();

    // Inicializar bandera de nacionalidad (Siempre aplica)
    const selectNacionalidad = document.getElementById('nacionalidad');
    if (selectNacionalidad) {
        mostrarBanderaNacionalidad(selectNacionalidad); // Llama a la función del nacionalidades.js
        selectNacionalidad.addEventListener('change', function() {
            mostrarBanderaNacionalidad(this);
        });
    }

    // Validar fecha de nacimiento (Siempre aplica)
    const inputFechaNacimiento = document.getElementById('f_nacimiento');
    if (inputFechaNacimiento) {
        inputFechaNacimiento.addEventListener('input', function() {
            const fechaNacimiento = new Date(this.value);
            const hoy = new Date();
            let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
            const m = hoy.getMonth() - fechaNacimiento.getMonth();

            if (m < 0 || (m === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                edad--;
            }

            if (edad < 18) {
                this.setCustomValidity('Debe ser mayor de edad (18 años mínimo)');
            } else {
                this.setCustomValidity('');
            }
        });
        // Trigger validation on load in case date is pre-filled
        inputFechaNacimiento.dispatchEvent(new Event('input'));
    }
});

// Función para mostrar bandera (de nacionalidades.js, asegúrate que esté incluida)
function mostrarBanderaNacionalidad(select) {
    // Obtener el contenedor de la bandera
    const flagContainer = document.getElementById('flag-container-nacionalidad');
    
    // Si no hay contenedor o no hay valor seleccionado, salir
    if (!flagContainer || !select.value) {
        return;
    }
    
    // Obtener el código de país seleccionado
    const countryCode = select.value.toLowerCase();
    
    
}
</script>

<?php
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php";
?>
