<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

try {
    $json = file_get_contents('php://input');
    if (empty($json)) throw new Exception('No se recibieron datos');

    $input = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON malformado: ' . json_last_error_msg());
    }

    // Validación de campos esenciales
    $camposRequeridos = ['cedula', 'nombre1', 'apellido1', 'genero', 'f_nacimiento'];
    foreach ($camposRequeridos as $campo) {
        if (empty($input[$campo])) {
            throw new Exception("Campo requerido: $campo");
        }
    }

    $db = getConnection();
    $db->set_charset("utf8mb4"); // Asegurar codificación

    // Mapeo de datos con valores por defecto
    $map = [
        'prefijo' => 's', 'tomo' => 's', 'asiento' => 's',
        'nombre1' => 's', 'nombre2' => 's', 'apellido1' => 's',
        'apellido2' => 's', 'apellidoc' => 's', 'genero' => 'i',
        'estado_civil' => 'i', 'tipo_sangre' => 's', 'usa_ac' => 'i',
        'f_nacimiento' => 's', 'celular' => 's', 'telefono' => 's',
        'correo' => 's', 'provincia' => 's', 'distrito' => 's',
        'corregimiento' => 's', 'calle' => 's', 'casa' => 's',
        'comunidad' => 's', 'nacionalidad' => 's', 'f_contra' => 's',
        'cargo' => 's', 'departamento' => 's', 'estado' => 'i',
        'cedula' => 's'
    ];

    $params = [];
    $tipos = '';
    foreach ($map as $key => $type) {
        $value = $input[$key] ?? ($type === 'i' ? 0 : '');
        $$key = $type === 'i' ? (int)$value : $db->real_escape_string($value);
        $params[] = $$key;
        $tipos .= $type;
    }

    $stmt = $db->prepare("UPDATE empleados SET
        prefijo=?, tomo=?, asiento=?, nombre1=?, nombre2=?, apellido1=?, apellido2=?, apellidoc=?,
        genero=?, estado_civil=?, tipo_sangre=?, usa_ac=?, f_nacimiento=?, celular=?, telefono=?, correo=?,
        provincia=?, distrito=?, corregimiento=?, calle=?, casa=?, comunidad=?, nacionalidad=?, f_contra=?,
        cargo=?, departamento=?, estado=? WHERE cedula=?");

    if (!$stmt) throw new Exception("Error preparando query: " . $db->error);

    $stmt->bind_param($tipos, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Error ejecutando: " . $stmt->error);
    }

    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Actualizado correctamente']);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}