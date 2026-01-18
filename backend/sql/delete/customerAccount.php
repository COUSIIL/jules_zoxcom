<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../../backend/config/dbConfig.php';

$data = json_decode(file_get_contents("php://input"), true);
$headers = getallheaders();
$token = $headers['Authorization'] ?? $data['token'] ?? '';

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$stmt = $mysqli->prepare("SELECT id FROM customers WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}
$user = $res->fetch_assoc();

// Delete
$stmtDel = $mysqli->prepare("DELETE FROM customers WHERE id = ?");
$stmtDel->bind_param("i", $user['id']);

if ($stmtDel->execute()) {
    echo json_encode(['success' => true, 'message' => 'Account deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'Deletion failed']);
}
?>
