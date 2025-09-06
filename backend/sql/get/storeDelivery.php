<?php
header("Content-Type: application/json; charset=UTF-8");

// 1. Inclure la config DB
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}
require_once $configPath; // Définit $mysqli

// 2. Vérifie que la table existe (optionnel mais sûr)
$createTableSQL = <<<SQL
CREATE TABLE IF NOT EXISTS store_delivery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(45) NOT NULL UNIQUE,
    method VARCHAR(45) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if (!$mysqli->query($createTableSQL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Error creating table: ' . $mysqli->error
    ]);
    exit;
}

// 3. Requête pour récupérer toutes les méthodes
$query = "SELECT id, name, method, created_at, updated_at FROM store_delivery ORDER BY id DESC";
$result = $mysqli->query($query);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching data: ' . $mysqli->error
    ]);
    exit;
}

$methods = [];
while ($row = $result->fetch_assoc()) {
    $methods[] = $row;
}

echo json_encode([
    'success' => true,
    'message' => 'Delivery methods retrieved successfully.',
    'data' => $methods
]);

$mysqli->close();
