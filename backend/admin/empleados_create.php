<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$conn = getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    exit;
}



$contrasenaHash = password_hash($data['contrasena'], PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO empleados
    (cedula, prefijo, tomo, asiento, nombre1, nombre2, apellido1, apellido2, apellidoc, genero, estado_civil, tipo_sangre, usa_ac, f_nacimiento, celular, telefono, correo, provincia, distrito, corregimiento, calle, casa, comunidad, nacionalidad, f_contra, cargo, departamento, estado, contraseña)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "sssssssssiisisssssssssssssssi",
    $data['cedula'],
    $data['prefijo'],
    $data['tomo'],
    $data['asiento'],
    $data['nombre1'],
    $data['nombre2'],
    $data['apellido1'],
    $data['apellido2'],
    $data['apellidoc'],
    $data['genero'],
    $data['estado_civil'],
    $data['tipo_sangre'],
    $data['usa_ac'],
    $data['f_nacimiento'],
    $data['celular'],
    $data['telefono'],
    $data['correo'],
    $data['provincia'],
    $data['distrito'],
    $data['corregimiento'],
    $data['calle'],
    $data['casa'],
    $data['comunidad'],
    $data['nacionalidad'],
    $data['f_contra'],
    $data['cargo'],
    $data['departamento'],
    $data['estado'],
    $contrasenaHash
);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Empleado registrado exitosamente"]);
} else {
    if ($conn->errno == 1062) { // 1062 = duplicate entry
        echo json_encode(["success" => false, "message" => "Ya existe un empleado con esa cédula"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al registrar empleado: " . $conn->error]);
    }
}

$stmt->close();
$conn->close();

