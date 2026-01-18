<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

$createTables = [
    "CREATE TABLE IF NOT EXISTS users (
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
    )"
];

foreach ($createTables as $query) {
    if (!$mysqli->query($query)) {
        echo json_encode(["success" => false, "message" => "Erreur SQL : " . $mysqli->error]);
        exit;
    }
}

$alters = [
    ['table' => 'users', 'column' => 'ip_adresse', 'query' => "ALTER TABLE users ADD COLUMN ip_adresse VARCHAR(45) NULL AFTER id"],
    ['table' => 'users', 'column' => 'email', 'query' => "ALTER TABLE users ADD COLUMN email VARCHAR(255) NULL AFTER ip_adresse"],
    ['table' => 'users', 'column' => 'username', 'query' => "ALTER TABLE users ADD COLUMN username VARCHAR(255) NULL AFTER email"],
    ['table' => 'users', 'column' => 'name', 'query' => "ALTER TABLE users ADD COLUMN name VARCHAR(255) NULL AFTER username"],
    
    ['table' => 'users', 'column' => 'family_name', 'query' => "ALTER TABLE users ADD COLUMN family_name VARCHAR(255) NULL AFTER name"],
    ['table' => 'users', 'column' => 'role', 'query' => "ALTER TABLE users ADD COLUMN role VARCHAR(50) NULL AFTER family_name"],
    ['table' => 'users', 'column' => 'profile_image', 'query' => "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER role"],
    ['table' => 'users', 'column' => 'token', 'query' => "ALTER TABLE users ADD COLUMN token VARCHAR(255) NOT NULL AFTER profile_image"],
    ['table' => 'users', 'column' => 'password', 'query' => "ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL AFTER token"],
    ['table' => 'users', 'column' => 'description', 'query' => "ALTER TABLE users ADD COLUMN description VARCHAR(255) NOT NULL AFTER password"],
    ['table' => 'users', 'column' => 'created_at', 'query' => "ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER password"]
];

foreach ($alters as $alter) {
    $table = $alter['table'];
    $column = $alter['column'];
    $check = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($check && $check->num_rows === 0) {
        if (!$mysqli->query($alter['query'])) {
            echo json_encode(["success" => false, "message" => "Erreur ALTER SQL : " . $mysqli->error]);
            exit;
        }
    }
}

// Requête pour récupérer les utilisateurs sans le token
$query = "SELECT u.id, u.username, u.name, u.family_name, u.email, u.profile_image, u.created_at, u.role, u.role_id, r.name as role_name, u.ip_adresse
          FROM users u
          LEFT JOIN roles r ON u.role_id = r.id
          ORDER BY u.id DESC";
$result = $mysqli->query($query);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving users: ' . $mysqli->error
    ]);
    exit;
}

$users = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'success' => true,
    'message' => 'Users retrieved successfully',
    'data' => $users
]);
?>
