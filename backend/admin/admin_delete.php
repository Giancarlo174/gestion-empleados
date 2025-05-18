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

    // 1. Obtener los datos del usuario antes de eliminar
    $stmtSelect = $conn->prepare("SELECT id, cedula, contraseña, correo_institucional FROM usuarios WHERE id = ?");
    $stmtSelect->bind_param("i", $id);
    $stmtSelect->execute();
    $result = $stmtSelect->get_result();
    $usuario = $result->fetch_assoc();
    $stmtSelect->close();

    if (!$usuario) {
        echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
        $conn->close();
        exit;
    }

    // 2. Insertar en u_eliminados con la fecha de hoy
    $fechaHoy = date('Y-m-d');
    $stmtInsert = $conn->prepare("INSERT INTO u_eliminados (id, cedula, contraseña, correo_institucional, f_eliminacion) VALUES (?, ?, ?, ?, ?)");
    $stmtInsert->bind_param("issss", $usuario['id'], $usuario['cedula'], $usuario['contraseña'], $usuario['correo_institucional'], $fechaHoy);
    if (!$stmtInsert->execute()) {
        echo json_encode(["success" => false, "message" => "No se pudo respaldar en u_eliminados"]);
        $stmtInsert->close();
        $conn->close();
        exit;
    }
    $stmtInsert->close();

    // 3. Ahora sí, eliminar el usuario
    $stmtDelete = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmtDelete->bind_param("i", $id);
    if ($stmtDelete->execute()) {
        echo json_encode(["success" => true, "message" => "Administrador eliminado y respaldado en u_eliminados"]);
    } else {
        echo json_encode(["success" => false, "message" => "No se pudo eliminar"]);
    }
    $stmtDelete->close();
    $conn->close();
}
?>
