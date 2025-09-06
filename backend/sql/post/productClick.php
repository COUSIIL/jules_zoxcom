<?php
header('Content-Type: application/json; charset=UTF-8');

// Pré-vol pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Chargement config MySQL
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'DB config not found.']));
}
require_once $configPath; // $mysqli

// Création de la table (correction de la virgule après click INT)
$tableSQL = <<<SQL
CREATE TABLE IF NOT EXISTS productClick (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    product_id INT NOT NULL,
    click INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if ($mysqli->query($tableSQL) === false) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Create table error: ' . $mysqli->error]));
}

// Lecture JSON
$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? intval($input['product_id']) : 0;

if ($product_id <= 0) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Invalid or missing product_id']));
}

// Vérifie si un enregistrement existe pour ce produit
$check = $mysqli->prepare("SELECT id, click FROM productClick WHERE product_id = ?");
$check->bind_param("i", $product_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Existant : on incrémente le compteur
    $row = $result->fetch_assoc();
    $update = $mysqli->prepare("UPDATE productClick SET click = click + 1 WHERE product_id = ?");
    $update->bind_param("i", $product_id);
    $success = $update->execute();
    $update->close();
} else {
    // Nouveau : on insère la ligne
    $insert = $mysqli->prepare("INSERT INTO productClick (product_id) VALUES (?)");
    $insert->bind_param("i", $product_id);
    $success = $insert->execute();
    $insert->close();
}

$check->close();

echo json_encode([
    'success' => $success,
    'message' => $success ? 'click updated successfully' : 'Failed to update click'
]);
?>
