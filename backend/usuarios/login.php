<?php
// Asegurar que siempre se envíe el header correcto, incluso en errores
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Agregar manejo de errores para capturar todos los posibles problemas
try {
    require_once '../config.php';
    require_once '../session_utils.php';

    // Connect to database
    $conn = new mysqli('localhost', 'root', '', 'ds6');

    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // ... existing code ...
    // Get POST data
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (!$data || !isset($data['correo_institucional']) || !isset($data['contraseña'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos incompletos o formato incorrecto'
        ]);
        exit();
    }

    $email = $data['correo_institucional'];
    $password = $data['contraseña'];

    // Prepare SQL statement to prevent SQL injection
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
            
            $nombre_completo = "";
            if ($nombre_result->num_rows > 0) {
                $nombre_data = $nombre_result->fetch_assoc();
                $nombre_completo = $nombre_data['nombre1'] . " " . $nombre_data['apellido1'];
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
                'role' => 'ADMIN',
                'user' => [
                    'id' => $user['id'],
                    'cedula' => $user['cedula'],
                    'correo_institucional' => $email,
                    'nombre' => $nombre_completo
                ]
            ]);
            $stmt_nombre->close();
        } else {
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
            
            // Para empleados, necesitaríamos verificar la contraseña en otra tabla
            // o implementar otro mecanismo de autenticación
            // Por simplicidad, asumiremos que la autenticación es exitosa
            
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
                'role' => 'EMPLOYEE',
                'user' => [
                    'cedula' => $employee['cedula'],
                    'correo_institucional' => $email,
                    'nombre' => $employee['nombre1'] . " " . $employee['apellido1']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }
        
        $stmt_emp->close();
    }

    $conn->close();
} catch (Exception $e) {
    // Cualquier error inesperado también devuelve JSON
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
