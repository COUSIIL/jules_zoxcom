<?php

header("Content-Type: application/json; charset=UTF-8");

// 1. Charger la config
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}

require_once $configPath; // définit $mysqli

// 2. Créer la table si nécessaire
$tableSQL = <<<SQL
CREATE TABLE IF NOT EXISTS store_delivery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(45) NOT NULL UNIQUE,
    method VARCHAR(45) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if ($mysqli->query($tableSQL) === false) {
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'message' => 'Create table error: ' . $mysqli->error
    ]));
}

// 3. Lire les données envoyées
$data = json_decode(file_get_contents('php://input'), true);
$name   = $data['name'] ?? '';
$method = $data['method'] ?? '';

if (!$name || !$method) {
    echo json_encode([
        'success' => false,
        'message' => 'Name and method are required.'
    ]);
    exit;
}

// 4. Vérifie si une ligne existe déjà pour ce name
$checkStmt = $mysqli->prepare("SELECT id FROM store_delivery WHERE name = ?");
$checkStmt->bind_param("s", $name);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    // 5. Si elle existe -> update
    $checkStmt->bind_result($existingId);
    $checkStmt->fetch();
    $checkStmt->close();

    $updateStmt = $mysqli->prepare("UPDATE store_delivery SET method = ? WHERE id = ?");
    $updateStmt->bind_param("si", $method, $existingId);
    
    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Delivery method updated successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating method: ' . $updateStmt->error
        ]);
    }

    $updateStmt->close();
} else {
    $checkStmt->close();

    // 6. Sinon -> insert
    $insertStmt = $mysqli->prepare("INSERT INTO store_delivery (name, method) VALUES (?, ?)");
    $insertStmt->bind_param("ss", $name, $method);

    if ($insertStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Delivery method added successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error inserting method: ' . $insertStmt->error
        ]);
    }

    $insertStmt->close();
}

$mysqli->close();
