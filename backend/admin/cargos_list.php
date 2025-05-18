<?php
require_once __DIR__ . '/../config.php';


$conn = getConnection();

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";

$sql = "
    SELECT c.codigo, c.nombre, d.nombre AS departamento
    FROM cargo c
    LEFT JOIN departamento d ON c.dep_codigo = d.codigo
";
if ($search !== "") {
    $sql .= " WHERE c.codigo LIKE '%$search%' OR c.nombre LIKE '%$search%' OR d.nombre LIKE '%$search%'";
}
$sql .= " ORDER BY c.nombre ASC";

$result = $conn->query($sql);

$cargos = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cargos[] = [
            "codigo" => $row["codigo"],
            "nombre" => $row["nombre"],
            "departamento" => $row["departamento"]
        ];
    }
}

echo json_encode($cargos);

$conn->close();
?>
