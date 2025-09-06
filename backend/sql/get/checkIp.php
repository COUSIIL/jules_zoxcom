<?php
header("Content-Type: application/json; charset=UTF-8");

function getUserIp(): string {
    return $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
}

$ip = getUserIp();

// Connexion à la BDD
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'message' => 'DB config not found.'
    ]));
}

require_once $configPath; // doit définir $mysqli (MySQLi)

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

// Requête MySQLi pour vérifier si l'IP est bannie
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM banned_ips WHERE ip_address = ?");
$stmt->bind_param("s", $ip);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    echo json_encode([
        'success' => false,
        'message' => "You have been suspended for suspicious reason. If you think it's wrong, please contact us."
    ]);
    exit;
}

echo json_encode(['success' => true]);
