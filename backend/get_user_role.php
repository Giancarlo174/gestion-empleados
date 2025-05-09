<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');

// Para solicitudes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config.php';

// Get email from request
$_POST = json_decode(file_get_contents('php://input'), true);

if (!isset($_POST['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email is required',
        'role' => 'UNKNOWN'
    ]);
    exit;
}

$email = $_POST['email'];
$role = 'UNKNOWN';

try {
    $conn = getConnection();
    
    // Check if user exists in usuarios table (admin check)
    $adminStmt = $conn->prepare("SELECT cedula FROM usuarios WHERE correo_institucional = ?");
    $adminStmt->bind_param("s", $email);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    
    if ($adminResult->num_rows == 1) {
        $adminRow = $adminResult->fetch_assoc();
        $cedula = $adminRow['cedula'];
        
        // Check if this cedula is NOT in empleados (true admin)
        $empCheckStmt = $conn->prepare("SELECT COUNT(*) as count FROM empleados WHERE cedula = ?");
        $empCheckStmt->bind_param("s", $cedula);
        $empCheckStmt->execute();
        $empCheckResult = $empCheckStmt->get_result();
        $empCheckRow = $empCheckResult->fetch_assoc();
        
        if ($empCheckRow['count'] == 0) {
            // User is in usuarios but not in empleados = ADMIN
            $role = 'ADMIN';
        } else {
            // User is in both tables = EMPLOYEE
            $role = 'EMPLOYEE';
        }
        
        $empCheckStmt->close();
    } else {
        // Not found in usuarios, check empleados directly
        $empStmt = $conn->prepare("SELECT cedula FROM empleados WHERE correo = ?");
        $empStmt->bind_param("s", $email);
        $empStmt->execute();
        $empResult = $empStmt->get_result();
        
        if ($empResult->num_rows == 1) {
            $role = 'EMPLOYEE';
        }
        
        $empStmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'role' => $role
    ]);
    
    $adminStmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'role' => 'UNKNOWN'
    ]);
}
?>
