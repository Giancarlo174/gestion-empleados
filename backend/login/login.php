<?php
// Iniciar buffer de salida para poder limpiarlo en caso de error
ob_start();

// Configurar manejador de errores para asegurar respuestas JSON
set_error_handler(function($severity, $message, $file, $line) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => "PHP Error: $message in $file on line $line"
    ]);
    exit;
});

// Configurar cabeceras CORS y JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');

// Para solicitudes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Incluir configuración y utilidades
    require_once '../config.php';
    require_once '../session_utils.php';

    // Verificar método de solicitud
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Use POST para iniciar sesión.');
    }

    // Obtener datos POST (JSON)
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (!$data || !isset($data['correo_institucional']) || !isset($data['contraseña'])) {
        throw new Exception('Datos incompletos o formato incorrecto');
    }

    $email = $data['correo_institucional'];
    $password = $data['contraseña'];

    $conn = getConnection();

    // Buscar usuario en la tabla usuarios
    $stmt = $conn->prepare("SELECT id, cedula, contraseña FROM usuarios WHERE correo_institucional = ?");
    if (!$stmt) {
        throw new Exception('Error en la preparación de la consulta: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si se encuentra en la tabla de usuarios, es un ADMIN
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['contraseña'])) {
            // Obtener el nombre del administrador desde la tabla de empleados
            $stmt_nombre = $conn->prepare("SELECT nombre1, apellido1 FROM empleados WHERE cedula = ?");
            $stmt_nombre->bind_param("s", $user['cedula']);
            $stmt_nombre->execute();
            $nombre_result = $stmt_nombre->get_result();
            
            $nombre_completo = "Administrador"; // Valor por defecto
            if ($nombre_result->num_rows > 0) {
                $nombre_data = $nombre_result->fetch_assoc();
                $nombre_completo = $nombre_data['nombre1'] . " " . $nombre_data['apellido1'];
            }
            
            // Crear token de sesión
            $sessionToken = createSessionToken($user['cedula'], $email, 'ADMIN');
            
            // Establecer cookie de sesión (importante para autenticación)
            setcookie('ds6p1_session', $sessionToken, COOKIE_EXPIRATION, '/', '', false, true);
            
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
                'role' => 'ADMIN',
                'user' => [
                    'id' => $user['id'],
                    'cedula' => $user['cedula'],
                    'correo_institucional' => $email,
                    'nombre' => $nombre_completo
                ],
                'session_token' => $sessionToken
            ]);
            
            if (isset($stmt_nombre)) $stmt_nombre->close();
        } else {
            // Contraseña incorrecta
            echo json_encode([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ]);
        }
    } else {
        // Si no está en usuarios, buscar en empleados
        $stmt->close();
        
        $stmt_emp = $conn->prepare("SELECT cedula, nombre1, apellido1 FROM empleados WHERE correo = ?");
        if (!$stmt_emp) {
            throw new Exception('Error en la preparación de la consulta: ' . $conn->error);
        }
        
        $stmt_emp->bind_param("s", $email);
        $stmt_emp->execute();
        $emp_result = $stmt_emp->get_result();
        
        if ($emp_result->num_rows > 0) {
            $employee = $emp_result->fetch_assoc();
            
            // Crear token de sesión para empleado
            $sessionToken = createSessionToken($employee['cedula'], $email, 'EMPLOYEE');
            
            // Establecer cookie de sesión
            setcookie('ds6p1_session', $sessionToken, COOKIE_EXPIRATION, '/', '', false, true);
            
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
                'role' => 'EMPLOYEE',
                'user' => [
                    'cedula' => $employee['cedula'],
                    'correo_institucional' => $email,
                    'nombre' => $employee['nombre1'] . " " . $employee['apellido1']
                ],
                'session_token' => $sessionToken
            ]);
        } else {
            // Usuario no encontrado
            echo json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }
        
        if (isset($stmt_emp)) $stmt_emp->close();
    }

    // Cerrar conexión
    $conn->close();
    
} catch (Exception $e) {
    // Limpiar cualquier output previo
    ob_clean();
    
    // Asegurarse de que el header tenga el content type correcto
    header('Content-Type: application/json');
    
    // Respuesta de error en formato JSON
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
