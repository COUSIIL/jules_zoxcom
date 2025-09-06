<?php
header("Content-Type: application/json; charset=UTF-8");
$input = json_decode(file_get_contents("php://input"), true);

$secret = "6LeAL7QrAAAAABBJHVja_OyzbcOYq2ZDJVdcMAZl";
$recaptcha = $input['recaptcha'] ?? null;

if (!$recaptcha) {
    echo json_encode(["success" => false, "message" => "reCAPTCHA manquant"]);
    exit;
}

$ch = curl_init("https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'secret' => $secret,
    'response' => $recaptcha
]);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur cURL",
        "error" => curl_error($ch)
    ]);
    exit;
}
curl_close($ch);

$captchaSuccess = json_decode($response, true);

if ($captchaSuccess && !$captchaSuccess['success']) {
    echo json_encode([
        "success" => false,
        "message" => $captchaSuccess ?? "error reCAPTCHA",
    ]);
    exit;
}

// reCAPTCHA v3 donne aussi un score (0.0 = bot, 1.0 = humain)
if ($captchaSuccess['score'] < 0.5) {
    echo json_encode(["success" => false, "message" => "Score reCAPTCHA trop bas"]);
    exit;
}

// 📌 Inclure la config DB
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// 📌 Créer la table si elle n'existe pas
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
    description VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$mysqli->query($createTable)) {
    echo json_encode(['success' => false, 'message' => 'Error creating table: ' . $mysqli->error]);
    exit;
}

// 📌 Lire les données envoyées

if (!isset($input['username']) || !isset($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Username/email and password are required.']);
    exit;
}

$usernameOrEmail = trim($input['username']);
$password = trim($input['password']);
if (empty($usernameOrEmail) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Empty username/email or password.']);
    exit;
}

// 📌 Vérifier si la table contient déjà un utilisateur
$checkUsers = $mysqli->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $checkUsers->fetch_assoc()['total'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$hashedPassword2 = hash('sha256', $password);
$token = hash('sha256', time() . $usernameOrEmail);

if ($totalUsers == 0) {
    // 📌 Pas d'utilisateur → création automatique
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password, token) VALUES (?, ?, ?, ?)");
    // Si l'entrée contient un @ → on considère que c'est l'email
    if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
        $stmt->bind_param("ssss", $usernameOrEmail, $usernameOrEmail, $hashedPassword, $token);
    } else {
        // Pas d'email → email vide
        $emptyEmail = '';
        $stmt->bind_param("ssss", $usernameOrEmail, $emptyEmail, $hashedPassword, $token);
    }
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'First user created successfully and logged in.',
            'data' => ['token' => $token, 'user' => $usernameOrEmail]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating first user: ' . $mysqli->error]);
    }
    exit;
}

// 📌 Connexion avec username OU email
$stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    if (password_verify($password, $user['password'])) {

        echo json_encode([
            'success' => true,
            'message' => 'Connexion successful',
            'data' => [
                'token' => $user['token'],
                'user' => $user['username'], 
                'profile_image' => $user['profile_image']
            ]
        ]);
        exit;
    }
    if ($user['password'] === $hashedPassword) {
        echo json_encode([
            'success' => true,
            'message' => 'Connexion successful',
            'data' => ['token' => $user['token'], 'user' => $user['username']]
        ]);
    } else if ($user['password'] === $hashedPassword2) {
        echo json_encode([
            'success' => true,
            'message' => 'Connexion successful',
            'data' => ['token' => $user['token'], 'user' => $user['username'], 'profile_image' => $user['profile_image']]
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Wrong password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
?>
