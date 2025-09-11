<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/dbConfig.php';
require_once __DIR__ . '/../config/init_db.php';

$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['user_id'] ?? null;
$title = $data['title'] ?? 'New Conversation';

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO conversations (user_id, title) VALUES (?, ?)");
$stmt->bind_param("is", $userId, $title);

if ($stmt->execute()) {
    $newConversationId = $mysqli->insert_id;
    echo json_encode(['success' => true, 'conversation_id' => $newConversationId]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create conversation: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
