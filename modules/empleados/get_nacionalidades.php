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

// Obtener el término de búsqueda si se proporciona
$search = isset($_GET['term']) ? $_GET['term'] : '';

// Construir la consulta
$query = "SELECT codigo, pais FROM nacionalidad WHERE 1=1";
if (!empty($search)) {
    $search = '%' . $search . '%';
    $query .= " AND (pais LIKE ?)";
}
$query .= " ORDER BY pais LIMIT 50";

// Preparar y ejecutar la consulta
$stmt = mysqli_prepare($conn, $query);
if (!empty($search)) {
    mysqli_stmt_bind_param($stmt, "s", $search);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Preparar la respuesta
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = [
        'id' => $row['codigo'],
        'text' => $row['pais'],
        'flag' => strtolower($row['codigo']) // Código para la bandera
    ];
}

// Devolver los resultados en formato JSON
header('Content-Type: application/json');
echo json_encode([
    'results' => $items
]);

// Cerrar la conexión
mysqli_close($conn);
?>
