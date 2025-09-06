<?php
header('Content-Type: application/json');

// Inclure la configuration de la BDD
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Vérifie si la table images existe
$table_check_query = "SHOW TABLES LIKE 'images'";
$table_check_result = $mysqli->query($table_check_query);

if (!$table_check_result) {
    echo json_encode(['success' => false, 'message' => "Error checking table: " . $mysqli->error]);
    exit;
}

// Si la table n'existe pas, on la crée
if ($table_check_result->num_rows == 0) {
    $create_table_query = "CREATE TABLE images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            folder_id INT NOT NULL,
            name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
            path TEXT NOT NULL,
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";

    if (!$mysqli->query($create_table_query)) {
        echo json_encode(['success' => false, 'message' => "Error creating table: " . $mysqli->error]);
        exit;
    }
} else {
    // Si la table existe, on s’assure que la colonne est bien en utf8mb4
    $alter_query = "ALTER TABLE images MODIFY name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if (!$mysqli->query($alter_query)) {
        echo json_encode(['success' => false, 'message' => "Error altering table: " . $mysqli->error]);
        exit;
    }
}

// Requête pour obtenir les images
$sql = "SELECT * FROM images ORDER BY folder_id ASC, name ASC";
$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => "Query failed: " . $mysqli->error]);
    exit;
}

$data = $result->fetch_all(MYSQLI_ASSOC);

// Réponse JSON
echo json_encode([
    'success' => true,
    'message' => 'Images retrieved successfully.',
    'data' => $data
]);

$mysqli->close();
?>
