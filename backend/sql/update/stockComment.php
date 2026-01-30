<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['comment'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$id = (int)$data['id'];
$comment = trim($data['comment']);

$stmt = $mysqli->prepare("UPDATE product_stock SET comment = ? WHERE id = ?");
$stmt->bind_param("si", $comment, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Comment updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
