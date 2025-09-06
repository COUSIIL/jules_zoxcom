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
        'message' => 'Connection to database failed: ' . $mysqli->connect_error
    ]);
    exit;
}

// Création de la table si elle n'existe pas
$tableSQL = <<<SQL
CREATE TABLE IF NOT EXISTS banned_ips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if ($mysqli->query($tableSQL) === false) {
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'message' => 'Create table error: ' . $mysqli->error
    ]));
}

// Lecture de l'IP à supprimer
$data = json_decode(file_get_contents('php://input'), true);
$ip = $data['ip'] ?? null;

if (!$ip) {
    echo json_encode([
        'success' => false,
        'message' => 'IP address not provided'
    ]);
    exit;
}

// Suppression de l'IP
$stmt = $mysqli->prepare("DELETE FROM banned_ips WHERE ip_address = ?");
$stmt->bind_param("s", $ip);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode([
        'success' => true,
        'message' => "IP deleted successfully"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "IP not found in the database"
    ]);
}

$stmt->close();
$mysqli->close();
