<?php
header("Content-Type: application/json; charset=UTF-8");

// Inclure le fichier de configuration de la base de données
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}

require_once $configPath;

// ✅ Crée la table et insère l'utilisateur admin par défaut
$hashedPassword0 = password_hash('admin', PASSWORD_DEFAULT);
$date = date('Y-m-d H:i:s');
$token = hash('sha256', $date);

$queryCreate = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL
    );
";

$queryInsert = "
    INSERT INTO users (username, password, token)
    SELECT 'admin', '$hashedPassword0', '$token'
    WHERE NOT EXISTS (
        SELECT 1 FROM users WHERE username = 'admin'
    );
";

// ✅ Exécuter les requêtes séparément
if (!$mysqli->query($queryCreate)) {
    echo json_encode([
        'success' => false,
        'message' => "Error creating table: " . $mysqli->error,
    ]);
    die;
}

if (!$mysqli->query($queryInsert)) {
    echo json_encode([
        'success' => false,
        'message' => "Error inserting data: " . $mysqli->error,
    ]);
    die;
}

// ✅ Récupérer les données JSON envoyées
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || !isset($input['password'])) {
    echo json_encode([
        'success' => false,
        'message' => "Error: All values are required.",
    ]);
    die;
}

$username = $input['username'];
$password = $input['password'];

if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username or password required'
    ]);
    die;
}

// 🔎 Requête SQL pour vérifier les identifiants
$stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result) {

    // ✅ Vérification avec password_verify()
    if (password_verify($password, $result['password'])) {

        echo json_encode([
            'success' => true,
            'message' => 'Connexion successful',
            'data' => [
                'token' => $result['token'],
                'user' => $result['username']
            ]
        ]);
        die;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Wrong password'
        ]);
        die;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);
    die;
}
