<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
require_once $configPath;

require_once __DIR__ . '/../../security/auth.php';

$data = json_decode(file_get_contents('php://input'), true);

$token = $data['token'] ?? '';
$userId = verifyToken($mysqli, $token);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($data['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing order ID']);
    exit;
}

$id = intval($data['order_id']);

$stmt = $mysqli->prepare("DELETE FROM orders_archive WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Archived order deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete order: ' . $mysqli->error]);
}

$stmt->close();
$mysqli->close();
?>