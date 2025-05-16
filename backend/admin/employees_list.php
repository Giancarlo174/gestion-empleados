<?php
// 1️⃣ Limpiar cualquier salida previa
if (ob_get_level()) ob_end_clean();

// 2️⃣ Incluir configuración de conexión
require_once '../config.php';

// 3️⃣ Cabeceras
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

// 4️⃣ Obtener parámetros
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// 5️⃣ Estructura inicial
$response = [
    'success' => false,
    'message' => '',
    'data'    => []   // aquí pondremos directamente el array de empleados
];

try {
    $conn = getConnection();

    // 6️⃣ Consulta base
    $sql = "SELECT
                e.cedula,
                e.nombre1, e.nombre2,
                e.apellido1, e.apellido2,
                e.estado,
                d.nombre AS departamento
            FROM empleados e
            LEFT JOIN departamento d
                ON e.departamento = d.codigo
            WHERE 1=1";

    // 7️⃣ Filtro de búsqueda
    if (!empty($search)) {
        $s = $conn->real_escape_string($search);
        $sql .= " AND (
                    e.cedula LIKE '%$s%' OR
                    CONCAT(e.nombre1, ' ', IFNULL(e.nombre2, '')) LIKE '%$s%' OR
                    CONCAT(e.apellido1, ' ', IFNULL(e.apellido2, '')) LIKE '%$s%' OR
                    d.nombre LIKE '%$s%'
                  )";
    }

    // 8️⃣ Filtro de estado
    if ($filter === 'active') {
        $sql .= " AND e.estado = 1";
    } elseif ($filter === 'inactive') {
        $sql .= " AND e.estado = 0";
    }

    // 9️⃣ Orden
    $sql .= " ORDER BY e.apellido1, e.nombre1";

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Error SQL: " . $conn->error);
    }

    // 🔟 Construir array de salida
    $empleados = [];
    while ($row = $result->fetch_assoc()) {
        $nombre = $row['nombre1'] . (!empty($row['nombre2']) ? ' ' . $row['nombre2'] : '');
        $apellido = $row['apellido1'] . (!empty($row['apellido2']) ? ' ' . $row['apellido2'] : '');

        $empleados[] = [
            'cedula'       => $row['cedula'],
            'nombre'       => $nombre,
            'apellido'     => $apellido,
            'departamento' => $row['departamento'] ?? 'Sin departamento',
            'estado'       => (int)$row['estado'],
            'estadoTexto'  => ($row['estado'] == 1) ? "Activo" : "Inactivo"
        ];
    }

    $response['success'] = true;
    $response['message'] = 'Empleados obtenidos correctamente';
    $response['data']    = $empleados;

    $conn->close();
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    // opcional: error_log($e->getMessage());
}

// 11️⃣ Devolver la respuesta unificada
echo json_encode($empleados);
?>
