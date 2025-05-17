<?php
ini_set('display_errors', 1); // Solo en desarrollo

// Configuración de manejadores de errores
set_exception_handler(function($e){
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Excepción: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function($errno, $errstr, $errfile, $errline){
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => "Error: $errstr en $errfile línea $errline"]);
    exit;
});

require_once '../config.php';
header('Content-Type: application/json');

// Verificación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validación de entrada
$codigo = $_POST['codigo'] ?? '';
if (empty($codigo)) {
    echo json_encode(['success' => false, 'message' => 'Falta el código del cargo']);
    exit;
}

// Inicialización de variables
$conn = null;
$stmtCheck = null;
$stmtDelete = null;
$success = false;

try {
    // Obtener conexión
    $conn = getConnection();
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    // 1. Verificar si el cargo existe
    $queryCheck = "SELECT codigo FROM cargo WHERE codigo = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param('s', $codigo);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("El cargo con código $codigo no existe");
    }
    
    // 2. Verificar si hay empleados asociados al cargo (para evitar problemas de integridad referencial)
    $queryCheckEmpleados = "SELECT COUNT(*) as total FROM empleados WHERE cargo = ?";
    $stmtCheckEmpleados = $conn->prepare($queryCheckEmpleados);
    $stmtCheckEmpleados->bind_param('s', $codigo);
    $stmtCheckEmpleados->execute();
    $totalEmpleados = $stmtCheckEmpleados->get_result()->fetch_assoc()['total'];
    
    if ($totalEmpleados > 0) {
        throw new Exception("No se puede eliminar el cargo porque tiene $totalEmpleados empleado(s) asociado(s)");
    }
    
    // 3. Eliminar el cargo
    $queryDelete = "DELETE FROM cargo WHERE codigo = ?";
    $stmtDelete = $conn->prepare($queryDelete);
    $stmtDelete->bind_param('s', $codigo);
    
    if (!$stmtDelete->execute()) {
        throw new Exception("Error al eliminar el cargo: " . $conn->error);
    }
    
    // Confirmar la cantidad de filas afectadas
    if ($stmtDelete->affected_rows === 0) {
        throw new Exception("No se pudo eliminar el cargo");
    }
    
    // Confirmar transacción
    $conn->commit();
    $success = true;
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($conn) {
        $conn->rollback();
    }
    throw $e;
} finally {
    // Cerrar recursos
    if ($stmtCheck) $stmtCheck->close();
    if (isset($stmtCheckEmpleados)) $stmtCheckEmpleados->close();
    if ($stmtDelete) $stmtDelete->close();
    if ($conn) $conn->close();
}

// Respuesta final
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Cargo eliminado correctamente' : 'Error eliminando el cargo'
]);
?>