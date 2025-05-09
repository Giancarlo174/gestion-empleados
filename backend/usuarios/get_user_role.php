<?php
header('Content-Type: application/json');

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

try {
    $conn = getConnection();
    
    // First check if user is in usuarios table (admins)
    $adminStmt = $conn->prepare("SELECT cedula FROM usuarios WHERE correo_institucional = ?");
    $adminStmt->bind_param("s", $email);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    
    $role = 'UNKNOWN';
    
    if ($adminResult->num_rows > 0) {
        // User found in usuarios table
        $row = $adminResult->fetch_assoc();
        $cedula = $row['cedula'];
        
        // Check if this cedula is in empleados table
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
            // Found in empleados only = EMPLOYEE
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
