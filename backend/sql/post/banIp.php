<?php

header("Content-Type: application/json; charset=UTF-8");

// Inclure la config
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}

require_once $configPath; // Définit $mysqli

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

// Lecture des données JSON
$data = json_decode(file_get_contents('php://input'), true);

$ip = $data['ip'] ?? $_SERVER['REMOTE_ADDR'];
$reason = $data['reason'] ?? 'abuse';

// Vérifier si l'IP est déjà présente
$checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM banned_ips WHERE ip_address = ?");
$checkStmt->bind_param("s", $ip);
$checkStmt->execute();
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'This IP is already banned'
    ]);
    exit;
}

// Insertion de l'IP si elle n'existe pas
$insertStmt = $mysqli->prepare("INSERT INTO banned_ips (ip_address, reason) VALUES (?, ?)");
$insertStmt->bind_param("ss", $ip, $reason);

if ($insertStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'IP added to dark list'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error adding IP to dark list: ' . $insertStmt->error
    ]);
}

$insertStmt->close();
$mysqli->close();
