<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/dbConfig.php';

$data = json_decode(file_get_contents('php://input'), true);

$conversationId = $data['conversation_id'] ?? null;
$newTitle = $data['title'] ?? null;

if (!$conversationId || !$newTitle) {
    echo json_encode(['success' => false, 'message' => 'Conversation ID and new title are required.']);
    exit;
}

$stmt = $mysqli->prepare("UPDATE conversations SET title = ? WHERE id = ?");
$stmt->bind_param("si", $newTitle, $conversationId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Conversation renamed successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Conversation not found or title is the same.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to rename conversation: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
