<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Créer la table `users` si elle n'existe pas
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

// Colonnes à ajouter si manquantes
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

// Traitement de la requête POST
if (!empty($_POST)) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['firstname'] ?? '');
    $family_name = trim($_POST['lastname'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $ip_adresse = $_SERVER['REMOTE_ADDR'] ?? null;
    $profileImage = $_FILES['profile_image'] ?? null;
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $name = trim($input['firstname'] ?? '');
    $family_name = trim($input['lastname'] ?? '');
    $role = trim($input['role'] ?? '');
    $ip_adresse = $_SERVER['REMOTE_ADDR'] ?? null;
    $profileImage = null;
}

// Vérification des champs obligatoires
if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username, email, and password are required.']);
    exit;
}

// Vérifier si username ou email existent déjà
$stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
    exit;
}

// Upload image de profil
$profileImageName = null;
if (isset($profileImage) && is_array($profileImage) && $profileImage['error'] === 0) {
    $uploadDir = __DIR__ . '/../../../uploads/profile/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            echo json_encode(['success' => false, 'message' => 'Impossible de créer le dossier de destination.']);
            exit;
        }
    }
    $ext = pathinfo($profileImage['name'], PATHINFO_EXTENSION);
    $profileImageName = uniqid('user_') . '.' . $ext;
    $targetPath = $uploadDir . $profileImageName;

    if (!move_uploaded_file($profileImage['tmp_name'], $targetPath)) {
        echo json_encode(['success' => false, 'message' => 'Échec de l\'upload de la photo de profil.']);
        exit;
    }
}

// Insertion dans la base de données
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$token = hash('sha256', time() . $username);

$stmt = $mysqli->prepare("INSERT INTO users (username, email, password, name, family_name, role, ip_adresse, profile_image, token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $username, $email, $hashedPassword, $name, $family_name, $role, $ip_adresse, $profileImageName, $token);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'User created successfully',
        'data' => [
            'username' => $username,
            'email' => $email,
            'name' => $name,
            'family_name' => $family_name,
            'role' => $role,
            'ip_adresse' => $ip_adresse,
            'profile_image' => $profileImageName,
            'token' => $token
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating user.']);
}
?>
