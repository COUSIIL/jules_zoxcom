<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/dbConfig.php';
require_once __DIR__ . '/../config/init_db.php';

$conversationId = $_GET['conversation_id'] ?? null;

if (!$conversationId) {
    echo json_encode(['success' => false, 'message' => 'Conversation ID is required.']);
    exit;
}

$stmt = $mysqli->prepare("SELECT id, role, content, created_at FROM messages WHERE conversation_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $conversationId);
$stmt->execute();
$result = $stmt->get_result();

$messages = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['success' => true, 'messages' => $messages]);

$stmt->close();
$mysqli->close();
?>
