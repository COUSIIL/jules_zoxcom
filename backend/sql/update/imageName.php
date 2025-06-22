<?php
header("Content-Type: application/json; charset=UTF-8");

// Inclure la configuration de la BDD
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier les champs requis
if (!isset($data['id']) || !isset($data['new_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields (id, new_name).']);
    exit;
}

$id = (int) $data['id'];
$new_name = trim($data['new_name']);

// Sécurité : éviter les noms vides
if ($new_name === '') {
    echo json_encode(['success' => false, 'message' => 'New name cannot be empty.']);
    exit;
}

// Vérifier si le dossier existe
$check_query = $mysqli->prepare("SELECT id FROM images WHERE id = ?");
$check_query->bind_param("i", $id);
$check_query->execute();
$check_result = $check_query->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Image not found.']);
    exit;
}

// Effectuer la mise à jour
$update_query = $mysqli->prepare("UPDATE images SET name = ?, updated_at = NOW() WHERE id = ?");
$update_query->bind_param("si", $new_name, $id);

if ($update_query->execute()) {
    echo json_encode(['success' => true, 'message' => 'Folder name updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating folder: ' . $mysqli->error]);
}

$mysqli->close();
