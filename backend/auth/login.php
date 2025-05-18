<?php
// Incluir archivo de configuración
require_once '../config.php';

// Asegurar salida limpia
ensureCleanOutput();

// Obtener datos del cuerpo de la solicitud JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Guardar log para depuración
file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Request: " . $json_data . PHP_EOL, FILE_APPEND);

// Verificación básica de los datos recibidos
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Correo y contraseña son requeridos'
    ]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

try {
    $conn = getConnection();
    
    // Primero buscar en la tabla de usuarios (administradores)
    $sql_admin = "SELECT id, cedula, contraseña FROM usuarios WHERE correo_institucional = ?";
    $stmt_admin = $conn->prepare($sql_admin);
    $stmt_admin->bind_param("s", $email);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();
    
    if ($result_admin && $result_admin->num_rows == 1) {
        // Es un administrador
        $row = $result_admin->fetch_assoc();
        
        // Verificar contraseña
        if (password_verify($password, $row['contraseña'])) {
            // Autenticación exitosa como administrador
            $response = [
                'success' => true,
                'message' => 'Login exitoso como administrador',
                'data' => [
                    'user_type' => 'admin',
                    'cedula' => $row['cedula'],
                    'api_key' => bin2hex(random_bytes(16))
                ]
            ];
            echo json_encode($response);
            exit;
        }
    }
    
    // Si no se encontró en usuarios, buscar en empleados
    // Ahora la tabla empleados sí tiene columnas correo y contraseña
    $sql_employee = "SELECT cedula, contraseña FROM empleados WHERE correo = ? AND estado = 1";
    $stmt_employee = $conn->prepare($sql_employee);
    $stmt_employee->bind_param("s", $email);
    $stmt_employee->execute();
    $result_employee = $stmt_employee->get_result();
    
    if ($result_employee && $result_employee->num_rows == 1) {
        // Es un empleado
        $row = $result_employee->fetch_assoc();
        
        // Verificar contraseña
        if (password_verify($password, $row['contraseña'])) {
            // Autenticación exitosa como empleado
            $response = [   
                'success' => true,
                'message' => 'Login exitoso como empleado',
                'data' => [
                    'user_type' => 'employee',
                    'cedula' => $row['cedula'],
                    'api_key' => bin2hex(random_bytes(16))
                ]
            ];
            echo json_encode($response);
            file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Employee success: " . json_encode($response) . PHP_EOL, FILE_APPEND);
            exit;
        }
    }
    
    // Si llegamos aquí, las credenciales son inválidas
    $response = [
        'success' => false,
        'message' => 'Correo o contraseña incorrectos'
    ];
    http_response_code(401);
    echo json_encode($response);
    file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Auth failed: " . json_encode($response) . PHP_EOL, FILE_APPEND);
    
    $conn->close();
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ];
    http_response_code(500);
    echo json_encode($response);
    file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . PHP_EOL, FILE_APPEND);
}
?>
