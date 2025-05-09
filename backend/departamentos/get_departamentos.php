<?php
header('Content-Type: application/json');

require_once '../config.php';

try {
    $conn = getConnection();
    
    // Get all departments
    $query = "SELECT * FROM departamento ORDER BY nombre";
    $result = $conn->query($query);
    
    $departamentos = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $departamentos[] = [
                'codigo' => $row['codigo'],
                'nombre' => $row['nombre']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'departamentos' => $departamentos
    ]);
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
