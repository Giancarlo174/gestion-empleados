<?php
header('Content-Type: application/json');

require_once '../config.php';

// Get posted data
$_POST = json_decode(file_get_contents('php://input'), true);

if (!isset($_POST['cedula']) || !isset($_POST['email']) || !isset($_POST['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Cedula, email, and password are required'
    ]);
    exit;
}

$cedula = $_POST['cedula'];
$email = $_POST['email'];
$password = $_POST['password'];

try {
    $conn = getConnection();
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert the new user
    $stmt = $conn->prepare("INSERT INTO usuarios (cedula, correo_institucional, contraseña) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $cedula, $email, $hashedPassword);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create user'
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
