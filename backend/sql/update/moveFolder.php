<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

$data = json_decode(file_get_contents('php://input'), true);
$folderId = $data['folder_id'] ?? null;
$parentId = $data['parent_id'] ?? null;

if (!$folderId) {
    echo json_encode(['success' => false, 'message' => 'Missing folder_id.']);
    exit;
}

if ($parentId == $folderId) {
    echo json_encode(['success' => false, 'message' => 'Cannot move a folder into itself.']);
    exit;
}

// Vérifier que le dossier existe
$stmtCheck = $mysqli->prepare("SELECT id FROM folder WHERE id = ?");
$stmtCheck->bind_param("i", $folderId);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
if ($resultCheck->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Folder not found.']);
    exit;
}

// Mise à jour du parent_id
if ($parentId === null) {
    $stmt = $mysqli->prepare("UPDATE folder SET parent_id = NULL WHERE id = ?");
    $stmt->bind_param("i", $folderId);
} else {
    $stmt = $mysqli->prepare("UPDATE folder SET parent_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $parentId, $folderId);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Folder moved successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
