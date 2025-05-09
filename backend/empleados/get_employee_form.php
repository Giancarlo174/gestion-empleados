<?php
header('Content-Type: application/json');

require_once '../config.php';

// Get cedula from request
$_POST = json_decode(file_get_contents('php://input'), true);

if (!isset($_POST['cedula'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Cedula is required'
    ]);
    exit;
}

$cedula = $_POST['cedula'];

try {
    $conn = getConnection();
    
    // Get detailed employee information
    $stmt = $conn->prepare("SELECT * FROM empleados WHERE cedula = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $employee = $result->fetch_assoc();
        
        // Get department name
        $deptName = "";
        if (!empty($employee['departamento'])) {
            $deptStmt = $conn->prepare("SELECT nombre FROM departamento WHERE codigo = ?");
            $deptStmt->bind_param("s", $employee['departamento']);
            $deptStmt->execute();
            $deptResult = $deptStmt->get_result();
            if ($deptResult->num_rows > 0) {
                $deptRow = $deptResult->fetch_assoc();
                $deptName = $deptRow['nombre'];
            }
            $deptStmt->close();
        }
        
        // Get cargo name
        $cargoName = "";
        if (!empty($employee['cargo'])) {
            $cargoStmt = $conn->prepare("SELECT nombre FROM cargo WHERE codigo = ?");
            $cargoStmt->bind_param("s", $employee['cargo']);
            $cargoStmt->execute();
            $cargoResult = $cargoStmt->get_result();
            if ($cargoResult->num_rows > 0) {
                $cargoRow = $cargoResult->fetch_assoc();
                $cargoName = $cargoRow['nombre'];
            }
            $cargoStmt->close();
        }
        
        // Get province name
        $provinceName = "";
        if (!empty($employee['provincia'])) {
            $provStmt = $conn->prepare("SELECT nombre_provincia FROM provincia WHERE codigo_provincia = ?");
            $provStmt->bind_param("s", $employee['provincia']);
            $provStmt->execute();
            $provResult = $provStmt->get_result();
            if ($provResult->num_rows > 0) {
                $provRow = $provResult->fetch_assoc();
                $provinceName = $provRow['nombre_provincia'];
            }
            $provStmt->close();
        }
        
        // Format the data for form display
        $formData = [
            'success' => true,
            'cedula' => $employee['cedula'],
            'nombres' => trim($employee['nombre1'] . ' ' . $employee['nombre2']),
            'apellidos' => trim($employee['apellido1'] . ' ' . $employee['apellido2']),
            'apellido_casada' => $employee['apellidoc'] ?? '',
            'genero' => $employee['genero'] ?? '',
            'estado_civil' => $employee['estado_civil'] ?? '',
            'tipo_sangre' => $employee['tipo_sangre'] ?? '',
            'fecha_nacimiento' => $employee['f_nacimiento'] ?? '',
            'celular' => $employee['celular'] ?? '',
            'telefono' => $employee['telefono'] ?? '',
            'provincia' => $employee['provincia'] ?? '',
            'provincia_nombre' => $provinceName,
            'distrito' => $employee['distrito'] ?? '',
            'corregimiento' => $employee['corregimiento'] ?? '',
            'calle' => $employee['calle'] ?? '',
            'casa' => $employee['casa'] ?? '',
            'comunidad' => $employee['comunidad'] ?? '',
            'nacionalidad' => $employee['nacionalidad'] ?? '',
            'fecha_contratacion' => $employee['f_contra'] ?? '',
            'cargo' => $employee['cargo'] ?? '',
            'cargo_nombre' => $cargoName,
            'departamento' => $employee['departamento'] ?? '',
            'departamento_nombre' => $deptName,
            'estado' => $employee['estado'] ?? ''
        ];
        
        echo json_encode($formData);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Employee not found'
        ]);
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
