<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

$data = json_decode(file_get_contents('php://input'), true);
$imageId = $data['image_id'] ?? null;
$folderId = $data['folder_id'] ?? null;

if (!$imageId || !$folderId) {
    echo json_encode(['success' => false, 'message' => 'Missing image_id or folder_id.']);
    exit;
}

// Vérifier que l'image existe
$stmtCheck = $mysqli->prepare("SELECT id FROM images WHERE id = ?");
$stmtCheck->bind_param("i", $imageId);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
if ($resultCheck->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Image not found.']);
    exit;
}

// Mise à jour de folder_id
$stmt = $mysqli->prepare("UPDATE images SET folder_id = ? WHERE id = ?");
$stmt->bind_param("ii", $folderId, $imageId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Image moved successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
