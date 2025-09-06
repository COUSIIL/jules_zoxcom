<?php
header("Content-Type: application/json; charset=UTF-8");

// Connexion à la BDD
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'DB config not found.']));
}
require_once $configPath; // $mysqli

// Lire toutes les IP bannies
$query = "SELECT ip_address, reason, created_at FROM banned_ips ORDER BY created_at DESC";
$result = $mysqli->query($query);

if (!$result) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Query error: ' . $mysqli->error]));
}

$ips = [];
while ($row = $result->fetch_assoc()) {
    $ips[] = $row;
}

echo json_encode([
    'success' => true,
    'message' => 'ips recieved',
    'data' => $ips
]);
