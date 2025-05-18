<?php
require_once '../config.php';
ensureCleanOutput();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$codigo = $_POST['codigo'] ?? '';
if (empty($codigo)) {
    echo json_encode(['success' => false, 'message' => 'Código de departamento requerido']);
    exit;
}

try {
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM cargo WHERE dep_codigo = ?");
    $stmt->bind_param('s', $codigo);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalCargos = $row['total'] ?? 0;

    if ($totalCargos > 0) {
        echo json_encode([
            'success' => false,
            'message' => "No se puede eliminar el departamento porque tiene $totalCargos cargo(s) asociado(s)"
        ]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM departamento WHERE codigo = ?");
    $stmt->bind_param('s', $codigo);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Departamento eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el departamento o no se pudo eliminar']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Excepción: ' . $e->getMessage()]);
}
exit;
