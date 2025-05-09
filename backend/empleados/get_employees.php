<?php
header('Content-Type: application/json');

require_once '../config.php';

try {
    $conn = getConnection();
    
    // Parámetros de filtrado opcionales
    $_POST = json_decode(file_get_contents('php://input'), true);
    $departamento = isset($_POST['departamento']) ? $_POST['departamento'] : null;
    $estado = isset($_POST['estado']) ? $_POST['estado'] : null;
    
    // Construir consulta base
    $query = "SELECT e.*, d.nombre as departamento_nombre, c.nombre as cargo_nombre 
              FROM empleados e 
              LEFT JOIN departamento d ON e.departamento = d.codigo 
              LEFT JOIN cargo c ON e.cargo = c.codigo";
    
    // Añadir condiciones de filtrado si existen
    $whereConditions = [];
    $params = [];
    $types = "";
    
    if ($departamento) {
        $whereConditions[] = "e.departamento = ?";
        $params[] = $departamento;
        $types .= "s";
    }
    
    if ($estado) {
        if (strtolower($estado) === 'activo') {
            $whereConditions[] = "TRIM(LOWER(e.estado)) = 'activo'";
        } else if (strtolower($estado) === 'inactivo') {
            $whereConditions[] = "TRIM(LOWER(e.estado)) != 'activo' AND e.estado IS NOT NULL AND TRIM(e.estado) != ''";
        }
    }
    
    // Agregar WHERE si hay condiciones
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    // Ordenar resultados
    $query .= " ORDER BY e.apellido1, e.nombre1";
    
    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = [
            'cedula' => $row['cedula'],
            'nombre_completo' => trim($row['nombre1'] . ' ' . $row['nombre2'] . ' ' . $row['apellido1'] . ' ' . $row['apellido2']),
            'genero' => $row['genero'],
            'departamento' => $row['departamento'],
            'departamento_nombre' => $row['departamento_nombre'],
            'cargo' => $row['cargo'],
            'cargo_nombre' => $row['cargo_nombre'],
            'estado' => $row['estado'],
            'celular' => $row['celular'],
            'fecha_contratacion' => $row['f_contra']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($employees),
        'employees' => $employees
    ]);
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
