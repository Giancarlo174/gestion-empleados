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
$prefijo = $tomo = $asiento = $cedula = "";
$contraseña = $confirmar_contraseña = $correo_institucional = "";
$cedula_err = $contraseña_err = $confirmar_contraseña_err = $correo_err = "";
$success = "";

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Construir la cédula a partir de sus componentes
    $prefijo = trim($_POST["prefijo"]);
    $tomo = trim($_POST["tomo"]);
    $asiento = trim($_POST["asiento"]);
    
    // Validar que todos los componentes de la cédula estén presentes
    if (empty($prefijo) || empty($tomo) || empty($asiento)) {
        $cedula_err = "Por favor complete todos los campos de la cédula.";
    } else {
        // Construir la cédula completa
        $cedula = $prefijo . "-" . $tomo . "-" . $asiento;
        
        // Verificar si la cédula ya existe
        $sql = "SELECT id FROM usuarios WHERE cedula = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $cedula);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $cedula_err = "Esta cédula ya está registrada como administrador.";
                }
            } else {
                echo "Error al ejecutar la consulta: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validar contraseña
    $contraseña = trim($_POST["contraseña"]);
    if (empty($contraseña)) {
        $contraseña_err = "Por favor ingrese una contraseña.";
    } elseif (strlen($contraseña) < 6) {
        $contraseña_err = "La contraseña debe tener al menos 6 caracteres.";
    }
    
    // Validar confirmación de contraseña
    $confirmar_contraseña = trim($_POST["confirmar_contraseña"]);
    if (empty($confirmar_contraseña)) {
        $confirmar_contraseña_err = "Por favor confirme la contraseña.";
    } else {
        if (empty($contraseña_err) && ($contraseña != $confirmar_contraseña)) {
            $confirmar_contraseña_err = "Las contraseñas no coinciden.";
        }
    }
    
    // Validar correo institucional
    $correo_institucional = trim($_POST["correo_institucional"]);
    if (empty($correo_institucional)) {
        $correo_err = "Por favor ingrese el correo institucional.";
    } elseif (!filter_var($correo_institucional, FILTER_VALIDATE_EMAIL)) {
        $correo_err = "Por favor ingrese un correo electrónico válido.";
    } else {
        // Verificar si el correo ya existe
        $sql = "SELECT id FROM usuarios WHERE correo_institucional = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $correo_institucional);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $correo_err = "Este correo institucional ya está en uso.";
                }
            } else {
                echo "Error al ejecutar la consulta: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    // Verificar errores de entrada antes de insertar en la base de datos
    if (empty($cedula_err) && empty($contraseña_err) && empty($confirmar_contraseña_err) && empty($correo_err)) {
        // Preparar la sentencia de inserción
        $sql = "INSERT INTO usuarios (cedula, contraseña, correo_institucional) VALUES (?, ?, ?)";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variables a la sentencia preparada como parámetros
            mysqli_stmt_bind_param($stmt, "sss", $cedula, $param_contraseña, $param_correo);
            
            // Establecer parámetros
            // Usar password_hash para crear un hash seguro de la contraseña
            // PASSWORD_DEFAULT usa el algoritmo bcrypt (actualmente), que es seguro y actualizado
            $param_contraseña = password_hash($contraseña, PASSWORD_DEFAULT);
            $param_correo = $correo_institucional;
            
            // Intentar ejecutar la sentencia preparada
            if (mysqli_stmt_execute($stmt)) {
                $success = "Administrador agregado correctamente.";
                // Limpiar variables
                $prefijo = $tomo = $asiento = $cedula = $contraseña = $confirmar_contraseña = $correo_institucional = "";
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
    <h1 class="h2">Agregar Nuevo Administrador</h1>
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
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label">Cédula *</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="prefijo" name="prefijo" value="<?php echo $prefijo; ?>" maxlength="6" required placeholder="8">
                    <span class="input-group-text">-</span>
                    <input type="text" class="form-control" id="tomo" name="tomo" value="<?php echo $tomo; ?>" maxlength="4" required placeholder="1234">
                    <span class="input-group-text">-</span>
                    <input type="text" class="form-control" id="asiento" name="asiento" value="<?php echo $asiento; ?>" maxlength="6" required placeholder="123456">
                </div>
                <div id="cedula-error" class="text-danger"><?php echo $cedula_err; ?></div>
                <small class="form-text text-muted">Ingrese la cédula completa (ej: 8-123-456)</small>
            </div>
            
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña *</label>
                <div class="input-group">
                    <input type="password" class="form-control <?php echo (!empty($contraseña_err)) ? 'is-invalid' : ''; ?>" id="contraseña" name="contraseña" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1" data-target="contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback"><?php echo $contraseña_err; ?></div>
                <small class="form-text text-muted">La contraseña debe tener al menos 6 caracteres</small>
            </div>
            
            <div class="mb-3">
                <label for="confirmar_contraseña" class="form-label">Confirmar Contraseña *</label>
                <div class="input-group">
                    <input type="password" class="form-control <?php echo (!empty($confirmar_contraseña_err)) ? 'is-invalid' : ''; ?>" id="confirmar_contraseña" name="confirmar_contraseña" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1" data-target="confirmar_contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback"><?php echo $confirmar_contraseña_err; ?></div>
            </div>
            
            <div class="mb-3">
                <label for="correo_institucional" class="form-label">Correo Institucional *</label>
                <input type="email" class="form-control <?php echo (!empty($correo_err)) ? 'is-invalid' : ''; ?>" id="correo_institucional" name="correo_institucional" value="<?php echo $correo_institucional; ?>" required>
                <div class="invalid-feedback"><?php echo $correo_err; ?></div>
                <small class="form-text text-muted">Ejemplo: usuario@dominio.com</small>
            </div>
            
            <div class="text-center mt-4">
                <button type="button" id="btnPreGuardar" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Administrador
                </button>
                <a href="list.php" class="btn btn-secondary btn-lg ms-2">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmación para guardar administrador -->
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
                    <h4>¿Está seguro que desea crear este administrador?</h4>
                    <p class="text-muted">Al confirmar, el nuevo administrador será registrado en el sistema.</p>
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
                                <i class="fas fa-envelope fa-2x text-secondary me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Correo</h6>
                                <div id="confirmEmail" class="fw-bold"></div>
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
                    <i class="fas fa-save me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a los campos
    const prefijoInput = document.getElementById('prefijo');
    const tomoInput = document.getElementById('tomo');
    const asientoInput = document.getElementById('asiento');
    const cedulaError = document.getElementById('cedula-error');
    
    // Primera parte de la cédula (prefijo)
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
    
    // Validación del formulario en el lado del cliente
    (function() {
        'use strict';
        
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation');
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
    })();
    
    // Password visibility toggle functionality
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            
            // Toggle type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
    
    // Modal de confirmación para guardar administrador
    const formulario = document.querySelector('form');
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
            const correo = document.getElementById('correo_institucional').value;
            
            document.getElementById('confirmCedula').textContent = cedula;
            document.getElementById('confirmEmail').textContent = correo;
            
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
