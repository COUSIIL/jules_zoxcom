<?php
header("Content-Type: application/json; charset=UTF-8");

// Inclure la configuration de la base de données
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found'
    ]);
    exit;
}

require_once $configPath; // Doit initialiser $mysqli

// Vérifie la connexion
if (!$mysqli || $mysqli->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'connection to database failed: ' . $mysqli->connect_error
    ]);
    exit;
}

// Création de la table si elle n'existe pas
$tableSQL = <<<SQL
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    power INT NOT NULL,
    image VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if ($mysqli->query($tableSQL) === false) {
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'message' => 'create table error: ' . $mysqli->error
    ]));
}

// Lecture de l'IP à supprimer
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode([
        'success' => false,
        'message' => 'customer not provided'
    ]);
    exit;
}

// Suppression de l'IP
$stmt = $mysqli->prepare("DELETE FROM customers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode([
        'success' => true,
        'message' => "customer deleted successfully"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "customer not found in the database"
    ]);
}

$stmt->close();
$mysqli->close();
