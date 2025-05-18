<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id ?? null;

    if (!$id) {
        echo json_encode(["success" => false, "message" => "ID requerido"]);
        exit;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Administrador eliminado"]);
    } else {
        echo json_encode(["success" => false, "message" => "No se pudo eliminar"]);
    }
    $stmt->close();
    $conn->close();
}
?>
