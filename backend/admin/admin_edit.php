<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id ?? null;
    $cedula = $data->cedula ?? null;
    $correo = $data->correo_institucional ?? null;
    $passActual = $data->contrasena_actual ?? null;
    $nuevaPass = $data->nueva_contrasena ?? null;

    if (!$id || !$cedula || !$correo || !$passActual) {
        echo json_encode(["success" => false, "message" => "Campos requeridos"]);
        exit;
    }

    $conn = getConnection();

    $stmt = $conn->prepare("SELECT contraseña FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($hashGuardado);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($passActual, $hashGuardado)) {
        echo json_encode(["success" => false, "message" => "Contraseña actual incorrecta"]);
        $conn->close();
        exit;
    }

    if ($nuevaPass) {
        $hash = password_hash($nuevaPass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET cedula = ?, correo_institucional = ?, contraseña = ? WHERE id = ?");
        $stmt->bind_param("sssi", $cedula, $correo, $hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET cedula = ?, correo_institucional = ? WHERE id = ?");
        $stmt->bind_param("ssi", $cedula, $correo, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Administrador actualizado"]);
    } else {
        echo json_encode(["success" => false, "message" => "No se pudo actualizar"]);
    }
    $stmt->close();
    $conn->close();
}
?>
