<?php
header('Content-Type: application/json');

require_once '../config.php';

try {
    $conn = getConnection();
    
    // Get total employees count
    $totalQuery = "SELECT COUNT(*) as total FROM empleados";
    $totalResult = $conn->query($totalQuery);
    $totalRow = $totalResult->fetch_assoc();
    $totalEmployees = $totalRow['total'];
    
    // If no employees, return empty stats
    if ($totalEmployees == 0) {
        echo json_encode([
            'success' => true,
            'total_employees' => 0,
            'active_employees' => 0,
            'inactive_employees' => 0,
            'departments' => []
        ]);
        exit;
    }
    
    // Get active employees count (where estado = 'activo')
    // Modificada para aceptar 'activo' en cualquier combinación de mayúsculas/minúsculas y TRIM espacios
    $activeQuery = "SELECT COUNT(*) as active FROM empleados WHERE TRIM(LOWER(estado)) = 'activo'";
    $activeResult = $conn->query($activeQuery);
    $activeRow = $activeResult->fetch_assoc();
    $activeEmployees = $activeRow['active'];
    
    // Get inactive employees count - CORREGIDA
    // Ahora cuenta como inactivos solo aquellos que explícitamente tienen un estado diferente de 'activo'
    // Nota: Los valores NULL o vacíos ya no se cuentan automáticamente como inactivos
    $inactiveQuery = "SELECT COUNT(*) as inactive FROM empleados WHERE 
                     TRIM(LOWER(estado)) != 'activo' AND 
                     estado IS NOT NULL AND 
                     TRIM(estado) != ''";
    $inactiveResult = $conn->query($inactiveQuery);
    $inactiveRow = $inactiveResult->fetch_assoc();
    $inactiveEmployees = $inactiveRow['inactive'];
    
    // Get department distribution
    $deptQuery = "SELECT d.codigo, d.nombre, COUNT(e.cedula) as employee_count 
                 FROM empleados e 
                 LEFT JOIN departamento d ON e.departamento = d.codigo 
                 GROUP BY d.codigo, d.nombre 
                 HAVING COUNT(e.cedula) > 0
                 ORDER BY employee_count DESC";
    
    $deptResult = $conn->query($deptQuery);
    
    $departments = [];
    
    // Handle case where department table might be empty
    if ($deptResult) {
        while ($row = $deptResult->fetch_assoc()) {
            // Calculate percentage
            $percentage = ($row['employee_count'] / $totalEmployees) * 100;
            
            $departments[] = [
                'codigo' => $row['codigo'] ?? 'N/A',
                'nombre' => $row['nombre'] ?? 'Sin Departamento',
                'employee_count' => (int)$row['employee_count'],
                'percentage' => round($percentage, 1)
            ];
        }
    }
    
    // In case we have employees but no departments assigned
    if (empty($departments) && $totalEmployees > 0) {
        // Get employees with no department assigned
        $noDeptQuery = "SELECT COUNT(*) as count FROM empleados WHERE departamento IS NULL OR departamento = ''";
        $noDeptResult = $conn->query($noDeptQuery);
        $noDeptRow = $noDeptResult->fetch_assoc();
        $noDeptCount = $noDeptRow['count'];
        
        if ($noDeptCount > 0) {
            $percentage = ($noDeptCount / $totalEmployees) * 100;
            
            $departments[] = [
                'codigo' => 'N/A',
                'nombre' => 'Sin Departamento',
                'employee_count' => $noDeptCount,
                'percentage' => round($percentage, 1)
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'total_employees' => (int)$totalEmployees,
        'active_employees' => (int)$activeEmployees,
        'inactive_employees' => (int)$inactiveEmployees,
        'departments' => $departments
    ]);
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
