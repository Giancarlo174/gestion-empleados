<?php
require_once '../config.php';
ensureCleanOutput();

$cedula = $_GET['cedula'] ?? '';
if (!$cedula) {
    echo json_encode(['success' => false, 'message' => 'Falta la cédula']);
    exit;
}

try {
    $conn = getConnection();

    $sql = "
        SELECT
            e.*,
            c.nombre AS nombre_cargo,
            d.nombre AS nombre_departamento,
            n.pais AS nacionalidad_nombre,
            p.nombre_provincia AS provincia_nombre,
            dis.nombre_distrito AS distrito_nombre,
            cor.nombre_corregimiento AS corregimiento_nombre
        FROM empleados e
        LEFT JOIN cargo c ON e.cargo = c.codigo
        LEFT JOIN departamento d ON e.departamento = d.codigo
        LEFT JOIN nacionalidad n ON e.nacionalidad = n.codigo
        LEFT JOIN provincia p ON e.provincia = p.codigo_provincia
        LEFT JOIN distrito dis 
            ON e.distrito = dis.codigo_distrito 
            AND e.provincia = dis.codigo_provincia
        LEFT JOIN corregimiento cor 
            ON e.corregimiento = cor.codigo_corregimiento
            AND e.distrito = cor.codigo_distrito
            AND e.provincia = cor.codigo_provincia
        WHERE e.cedula = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    $empleado = $result->fetch_assoc();
    if ($empleado) {
        // Limpiar valores nulos o vacíos para una mejor presentación en el cliente
        foreach (['calle', 'casa', 'comunidad', 'provincia_nombre', 'distrito_nombre', 'corregimiento_nombre'] as $field) {
            if (!isset($empleado[$field]) || $empleado[$field] === null || $empleado[$field] === '') {
                $empleado[$field] = '';
            }
        }
        echo json_encode(['success' => true, 'empleado' => $empleado]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Empleado no encontrado']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
exit;
