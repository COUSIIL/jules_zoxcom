<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Connexion à la base de données
require_once __DIR__ . '/../../../backend/config/dbConfig.php';

// Lecture des données JSON POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    response(false, 'Invalid JSON data');
}

// Extraction sécurisée des champs
$method         = $data['method']         ?? '';
$deliveryName   = $data['delivery_name']  ?? '';
$dropAreaName   = $data['drop_area_name'] ?? '';
$dropAreaId     = $data['drop_area_id']   ?? null;
$returnFree     = !empty($data['return_free']) ? 1 : 0;
$includeFees    = !empty($data['include_fees']) ? 1 : 0;
$createdAt      = date('Y-m-d H:i:s');

// Validation minimale
if (!$method || !$deliveryName || !$dropAreaName || $dropAreaId === null) {
    response(false, 'Missing required fields');
}

// Création de la table si elle n’existe pas
$createTable = "
CREATE TABLE IF NOT EXISTS deliver_method (
    id INT AUTO_INCREMENT PRIMARY KEY,
    method VARCHAR(100) NOT NULL UNIQUE,
    delivery_name VARCHAR(255) NOT NULL,
    drop_area_name VARCHAR(255) NOT NULL,
    drop_area_id INT NOT NULL,
    return_free BOOLEAN DEFAULT 0,
    include_fees BOOLEAN DEFAULT 0,
    active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (!$mysqli->query($createTable)) {
    response(false, 'Failed to create table: ' . $mysqli->error);
}

// Vérifie si la méthode existe déjà
$check = $mysqli->prepare("SELECT id FROM deliver_method WHERE method = ?");
$check->bind_param("s", $method);
$check->execute();
$result = $check->get_result();
$exists = $result->num_rows > 0;
$check->close();

if ($exists) {
    // Mettre à jour l'enregistrement existant
    $update = $mysqli->prepare("
        UPDATE deliver_method 
        SET delivery_name = ?, drop_area_name = ?, drop_area_id = ?, return_free = ?, include_fees = ?, created_at = ?
        WHERE method = ?
    ");
    $update->bind_param("ssiisss", $deliveryName, $dropAreaName, $dropAreaId, $returnFree, $includeFees, $createdAt, $method);

    if ($update->execute()) {
        response(true, 'Delivery method updated successfully');
    } else {
        response(false, 'Update failed: ' . $update->error);
    }

    $update->close();
} else {
    // Insérer un nouvel enregistrement
    $insert = $mysqli->prepare("
        INSERT INTO deliver_method (method, delivery_name, drop_area_name, drop_area_id, return_free, include_fees, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $insert->bind_param("sssiiis", $method, $deliveryName, $dropAreaName, $dropAreaId, $returnFree, $includeFees, $createdAt);

    if ($insert->execute()) {
        response(true, 'Delivery method inserted successfully');
    } else {
        response(false, 'Insert failed: ' . $insert->error);
    }

    $insert->close();
}

/**
 * Fonction de réponse JSON
 */
function response($success, $message)
{
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}
