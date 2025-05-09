<?php
header('Content-Type: application/json');

require_once '../config.php';

// Get posted data
$_POST = json_decode(file_get_contents('php://input'), true);

if (!isset($_POST['email']) || !isset($_POST['current_password']) || !isset($_POST['new_password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email, current password, and new password are required'
    ]);
    exit;
}

$email = $_POST['email'];
$currentPassword = $_POST['current_password'];
$newPassword = $_POST['new_password'];

try {
    $conn = getConnection();
    
    // Get current hashed password
    $stmt = $conn->prepare("SELECT id, contraseña FROM usuarios WHERE correo_institucional = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    // Verify current password
    if (!password_verify($currentPassword, $user['contraseña'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]);
        exit;
    }
    
    // Hash new password
    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $updateStmt = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
    $updateStmt->bind_param("si", $hashedNewPassword, $user['id']);
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
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
