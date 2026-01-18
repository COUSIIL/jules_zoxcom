<?php
header("Content-Type: application/json; charset=UTF-8");

// 📌 Inclure la config DB
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// 📌 Créer la table `users` si elle n'existe pas (même format que dans ton login)
$createTable = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    ip_adresse VARCHAR(45),
    name VARCHAR(255),
    family_name VARCHAR(255),
    role VARCHAR(50),
    profile_image VARCHAR(255) DEFAULT NULL,
    token VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if (!$mysqli->query($createTable)) {
    echo json_encode(['success' => false, 'message' => 'Error creating table: ' . $mysqli->error]);
    exit;
}

// 📌 Récupérer les données JSON envoyées
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['token']) || empty($input['token'])) {
    echo json_encode(['success' => false, 'message' => 'Token is required']);
    exit;
}

$token = $mysqli->real_escape_string($input['token']);

// 📌 Vérifier si le token existe en base
$stmt = $mysqli->prepare("SELECT * FROM users WHERE token = ?");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    // Fetch permissions
    $permissions = [];
    if (!empty($user['role_id'])) {
        $pRes = $mysqli->query("SELECT permission_slug FROM role_permissions WHERE role_id = " . $user['role_id']);
        while ($pRow = $pRes->fetch_assoc()) {
            $permissions[] = $pRow['permission_slug'];
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'User is connected',
        'data' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'], // Legacy
            'role_id' => $user['role_id'],
            'profile_image' => $user['profile_image'],
            'created_at' => $user['created_at'],
            'permissions' => $permissions,
            'token' => $token // Return token to ensure client state consistency if needed
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
}

$mysqli->close();
