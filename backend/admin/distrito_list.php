<?php
require_once __DIR__ . '/../config.php';
$conn = getConnection();
$provincia = $_GET['provincia'] ?? '';
$stmt = $conn->prepare("SELECT codigo_distrito AS codigo, nombre_distrito AS nombre, codigo_provincia FROM distrito WHERE codigo_provincia=?");
$stmt->bind_param("s", $provincia);
$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;
echo json_encode($data);
$conn->close();
?>
