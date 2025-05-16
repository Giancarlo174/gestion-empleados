<?php
require_once __DIR__ . '/../config.php';
$conn = getConnection();
$departamento = $_GET['departamento'] ?? '';
$stmt = $conn->prepare("SELECT codigo, nombre, dep_codigo FROM cargo WHERE dep_codigo=?");
$stmt->bind_param("s", $departamento);
$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;
echo json_encode($data);
$conn->close();
?>
