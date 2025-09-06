<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Connexion à la base de données
require_once __DIR__ . '/../../../backend/config/dbConfig.php';

// ⚠️ Nom de la table : change si nécessaire
$table = 'deliver_method';

// Vérifie si la table existe
$checkTable = $mysqli->query("SHOW TABLES LIKE '$table'");
if ($checkTable->num_rows == 0) {
    response(false, "Table '$table' not found.");
}

// Requête SELECT
$query = "SELECT * FROM $table ORDER BY created_at DESC";
$result = $mysqli->query($query);

if (!$result) {
    response(false, "Database error: " . $mysqli->error);
}

$methods = [];

while ($row = $result->fetch_assoc()) {
    $methods[] = $row;
}

response(true, "Delivery methods fetched successfully", $methods);


/**
 * Fonction de réponse JSON
 */
function response($success, $message, $data = [])
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}
