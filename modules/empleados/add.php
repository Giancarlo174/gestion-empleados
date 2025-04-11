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
require_once "load_nacionalidades.php";

// Definir variables e inicializar con valores vacíos
$prefijo = $tomo = $asiento = $cedula = $nombre1 = $nombre2 = $apellido1 = $apellido2 = $apellidoc = "";
$genero = $estado_civil = $tipo_sangre = $usa_ac = $f_nacimiento = $celular = $telefono = $correo = "";
$provincia = $distrito = $corregimiento = $calle = $casa = $comunidad = $nacionalidad = $f_contra = $cargo = $departamento = $estado = "";
$error = "";
$success = "";

// Calcular fecha máxima para ser mayor de edad (18 años atrás desde hoy)
$maxDate = date('Y-m-d', strtotime('-18 years'));

// Cargar listas necesarias - Mover estas consultas antes de intentar usarlas
// Nacionalidades
$query_nacionalidades = "SELECT * FROM nacionalidad ORDER BY pais";
$result_nacionalidades = mysqli_query($conn, $query_nacionalidades);
if (!$result_nacionalidades) {
    $error = "Error al cargar nacionalidades: " . mysqli_error($conn);
}

// Provincias
$query_provincias = "SELECT * FROM provincia ORDER BY nombre_provincia";
$result_provincias = mysqli_query($conn, $query_provincias);
if (!$result_provincias) {
    $error = "Error al cargar provincias: " . mysqli_error($conn);
}

// Departamentos
$query_departamentos = "SELECT * FROM departamento ORDER BY nombre";
$result_departamentos = mysqli_query($conn, $query_departamentos);
if (!$result_departamentos) {
    $error = "Error al cargar departamentos: " . mysqli_error($conn);
}

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar datos del formulario
    
    // Datos de cédula
    $prefijo = trim($_POST["prefijo"]);
    $tomo = trim($_POST["tomo"]);
    $asiento = trim($_POST["asiento"]);
    
    // Construir la cédula completa
    $cedula = $prefijo . "-" . $tomo . "-" . $asiento;
    
    // Datos básicos
    $nombre1 = trim($_POST["nombre1"]);
    $nombre2 = trim($_POST["nombre2"]);
    $apellido1 = trim($_POST["apellido1"]);
    $apellido2 = trim($_POST["apellido2"]);
    $apellidoc = isset($_POST["apellidoc"]) ? trim($_POST["apellidoc"]) : "";
    $genero = $_POST["genero"];
    $estado_civil = $_POST["estado_civil"];
    $tipo_sangre = trim($_POST["tipo_sangre"]);
    if (empty($tipo_sangre)) {
        $error = "Por favor seleccione un tipo de sangre.";
    }
    $usa_ac = isset($_POST["usa_ac"]) ? 1 : 0;
    $f_nacimiento = trim($_POST["f_nacimiento"]);
    
    // Validar mayoría de edad
    $birthDate = new DateTime($f_nacimiento);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    
    if ($age < 18) {
        $error = "El empleado debe ser mayor de edad (al menos 18 años).";
    }
    
    // Contacto - Validar formatos
    $celular = trim($_POST["celular"]);
    if (!empty($celular) && (!is_numeric($celular) || strlen($celular) > 8)) {
        $error = "El celular debe ser un número de máximo 8 dígitos.";
    }
    
    $telefono = trim($_POST["telefono"]);
    if (!empty($telefono) && (!is_numeric($telefono) || strlen($telefono) > 7)) {
        $error = "El teléfono debe ser un número de máximo 7 dígitos.";
    }
    
    $correo = trim($_POST["correo"]);
    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor ingrese un correo electrónico válido.";
    }
    
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
    
    // Verificar si la cédula ya existe
    $sql_check = "SELECT cedula FROM empleados WHERE cedula = ?";
    if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
        mysqli_stmt_bind_param($stmt_check, "s", $cedula);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Este número de cédula ya existe en el sistema.";
        }
        mysqli_stmt_close($stmt_check);
    }
    
    // Si no hay errores, insertar el nuevo empleado
    if (empty($error)) {
        $sql = "INSERT INTO empleados (cedula, prefijo, tomo, asiento, nombre1, nombre2, apellido1, apellido2, apellidoc, 
                genero, estado_civil, tipo_sangre, usa_ac, f_nacimiento, celular, telefono, correo, 
                provincia, distrito, corregimiento, calle, casa, comunidad, nacionalidad, 
                f_contra, cargo, departamento, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssssssiisissssssssssssssi", 
                $cedula, $prefijo, $tomo, $asiento, $nombre1, $nombre2, $apellido1, $apellido2, $apellidoc, 
                $genero, $estado_civil, $tipo_sangre, $usa_ac, $f_nacimiento, $celular, $telefono, $correo, 
                $provincia, $distrito, $corregimiento, $calle, $casa, $comunidad, $nacionalidad, 
                $f_contra, $cargo, $departamento, $estado);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Empleado agregado correctamente.";
                
                // Limpiar todas las variables después de guardar
                $prefijo = $tomo = $asiento = $cedula = $nombre1 = $nombre2 = $apellido1 = $apellido2 = $apellidoc = "";
                $genero = $estado_civil = $tipo_sangre = $usa_ac = $f_nacimiento = $celular = $telefono = $correo = "";
                $provincia = $distrito = $corregimiento = $calle = $casa = $comunidad = $nacionalidad = $f_contra = $cargo = $departamento = $estado = "";
            } else {
                $error = "Error al registrar el empleado: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

// Incluir header
include "../../includes/header.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Agregar Nuevo Empleado</h1>
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
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" id="formularioEmpleado" novalidate>
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="mb-3">Información Personal</h4>
                </div>
                
                <!-- Datos de cédula con formato visual unificado -->
                <div class="col-md-6 mb-3">
                    <label for="prefijo" class="form-label">Cédula *</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="prefijo" name="prefijo" value="<?php echo $prefijo; ?>" maxlength="6" required placeholder="8">
                        <span class="input-group-text">-</span>
                        <input type="text" class="form-control" id="tomo" name="tomo" value="<?php echo $tomo; ?>" maxlength="4" required placeholder="1234">
                        <span class="input-group-text">-</span>
                        <input type="text" class="form-control" id="asiento" name="asiento" value="<?php echo $asiento; ?>" maxlength="6" required placeholder="123456">
                    </div>
                    <div class="form-text text-muted">Ingrese su número de cédula completo con el formato correcto</div>
                    <div id="cedula-error" class="text-danger"></div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nombre1" class="form-label">Primer Nombre *</label>
                    <input type="text" class="form-control" id="nombre1" name="nombre1" value="<?php echo $nombre1; ?>" maxlength="25" required>
                    <div class="invalid-feedback">
                        Este campo es requerido.
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nombre2" class="form-label">Segundo Nombre</label>
                    <input type="text" class="form-control" id="nombre2" name="nombre2" value="<?php echo $nombre2; ?>" maxlength="25">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="apellido1" class="form-label">Primer Apellido *</label>
                    <input type="text" class="form-control" id="apellido1" name="apellido1" value="<?php echo $apellido1; ?>" maxlength="25" required>
                    <div class="invalid-feedback">
                        Este campo es requerido.
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="apellido2" class="form-label">Segundo Apellido</label>
                    <input type="text" class="form-control" id="apellido2" name="apellido2" value="<?php echo $apellido2; ?>" maxlength="25">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="genero" class="form-label">Género *</label>
                    <select class="form-select" id="genero" name="genero" required onchange="mostrarApellidoCasada()">
                        <option value="" selected disabled>Seleccione...</option>
                        <option value="1" <?php if ($genero == 1) echo "selected"; ?>>Masculino</option>
                        <option value="0" <?php if ($genero == 0) echo "selected"; ?>>Femenino</option>
                    </select>
                    <div class="invalid-feedback">
                        Seleccione un género.
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="estado_civil" class="form-label">Estado Civil *</label>
                    <select class="form-select" id="estado_civil" name="estado_civil" required onchange="mostrarApellidoCasada()">
                        <option value="" selected disabled>Seleccione...</option>
                        <option value="0" <?php if ($estado_civil == 0) echo "selected"; ?>>Soltero/a</option>
                        <option value="1" <?php if ($estado_civil == 1) echo "selected"; ?>>Casado/a</option>
                        <option value="2" <?php if ($estado_civil == 2) echo "selected"; ?>>Viudo/a</option>
                        <option value="3" <?php if ($estado_civil == 3) echo "selected"; ?>>Divorciado/a</option>
                    </select>
                    <div class="invalid-feedback">
                        Seleccione un estado civil.
                    </div>
                </div>
                
                <!-- Campo de apellido de casada (inicialmente oculto) -->
                <div class="col-md-6" id="seccion_apellido_casada" style="display: none;">
                    <div class="mb-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="usa_ac" name="usa_ac" value="1" <?php if ($usa_ac == 1) echo "checked"; ?> onchange="toggleApellidoCasada()">
                            <label class="form-check-label" for="usa_ac">¿Usa apellido de casada?</label>
                        </div>
                        <div id="campo_apellido_casada" style="display: none;">
                            <label for="apellidoc" class="form-label">Apellido de Casada</label>
                            <input type="text" class="form-control" id="apellidoc" name="apellidoc" value="<?php echo $apellidoc; ?>" maxlength="25">
                            <small class="form-text text-muted">Ingrese el apellido del esposo</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="tipo_sangre" class="form-label">Tipo de Sangre *</label>
                    <select class="form-select" id="tipo_sangre" name="tipo_sangre" required>
                        <option value="" selected disabled>Seleccione...</option>
                        <option value="A+" <?php if ($tipo_sangre == "A+") echo "selected"; ?>>A+</option>
                        <option value="A-" <?php if ($tipo_sangre == "A-") echo "selected"; ?>>A-</option>
                        <option value="B+" <?php if ($tipo_sangre == "B+") echo "selected"; ?>>B+</option>
                        <option value="B-" <?php if ($tipo_sangre == "B-") echo "selected"; ?>>B-</option>
                        <option value="AB+" <?php if ($tipo_sangre == "AB+") echo "selected"; ?>>AB+</option>
                        <option value="AB-" <?php if ($tipo_sangre == "AB-") echo "selected"; ?>>AB-</option>
                        <option value="O+" <?php if ($tipo_sangre == "O+") echo "selected"; ?>>O+</option>
                        <option value="O-" <?php if ($tipo_sangre == "O-") echo "selected"; ?>>O-</option>
                    </select>
                    <div class="invalid-feedback">
                        Seleccione un tipo de sangre.
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="f_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                    <input type="date" class="form-control" id="f_nacimiento" name="f_nacimiento" value="<?php echo $f_nacimiento; ?>" max="<?php echo $maxDate; ?>" required>
                    <div class="invalid-feedback">
                        Debe ser mayor de edad (18 años mínimo).
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="nacionalidad" class="form-label">Nacionalidad *</label>
                    <select class="form-select nacionalidad-select" id="nacionalidad" name="nacionalidad" required>
                        <?php echo loadNacionalidades($conn, $nacionalidad); ?>
                    </select>
                    <div class="invalid-feedback">
                        Seleccione una nacionalidad.
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="mb-3">Información de Contacto</h4>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="celular" class="form-label">Celular *</label>
                    <input type="text" class="form-control" id="celular" name="celular" value="<?php echo $celular; ?>" maxlength="8" required>
                    <div class="invalid-feedback">
                        Ingrese un número de celular válido (máximo 8 dígitos).
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="telefono" class="form-label">Teléfono Fijo</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo $telefono; ?>" maxlength="7">
                    <div class="invalid-feedback">
                        Ingrese un número de teléfono válido (máximo 7 dígitos).
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="correo" class="form-label">Correo Electrónico *</label>
                    <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $correo; ?>" maxlength="40" required>
                    <div class="invalid-feedback">
                        Ingrese un correo electrónico válido.
                    </div>
                </div>
            </div>
            
            <!-- Sección de Ubicación -->
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="mb-3">Información de Ubicación</h4>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="provincia" class="form-label">Provincia *</label>
                    <select class="form-select" id="provincia" name="provincia" required>
                        <option value="">Seleccione una provincia...</option>
                        <?php
                        // Restablecer el puntero de resultados
                        if (isset($result_provincias) && $result_provincias) {
                            mysqli_data_seek($result_provincias, 0);
                            while ($row = mysqli_fetch_assoc($result_provincias)) {
                                $selected = ($provincia == $row['codigo_provincia']) ? 'selected' : '';
                                echo "<option value='" . $row['codigo_provincia'] . "' $selected>" . $row['nombre_provincia'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <div class="invalid-feedback">
                        Por favor seleccione una provincia.
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="distrito" class="form-label">Distrito *</label>
                    <select class="form-select" id="distrito" name="distrito" required disabled>
                        <option value="">Seleccione un distrito...</option>
                    </select>
                    <div class="invalid-feedback">
                        Por favor seleccione un distrito.
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="corregimiento" class="form-label">Corregimiento *</label>
                    <select class="form-select" id="corregimiento" name="corregimiento" required disabled>
                        <option value="">Seleccione un corregimiento...</option>
                    </select>
                    <div class="invalid-feedback">
                        Por favor seleccione un corregimiento.
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="calle" class="form-label">Calle</label>
                    <input type="text" class="form-control" id="calle" name="calle" value="<?php echo $calle; ?>" maxlength="30">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="casa" class="form-label">Casa/Apto</label>
                    <input type="text" class="form-control" id="casa" name="casa" value="<?php echo $casa; ?>" maxlength="10">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="comunidad" class="form-label">Comunidad/Urbanización</label>
                    <input type="text" class="form-control" id="comunidad" name="comunidad" value="<?php echo $comunidad; ?>" maxlength="25">
                </div>
            </div>
            
            <!-- Sección de Información Laboral -->
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="mb-3">Información Laboral</h4>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="f_contra" class="form-label">Fecha de Contratación *</label>
                    <input type="date" class="form-control" id="f_contra" name="f_contra" value="<?php echo $f_contra; ?>" required>
                    <div class="invalid-feedback">
                        Este campo es requerido.
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="departamento" class="form-label">Departamento *</label>
                    <select class="form-select" id="departamento" name="departamento" required>
                        <option value="">Seleccione...</option>
                        <?php
                        // Restablecer el puntero de resultados
                        if (isset($result_departamentos) && $result_departamentos) {
                            mysqli_data_seek($result_departamentos, 0);
                            while ($row = mysqli_fetch_assoc($result_departamentos)) {
                                $selected = ($departamento == $row['codigo']) ? 'selected' : '';
                                echo "<option value='" . $row['codigo'] . "' $selected>" . $row['nombre'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <div class="invalid-feedback">
                        Este campo es requerido.
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="cargo" class="form-label">Cargo *</label>
                    <select class="form-select" id="cargo" name="cargo" required disabled>
                        <option value="">Seleccione un departamento primero...</option>
                    </select>
                    <div class="invalid-feedback">
                        Este campo es requerido.
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="estado" class="form-label">Estado *</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="">Seleccione...</option>
                        <option value="1" <?php if ($estado == 1) echo "selected"; ?>>Activo</option>
                        <option value="0" <?php if ($estado == 0) echo "selected"; ?>>Inactivo</option>
                    </select>
                    <div class="invalid-feedback">
                        Este campo es requerido.
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="button" id="btnPreGuardar" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Empleado
                </button>
                <a href="list.php" class="btn btn-secondary btn-lg ms-2">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmación para guardar empleado -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalConfirmacionLabel">
                    <i class="fas fa-save me-2"></i> Confirmar Guardado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-user-plus fa-4x text-primary mb-3"></i>
                    <h4>¿Está seguro que desea guardar este empleado?</h4>
                    <p class="text-muted">Al confirmar, los datos del empleado serán registrados en el sistema.</p>
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

<script>
// Función para confirmar el guardado
function confirmarGuardado() {
    // Primero verificamos si el formulario es válido
    if (!document.getElementById('formularioEmpleado').checkValidity()) {
        // Si no es válido, permitir que continúe con la validación nativa del navegador
        return true;
    }
    
    // Si es válido, mostrar confirmación
    return confirm('¿Está seguro que desea guardar este empleado?');
}

// Función para mostrar/ocultar la sección de apellido de casada
function mostrarApellidoCasada() {
    const genero = document.getElementById('genero').value;
    const estadoCivil = document.getElementById('estado_civil').value;
    const seccionApellidoCasada = document.getElementById('seccion_apellido_casada');
    
    // Mostrar sección solo si es mujer (0) y casada (1)
    if (genero === '0' && estadoCivil === '1') {
        seccionApellidoCasada.style.display = 'block';
        // Verificar si usa apellido de casada
        toggleApellidoCasada();
    } else {
        seccionApellidoCasada.style.display = 'none';
        document.getElementById('campo_apellido_casada').style.display = 'none';
        // Limpiar valor si no aplica
        document.getElementById('apellidoc').value = '';
        document.getElementById('usa_ac').checked = false;
    }
}

// Función para mostrar/ocultar el campo de apellido de casada
function toggleApellidoCasada() {
    const usaApellidoCasada = document.getElementById('usa_ac').checked;
    const campoApellidoCasada = document.getElementById('campo_apellido_casada');
    
    if (usaApellidoCasada) {
        campoApellidoCasada.style.display = 'block';
    } else {
        campoApellidoCasada.style.display = 'none';
        document.getElementById('apellidoc').value = '';
    }
}

// Ejecutar al cargar la página para configurar los campos iniciales
document.addEventListener('DOMContentLoaded', function() {
    mostrarApellidoCasada();
    
    // Validar que la fecha de nacimiento sea al menos 18 años atrás
    const inputFechaNacimiento = document.getElementById('f_nacimiento');
    
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

    // Primera parte de la cédula (prefijo)
    const prefijoInput = document.getElementById('prefijo');
    const tomoInput = document.getElementById('tomo');
    const asientoInput = document.getElementById('asiento');
    const cedulaError = document.getElementById('cedula-error');
    
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

    // Validación para campos de nombres y apellidos
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
    
    // Validación para campos de solo números (celular y teléfono)
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

    // Cargar distritos cuando se selecciona una provincia
    const provinciaSelect = document.getElementById('provincia');
    const distritoSelect = document.getElementById('distrito');
    const corregimientoSelect = document.getElementById('corregimiento');

    // Función para cargar distritos
    function cargarDistritos(provinciaId) {
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
                    <?php if (!empty($distrito)): ?>
                    distritoSelect.value = '<?php echo $distrito; ?>';
                    cargarCorregimientos('<?php echo $provincia; ?>', '<?php echo $distrito; ?>');
                    <?php endif; ?>
                } else {
                    console.log('No se encontraron distritos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                distritoSelect.innerHTML = '<option value="">Error al cargar distritos</option>';
            });
    }

    // Función para cargar corregimientos
    function cargarCorregimientos(provinciaId, distritoId) {
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
                    <?php if (!empty($corregimiento)): ?>
                    corregimientoSelect.value = '<?php echo $corregimiento; ?>';
                    <?php endif; ?>
                } else {
                    console.log('No se encontraron corregimientos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                corregimientoSelect.innerHTML = '<option value="">Error al cargar corregimientos</option>';
            });
    }

    // Agregar evento change a provincia
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

    // Agregar evento change a distrito
    distritoSelect.addEventListener('change', function() {
        // Resetear y deshabilitar selector de corregimiento
        corregimientoSelect.innerHTML = '<option value="">Seleccione un corregimiento...</option>';
        corregimientoSelect.disabled = true;
        
        // Si hay un valor seleccionado, cargar corregimientos
        if (this.value) {
            cargarCorregimientos(provinciaSelect.value, this.value);
        }
    });

    // Inicialización: Si hay provincia seleccionada, cargar distritos
    <?php if (!empty($provincia)): ?>
    // Cargar distritos al inicio si hay provincia seleccionada
    cargarDistritos('<?php echo $provincia; ?>');
    <?php endif; ?>

    // Cargar cargos cuando se selecciona un departamento
    const departamentoSelect = document.getElementById('departamento');
    const cargoSelect = document.getElementById('cargo');

    // Función para cargar cargos
    function cargarCargos(departamentoId) {
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
                    
                    // Habilitar el selector
                    cargoSelect.disabled = false;
                    
                    // Si hay un valor previamente seleccionado
                    <?php if (!empty($cargo)): ?>
                    cargoSelect.value = '<?php echo $cargo; ?>';
                    <?php endif; ?>
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

    // Agregar evento change a departamento
    departamentoSelect.addEventListener('change', function() {
        // Resetear y deshabilitar selector de cargo
        cargoSelect.innerHTML = '<option value="">Seleccione un cargo...</option>';
        cargoSelect.disabled = true;
        
        // Si hay un valor seleccionado, cargar cargos
        if (this.value) {
            cargarCargos(this.value);
        }
    });

    // Inicialización: Si hay departamento seleccionado, cargar cargos
    <?php if (!empty($departamento)): ?>
    // Cargar cargos al inicio si hay departamento seleccionado
    cargarCargos('<?php echo $departamento; ?>');
    <?php endif; ?>

    // Modal de confirmación para guardar empleado
    const formulario = document.getElementById('formularioEmpleado');
    const btnPreGuardar = document.getElementById('btnPreGuardar');
    const btnConfirmarGuardado = document.getElementById('btnConfirmarGuardado');
    const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
    
    // Manejar el clic en el botón de pre-guardar
    btnPreGuardar.addEventListener('click', function() {
        // Validar formulario antes de mostrar modal
        if (formulario.checkValidity()) {
            // Mostrar resumen de datos en el modal
            const prefijo = document.getElementById('prefijo').value;
            const tomo = document.getElementById('tomo').value;
            const asiento = document.getElementById('asiento').value;
            const cedula = `${prefijo}-${tomo}-${asiento}`;
            
            const nombre1 = document.getElementById('nombre1').value;
            const apellido1 = document.getElementById('apellido1').value;
            
            document.getElementById('confirmCedula').textContent = cedula;
            document.getElementById('confirmNombre').textContent = `${nombre1} ${apellido1}`;
            
            // Mostrar el modal
            modalConfirmacion.show();
        } else {
            // Si no es válido, activar las validaciones visuales
            formulario.classList.add('was-validated');
        }
    });
    
    // Manejar el clic en el botón de confirmación
    btnConfirmarGuardado.addEventListener('click', function() {
        // Ocultar el modal y enviar el formulario
        modalConfirmacion.hide();
        formulario.submit();
    });
});
</script>

<?php 
// Cerrar conexión
mysqli_close($conn);
// Incluir footer
include "../../includes/footer.php"; 
?>
