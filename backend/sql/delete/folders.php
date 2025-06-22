<?php

header("Content-Type: application/json; charset=UTF-8");

// Inclure la configuration de la BDD
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Récupérer les IDs des dossiers à supprimer
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['folder_ids']) || !is_array($data['folder_ids'])) {
    echo json_encode(['success' => false, 'message' => "Missing or invalid 'folder_ids' array."]);
    exit;
}

$folderIds = array_map('intval', $data['folder_ids']);

// Vérifie si les tables nécessaires existent
$requiredTables = ['folder', 'images'];
foreach ($requiredTables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => "Required table '$table' does not exist."]);
        exit;
    }
}

// Fonction récursive pour collecter tous les dossiers enfants
function getAllChildFolderIds($mysqli, $parentId) {
    $ids = [$parentId];
    $stmt = $mysqli->prepare("SELECT id FROM folder WHERE parent_id = ?");
    $stmt->bind_param("i", $parentId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ids = array_merge($ids, getAllChildFolderIds($mysqli, $row['id']));
    }
    return $ids;
}

$allToDelete = [];

foreach ($folderIds as $folderId) {
    $allToDelete = array_merge($allToDelete, getAllChildFolderIds($mysqli, $folderId));
}

$allToDelete = array_unique($allToDelete);

// Supprimer les images associées à ces dossiers
$imageDeleteStmt = $mysqli->prepare("DELETE FROM images WHERE folder_id = ?");
foreach ($allToDelete as $id) {
    $imageDeleteStmt->bind_param("i", $id);
    $imageDeleteStmt->execute();
}

// Supprimer les dossiers (les enfants d'abord)
$folderDeleteStmt = $mysqli->prepare("DELETE FROM folder WHERE id = ?");
foreach (array_reverse($allToDelete) as $id) {
    $folderDeleteStmt->bind_param("i", $id);
    $folderDeleteStmt->execute();
}

echo json_encode([
    'success' => true,
    'message' => 'Folders and their contents deleted successfully.',
    'deleted_folder_ids' => $allToDelete
]);

$mysqli->close();
