<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/dbConfig.php';
require_once __DIR__ . '/../config/init_db.php';

$data = json_decode(file_get_contents('php://input'), true);

$conversationId = $data['conversation_id'] ?? null;

if (!$conversationId) {
    echo json_encode(['success' => false, 'message' => 'Conversation ID is required.']);
    exit;
}

$stmt = $mysqli->prepare("DELETE FROM conversations WHERE id = ?");
$stmt->bind_param("i", $conversationId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Conversation deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Conversation not found or already deleted.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete conversation: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
