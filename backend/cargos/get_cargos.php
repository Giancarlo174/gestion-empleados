<?php
header('Content-Type: application/json');

require_once '../config.php';

try {
    $conn = getConnection();
    
    // Get all cargos
    $query = "SELECT * FROM cargo ORDER BY nombre";
    $result = $conn->query($query);
    
    $cargos = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $cargos[] = [
                'codigo' => $row['codigo'],
                'nombre' => $row['nombre'],
                'dep_codigo' => $row['dep_codigo']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'cargos' => $cargos
    ]);
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
