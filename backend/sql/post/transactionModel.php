<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Créer la table si elle n'existe pas
$createTable = "
    CREATE TABLE IF NOT EXISTS transaction_model (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE,
        type VARCHAR(255),
        banque_id_lose VARCHAR(255),  
        banque_id_win VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
";
if (!$mysqli->query($createTable)) {
    echo json_encode(["success" => false, "message" => "Erreur SQL : " . $mysqli->error]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    response(false, "Invalid input data.", 400);
}

// Extraction des paramètres
$name          = $data['name'] ?? '';
$type          = $data['type'] ?? '';
$banque_id_lose= $data['banque_id_lose'] ?? '';
$banque_id_win = $data['banque_id_win'] ?? '';
$id            = isset($data['id']) ? intval($data['id']) : -1;

// Vérification si la transaction_model existe
$check_query = $mysqli->prepare("SELECT id FROM transaction_model WHERE id = ?");
$check_query->bind_param("i", $id);
$check_query->execute();
$product_result = $check_query->get_result();
$check_query->close();

if ($product_result->num_rows > 0) {
    // Mise à jour
    $transaction_id = $product_result->fetch_assoc()['id'];
    updateModels($name, $type, $banque_id_lose, $banque_id_win, $transaction_id, $mysqli);
    response(true, "Transaction model updated with id: $transaction_id");
} else {
    // Insertion
    insertModels($name, $type, $banque_id_lose, $banque_id_win, $mysqli);
    response(true, "Transaction model added successfully");
}

// === Fonctions ===

function insertModels($name, $type, $banque_id_lose, $banque_id_win, $mysqli) {
    if ($mysqli === null || $mysqli->connect_error) {
        response(false, "Database connection failed.");
    }

    $stmt = $mysqli->prepare(
        "INSERT INTO transaction_model (name, type, banque_id_lose, banque_id_win) 
         VALUES (?, ?, ?, ?)"
    );
    if (!$stmt) {
        response(false, "Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("ssss", $name, $type, $banque_id_lose, $banque_id_win);

    if (!$stmt->execute()) {
        response(false, "MySQL Error: " . $stmt->error);
    }
    $stmt->close();
}

function updateModels($name, $type, $banque_id_lose, $banque_id_win, $transaction_id, $mysqli) {
    $stmt = $mysqli->prepare(
        "UPDATE transaction_model 
         SET name = ?, type = ?, banque_id_lose = ?, banque_id_win = ? 
         WHERE id = ?"
    );
    if (!$stmt) {
        response(false, "Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("ssssi", $name, $type, $banque_id_lose, $banque_id_win, $transaction_id);

    if (!$stmt->execute()) {
        response(false, "Update failed: " . $stmt->error);
    }
    $stmt->close();
}

function response($success, $message, $statusCode = 200, $data = null) {
    http_response_code($statusCode);
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit();
}
