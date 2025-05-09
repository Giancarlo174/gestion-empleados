<?php
header('Content-Type: application/json');

require_once '../config.php';

// Get posted data
$_POST = json_decode(file_get_contents('php://input'), true);

if (!isset($_POST['cedula']) || !isset($_POST['new_password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Cedula and new password are required'
    ]);
    exit;
}

$cedula = $_POST['cedula'];
$newPassword = $_POST['new_password'];

try {
    $conn = getConnection();
    
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update the user's password
    $stmt = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE cedula = ?");
    $stmt->bind_param("ss", $hashedPassword, $cedula);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found or reset failed'
        ]);
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
