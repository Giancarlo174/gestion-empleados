<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
$conn = getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['codigo']) || empty($data['nombre']) || empty($data['dep_codigo'])) {
    echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios"]);
    exit;
}

// Validar que no exista el mismo código en ese departamento
$stmt_check = $conn->prepare("SELECT COUNT(*) FROM cargo WHERE codigo = ? AND dep_codigo = ?");
$stmt_check->bind_param("ss", $data['codigo'], $data['dep_codigo']);
$stmt_check->execute();
$stmt_check->bind_result($existe);
$stmt_check->fetch();
$stmt_check->close();

if ($existe > 0) {
    echo json_encode(["success" => false, "message" => "Ya existe ese código para este departamento"]);
    $conn->close();
    exit;
}

// Validar que no exista el mismo nombre en ese departamento
$stmt_check = $conn->prepare("SELECT COUNT(*) FROM cargo WHERE nombre = ? AND dep_codigo = ?");
$stmt_check->bind_param("ss", $data['nombre'], $data['dep_codigo']);
$stmt_check->execute();
$stmt_check->bind_result($existe_nombre);
$stmt_check->fetch();
$stmt_check->close();

if ($existe_nombre > 0) {
    echo json_encode(["success" => false, "message" => "Ya existe un cargo con ese nombre en este departamento"]);
    $conn->close();
    exit;
}

$stmt = $conn->prepare("INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $data['dep_codigo'], $data['codigo'], $data['nombre']);
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Cargo creado correctamente"]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
}
$stmt->close();
$conn->close();
?>
