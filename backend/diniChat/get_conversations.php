<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/dbConfig.php';

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

$stmt = $mysqli->prepare("SELECT id, title, created_at FROM conversations WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$conversations = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['success' => true, 'conversations' => $conversations]);

$stmt->close();
$mysqli->close();
?>
