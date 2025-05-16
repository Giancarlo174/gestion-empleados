<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
$conn = getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['nombre'])) {
    echo json_encode(["success" => false, "message" => "Nombre requerido"]);
    exit;
}

$nombre = trim($data['nombre']);

// Verificar si el nombre ya existe
$stmt_check = $conn->prepare("SELECT COUNT(*) FROM departamento WHERE nombre = ?");
$stmt_check->bind_param("s", $nombre);
$stmt_check->execute();
$stmt_check->bind_result($existe);
$stmt_check->fetch();
$stmt_check->close();

if ($existe > 0) {
    echo json_encode(["success" => false, "message" => "Ya existe un departamento con ese nombre"]);
    $conn->close();
    exit;
}

// Obtener todos los códigos existentes y buscar el primero disponible entre 01 y 99
$res = $conn->query("SELECT codigo FROM departamento ORDER BY CAST(codigo AS UNSIGNED)");
$ocupados = [];
while ($row = $res->fetch_assoc()) {
    $ocupados[] = (int)$row['codigo'];
}

$codigo_disponible = null;
for ($i = 1; $i <= 99; $i++) {
    if (!in_array($i, $ocupados)) {
        $codigo_disponible = str_pad($i, 2, '0', STR_PAD_LEFT);
        break;
    }
}

if (!$codigo_disponible) {
    echo json_encode(["success" => false, "message" => "No hay más códigos disponibles"]);
    $conn->close();
    exit;
}

// Insertar departamento
$stmt = $conn->prepare("INSERT INTO departamento (codigo, nombre) VALUES (?, ?)");
$stmt->bind_param("ss", $codigo_disponible, $nombre);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Departamento creado",
        "codigo" => $codigo_disponible
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
