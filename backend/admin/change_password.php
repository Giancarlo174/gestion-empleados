<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    $data = [
        'cedula' => $_POST['cedula'] ?? '',
        'password_actual' => $_POST['password_actual'] ?? '',
        'password_nueva' => $_POST['password_nueva'] ?? '',
    ];
}

if (empty($data['cedula']) || empty($data['password_actual']) || empty($data['password_nueva'])) {
    echo json_encode(['success' => false, 'message' => 'Campos requeridos: cedula, password_actual, password_nueva']);
    exit;
}

$cedula = $data['cedula'];
$passwordActual = $data['password_actual'];
$passwordNueva = $data['password_nueva'];

if (strlen($passwordNueva) < 6) {
    echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres']);
    exit;
}

try {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id, contraseña FROM usuarios WHERE cedula = ?");
    $stmt->bind_param('s', $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    $usuario = $result->fetch_assoc();
    
    if (!password_verify($passwordActual, $usuario['contraseña'])) {
        echo json_encode(['success' => false, 'message' => 'Contraseña actual incorrecta']);
        exit;
    }
    
    $hashedPassword = password_hash($passwordNueva, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
    $updateStmt->bind_param('si', $hashedPassword, $usuario['id']);
    
    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña: ' . $conn->error]);
    }
    
    $updateStmt->close();
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
