<?php
header('Content-Type: application/json');

require_once '../config.php';

// Get email from request
$_POST = json_decode(file_get_contents('php://input'), true);

if (!isset($_POST['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email is required'
    ]);
    exit;
}

$email = $_POST['email'];

try {
    $conn = getConnection();
    
    // First get cedula from usuarios table
    $stmt = $conn->prepare("SELECT cedula FROM usuarios WHERE correo_institucional = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $cedula = $row['cedula'];
        
        // Now get user details from empleados table
        $empStmt = $conn->prepare("SELECT * FROM empleados WHERE cedula = ?");
        $empStmt->bind_param("s", $cedula);
        $empStmt->execute();
        $empResult = $empStmt->get_result();
        
        if ($empResult->num_rows == 1) {
            $empleado = $empResult->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'cedula' => $empleado['cedula'],
                    'nombre' => $empleado['nombre1'] . ' ' . $empleado['apellido1'],
                    'correo' => $email,
                    'departamento' => $empleado['departamento'],
                    'cargo' => $empleado['cargo'],
                    'estado' => $empleado['estado']
                ]
            ]);
        } else {
            // Admin user exists but no employee record
            echo json_encode([
                'success' => true,
                'user' => [
                    'cedula' => $cedula,
                    'nombre' => 'Administrador',
                    'correo' => $email,
                    'departamento' => null,
                    'cargo' => null,
                    'estado' => null
                ]
            ]);
        }
        
        $empStmt->close();
    } else {
        // Not in usuarios, check empleados
        $stmt->close();
        
        $empDirectStmt = $conn->prepare("SELECT * FROM empleados WHERE correo = ?");
        $empDirectStmt->bind_param("s", $email);
        $empDirectStmt->execute();
        $empDirectResult = $empDirectStmt->get_result();
        
        if ($empDirectResult->num_rows == 1) {
            $empleado = $empDirectResult->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'cedula' => $empleado['cedula'],
                    'nombre' => $empleado['nombre1'] . ' ' . $empleado['apellido1'],
                    'correo' => $email,
                    'departamento' => $empleado['departamento'],
                    'cargo' => $empleado['cargo'],
                    'estado' => $empleado['estado']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
        
        $empDirectStmt->close();
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
