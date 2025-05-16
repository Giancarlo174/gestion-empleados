<?php
require_once __DIR__ . '/../config.php';

ensureCleanOutput(); // Limpia headers y output

$conn = getConnection(); // ¡Así sí funciona!

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";

$sql = "SELECT codigo, nombre FROM departamento";
if ($search !== "") {
    $sql .= " WHERE codigo LIKE '%$search%' OR nombre LIKE '%$search%'";
}
$sql .= " ORDER BY nombre ASC";

$result = $conn->query($sql);

$departments = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = [
            "codigo" => $row["codigo"],
            "nombre" => $row["nombre"]
        ];
    }
}

echo json_encode($departments);

$conn->close();
?>
