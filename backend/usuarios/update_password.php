<?php
header('Content-Type: application/json');

require_once '../config.php';

// Get posted data
$_POST = json_decode(file_get_contents('php://input'), true);

if (!isset($_POST['email']) || !isset($_POST['new_password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email and new password are required'
    ]);
    exit;
}

$email = $_POST['email'];
$newPassword = $_POST['new_password'];
$forceUpdate = isset($_POST['force_update']) ? $_POST['force_update'] : false;

// This endpoint allows direct password updates without verification
// This is potentially risky and should only be used by authenticated admins
// or in specific controlled scenarios

try {
    $conn = getConnection();
    
    // Check if the user exists
    $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM usuarios WHERE correo_institucional = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkRow = $checkResult->fetch_assoc();
    
    if ($checkRow['count'] == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    // Hash new password
    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $updateStmt = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE correo_institucional = ?");
    $updateStmt->bind_param("ss", $hashedNewPassword, $email);
    $updateStmt->execute();
    
    if ($updateStmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update password'
        ]);
    }
    
    $updateStmt->close();
    $checkStmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
