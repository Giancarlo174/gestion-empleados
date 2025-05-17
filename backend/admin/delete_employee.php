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
$cedula = $_POST['cedula'] ?? '';
if (empty($cedula)) {
    echo json_encode(['success' => false, 'message' => 'Falta la cédula del empleado']);
    exit;
}

// Inicialización de variables
$conn = null;
$stmtSelect = null;
$stmtInsert = null;
$stmtDelete = null;
$success = false;

try {
    // Obtener conexión
    $conn = getConnection();
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    // 1. Primero obtener los datos del empleado
    $querySelect = "SELECT * FROM empleados WHERE cedula = ?";
    $stmtSelect = $conn->prepare($querySelect);
    $stmtSelect->bind_param('s', $cedula);
    $stmtSelect->execute();
    $result = $stmtSelect->get_result();
    $empleado = $result->fetch_assoc();
    
    if (!$empleado) {
        throw new Exception("Empleado no encontrado");
    }
    
    // 2. Insertar en e_eliminados con todas las columnas correctas
    $queryInsert = "INSERT INTO e_eliminados (
        cedula, prefijo, tomo, asiento, nombre1, nombre2, apellido1, apellido2, 
        apellidoc, genero, estado_civil, tipo_sangre, usa_ac, f_nacimiento, 
        celular, telefono, correo, provincia, distrito, corregimiento, calle, 
        casa, comunidad, nacionalidad, f_contra, cargo, departamento, estado, 
        f_eliminacion
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
    )";
    
    $stmtInsert = $conn->prepare($queryInsert);
    
    // Bind de todos los parámetros
    $stmtInsert->bind_param(
        'ssisssssssssssssssssssssssss',
        $empleado['cedula'],
        $empleado['prefijo'],
        $empleado['tomo'],
        $empleado['asiento'],
        $empleado['nombre1'],
        $empleado['nombre2'],
        $empleado['apellido1'],
        $empleado['apellido2'],
        $empleado['apellidoc'],
        $empleado['genero'],
        $empleado['estado_civil'],
        $empleado['tipo_sangre'],
        $empleado['usa_ac'],
        $empleado['f_nacimiento'],
        $empleado['celular'],
        $empleado['telefono'],
        $empleado['correo'],
        $empleado['provincia'],
        $empleado['distrito'],
        $empleado['corregimiento'],
        $empleado['calle'],
        $empleado['casa'],
        $empleado['comunidad'],
        $empleado['nacionalidad'],
        $empleado['f_contra'],
        $empleado['cargo'],
        $empleado['departamento'],
        $empleado['estado']
    );
    
    if (!$stmtInsert->execute()) {
        throw new Exception("Error al insertar en e_eliminados: " . $conn->error);
    }
    
    // 3. Eliminar de la tabla principal
    $queryDelete = "DELETE FROM empleados WHERE cedula = ?";
    $stmtDelete = $conn->prepare($queryDelete);
    $stmtDelete->bind_param('s', $cedula);
    
    if (!$stmtDelete->execute()) {
        throw new Exception("Error al eliminar de empleados: " . $conn->error);
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
    if ($stmtSelect) $stmtSelect->close();
    if ($stmtInsert) $stmtInsert->close();
    if ($stmtDelete) $stmtDelete->close();
    if ($conn) $conn->close();
}

// Respuesta final
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Empleado eliminado correctamente' : 'Error eliminando empleado'
]);
?>