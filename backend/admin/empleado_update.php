<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

try {
    // Validar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }

    // Obtener y validar JSON
    $json = file_get_contents('php://input');
    if (empty($json)) {
        throw new Exception('No se recibieron datos', 400);
    }

    $input = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON inválido: ' . json_last_error_msg(), 400);
    }

    // Validar cédula
    if (empty($input['cedula'])) {
        throw new Exception('La cédula es requerida', 400);
    }

    $db = getConnection();
    $db->set_charset("utf8mb4");

    // PRIMERO VERIFICAR QUE EL EMPLEADO EXISTE
    $checkStmt = $db->prepare("SELECT cedula FROM empleados WHERE cedula = ?");
    $checkStmt->bind_param("s", $input['cedula']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        throw new Exception("Empleado con cédula {$input['cedula']} no existe", 404);
    }

    // Construir la consulta dinámicamente
    $fieldsToUpdate = [
        'prefijo' => 's', 'tomo' => 's', 'asiento' => 's',
        'nombre1' => 's', 'nombre2' => 's', 'apellido1' => 's',
        'apellido2' => 's', 'apellidoc' => 's', 'genero' => 'i',
        'estado_civil' => 'i', 'tipo_sangre' => 's', 'usa_ac' => 'i',
        'f_nacimiento' => 's', 'celular' => 's', 'telefono' => 's',
        'correo' => 's', 'provincia' => 's', 'distrito' => 's',
        'corregimiento' => 's', 'calle' => 's', 'casa' => 's',
        'comunidad' => 's', 'nacionalidad' => 's', 'f_contra' => 's',
        'cargo' => 's', 'departamento' => 's', 'estado' => 'i'
    ];

    $setParts = [];
    $params = [];
    $types = '';

    foreach ($fieldsToUpdate as $field => $type) {
        if (isset($input[$field])) {
            $setParts[] = "$field = ?";
            $params[] = $input[$field];
            $types .= $type;
        }
    }

    // Agregar cédula al final para WHERE
    $params[] = $input['cedula'];
    $types .= 's';

    if (empty($setParts)) {
        throw new Exception('No hay campos para actualizar', 400);
    }

    $query = "UPDATE empleados SET " . implode(', ', $setParts) . " WHERE cedula = ?";
    $stmt = $db->prepare($query);

    if (!$stmt) {
        throw new Exception("Error preparando query: " . $db->error, 500);
    }

    // Vincular parámetros dinámicamente
    $bindNames = [$types];
    for ($i = 0; $i < count($params); $i++) {
        $bindNames[] = &$params[$i];
    }

    call_user_func_array([$stmt, 'bind_param'], $bindNames);

    if (!$stmt->execute()) {
        throw new Exception("Error ejecutando actualización: " . $stmt->error, 500);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Empleado actualizado correctamente',
        'affected_rows' => $stmt->affected_rows
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode() ?: 500
    ]);
}
?>