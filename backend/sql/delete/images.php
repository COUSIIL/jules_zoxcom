<?php

header("Content-Type: application/json; charset=UTF-8");

// Inclure config BDD
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Fichier de configuration introuvable.']);
    exit;
}
require_once $configPath;

// Lecture des données JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['imageIds']) || !is_array($data['imageIds']) || empty($data['imageIds'])) {
    echo json_encode(['success' => false, 'message' => 'Aucun ID d’image fourni.']);
    exit;
}

// Nettoyage des IDs
$imageIds = array_map('intval', $data['imageIds']);

// Préparer les placeholders pour la requête SQL
$placeholders = implode(',', array_fill(0, count($imageIds), '?'));
$types = str_repeat('i', count($imageIds));

// Récupération des chemins des fichiers à supprimer
$stmt = $mysqli->prepare("SELECT id, path FROM images WHERE id IN ($placeholders)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erreur de préparation SQL : ' . $mysqli->error]);
    exit;
}
$stmt->bind_param($types, ...$imageIds);
$stmt->execute();
$result = $stmt->get_result();

$filesToDelete = [];
$validIds = [];
while ($row = $result->fetch_assoc()) {
    $validIds[] = $row['id'];
    $filesToDelete[] = $row['path']; // Ce champ doit contenir le nom du fichier (ex: "az98g8sd.jpg")
}
$stmt->close();

// Suppression physique des fichiers
$deletedFiles = [];
foreach ($filesToDelete as $filePath) {
    $filePath = basename($filePath);

    $fullPath = __DIR__ . '/../../../uploads/images/' . $filePath;
    if (file_exists($fullPath)) {
        if (unlink($fullPath)) {
            $deletedFiles[] = $filePath;
        }
    }
}

// Suppression des entrées BDD
if (!empty($validIds)) {
    $placeholders = implode(',', array_fill(0, count($validIds), '?'));
    $types = str_repeat('i', count($validIds));

    $stmt = $mysqli->prepare("DELETE FROM images WHERE id IN ($placeholders)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Erreur SQL (delete) : ' . $mysqli->error]);
        exit;
    }

    $stmt->bind_param($types, ...$validIds);
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => count($validIds) . " image(s) supprimée(s) avec succès.",
            'deleted_ids' => $validIds,
            'deleted_files' => $deletedFiles
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression en BDD : ' . $mysqli->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Aucune image trouvée avec les IDs fournis.']);
}

$mysqli->close();
