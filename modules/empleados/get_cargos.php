<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Incluir archivo de configuración
require_once "../../config/db.php";

// Verificar si se recibió el parámetro departamento
if (isset($_GET['departamento']) && !empty($_GET['departamento'])) {
    $departamento = $_GET['departamento'];
    
    // Consultar cargos
    $sql = "SELECT codigo, nombre FROM cargo WHERE dep_codigo = ? ORDER BY nombre";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $departamento);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            $cargos = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $cargos[] = [
                    'codigo' => $row['codigo'],
                    'nombre' => $row['nombre']
                ];
            }
            
            // Devolver los cargos en formato JSON
            header('Content-Type: application/json');
            echo json_encode($cargos);
        } else {
            // Error al ejecutar la consulta
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error al ejecutar la consulta']);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        // Error al preparar la consulta
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al preparar la consulta']);
    }
} else {
    // No se recibió el parámetro departamento
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Parámetro departamento faltante']);
}

// Cerrar conexión
mysqli_close($conn);
?>
