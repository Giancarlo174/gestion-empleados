<?php
// Este archivo sirve para diagnosticar problemas con la API
// Responde en formato JSON para que puedas verificar que todo funciona correctamente

// Incluir configuración
require_once 'config.php';

// Siempre usar el formato JSON para la respuesta
ensureCleanOutput();

// Verificar que estamos recibiendo el formato JSON correcto
$result = [
    'success' => true,
    'message' => 'Diagnóstico completado exitosamente',
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'],
    'request_time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
    'post_max_size' => ini_get('post_max_size'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'headers' => []
];

// Verificar headers
$headers = getallheaders();
foreach ($headers as $name => $value) {
    $result['headers'][$name] = $value;
}

// Verificar conexión a la base de datos
try {
    $conn = getConnection();
    $result['database_connection'] = 'ok';
    
    // Verificar tablas
    $tables = ['usuarios', 'empleados', 'departamento', 'cargo'];
    $result['tables'] = [];
    
    foreach ($tables as $table) {
        $tableResult = $conn->query("SHOW TABLES LIKE '{$table}'");
        $result['tables'][$table] = ($tableResult->num_rows > 0) ? 'exists' : 'missing';
    }
    
    $conn->close();
} catch (Exception $e) {
    $result['success'] = false;
    $result['database_connection'] = 'error';
    $result['database_error'] = $e->getMessage();
}

// Devolver el resultado en formato JSON
echo json_encode($result, JSON_PRETTY_PRINT);
?>
