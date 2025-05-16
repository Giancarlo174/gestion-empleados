<?php
// Limpiar cualquier salida previa para evitar conflictos con JSON
if (ob_get_level()) ob_end_clean();

// Incluir la configuración de la base de datos
require_once '../config.php';

// Establecer cabeceras para JSON y CORS
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Estructura de respuesta
$response = [
    'success' => false,
    'message' => '',
    'data' => [
        'totalEmpleados' => 0,
        'empleadosActivos' => 0,
        'empleadosInactivos' => 0,
        'departamentos' => []
    ]
];

try {
    // Obtener conexión a la base de datos
    $conn = getConnection();
    
    // 1. Consultar el total de empleados (sin filtrar por f_eliminacion)
    $sql_total = "SELECT COUNT(*) as total FROM empleados";
    $result_total = $conn->query($sql_total);
    
    if (!$result_total) {
        throw new Exception("Error al consultar el total: " . $conn->error);
    }
    
    $response['data']['totalEmpleados'] = (int)$result_total->fetch_assoc()['total'];
    
    // 2. Consultar empleados activos (estado = 1)
    $sql_activos = "SELECT COUNT(*) as total FROM empleados WHERE estado = 1";
    $result_activos = $conn->query($sql_activos);
    
    if (!$result_activos) {
        throw new Exception("Error al consultar activos: " . $conn->error);
    }
    
    $response['data']['empleadosActivos'] = (int)$result_activos->fetch_assoc()['total'];
    
    // 3. Consultar empleados inactivos (estado = 0)
    $sql_inactivos = "SELECT COUNT(*) as total FROM empleados WHERE estado = 0";
    $result_inactivos = $conn->query($sql_inactivos);
    
    if (!$result_inactivos) {
        throw new Exception("Error al consultar inactivos: " . $conn->error);
    }
    
    $response['data']['empleadosInactivos'] = (int)$result_inactivos->fetch_assoc()['total'];
    
    // 4. Consultar la distribución por departamentos - SOLO LOS QUE TIENEN EMPLEADOS
    $sql_departamentos = "SELECT d.nombre, COUNT(e.cedula) as total_empleados 
                          FROM departamento d
                          LEFT JOIN empleados e ON d.codigo = e.departamento
                          GROUP BY d.codigo, d.nombre
                          HAVING total_empleados > 0
                          ORDER BY total_empleados DESC";
    
    $result_departamentos = $conn->query($sql_departamentos);
    
    if (!$result_departamentos) {
        throw new Exception("Error al consultar departamentos: " . $conn->error);
    }
    
    $departamentos = [];
    while ($row = $result_departamentos->fetch_assoc()) {
        // Calcular el porcentaje
        $total_emp = $response['data']['totalEmpleados'];
        $porcentaje = $total_emp > 0 ? ($row['total_empleados'] / $total_emp) * 100 : 0;
        
        $departamentos[] = [
            'nombre' => $row['nombre'],
            'totalEmpleados' => (int)$row['total_empleados'],
            'porcentaje' => round($porcentaje, 1)
        ];
    }
    
    $response['data']['departamentos'] = $departamentos;
    $response['success'] = true;
    $response['message'] = 'Datos obtenidos correctamente';
    
    // Cerrar la conexión
    $conn->close();
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    
    // Registrar el error para depuración
    error_log('Dashboard Stats Error: ' . $e->getMessage());
}

// Devolver la respuesta como JSON
echo json_encode($response);
?>
