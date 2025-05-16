<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
$conn = getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (
    !$data ||
    empty($data['cedula']) ||
    empty($data['contrasena']) ||
    empty($data['correo_institucional'])
) {
    echo json_encode(["success" => false, "message" => "Todos los campos son requeridos"]);
    exit;
}

// Validar longitud de contraseña
if (strlen($data['contrasena']) < 6) {
    echo json_encode(["success" => false, "message" => "La contraseña debe tener al menos 6 caracteres"]);
    exit;
}

// Validar que no exista el correo institucional
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo_institucional = ?");
$stmt->bind_param("s", $data['correo_institucional']);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Ya existe un usuario con ese correo institucional"]);
    exit;
}
$stmt->close();

// Validar que no exista la cédula
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE cedula = ?");
$stmt->bind_param("s", $data['cedula']);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Ya existe un usuario con esa cédula"]);
    exit;
}
$stmt->close();

// Hash de la contraseña
$contrasenaHash = password_hash($data['contrasena'], PASSWORD_DEFAULT);

// Insertar el usuario
$stmt = $conn->prepare(
    "INSERT INTO usuarios (cedula, contraseña, correo_institucional) VALUES (?, ?, ?)"
);
$stmt->bind_param("sss", $data['cedula'], $contrasenaHash, $data['correo_institucional']);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Administrador creado exitosamente"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al registrar: " . $conn->error]);
}

$stmt->close();
$conn->close();
