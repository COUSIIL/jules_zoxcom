<?php
header('Content-Type: application/json');

// Inclure la configuration de la BDD
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Fonction pour insérer le dossier racine "home"
function insertHomeFolder($mysqli) {
    $name = 'home';
    $size = 0;

    $insert_query = $mysqli->prepare("INSERT INTO folder (name, size, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    if (!$insert_query) {
        return ['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error];
    }

    $insert_query->bind_param("si", $name, $size);
    if (!$insert_query->execute()) {
        return ['success' => false, 'message' => 'Insert failed: ' . $insert_query->error];
    }

    return ['success' => true];
}

// Vérifie si la table folder existe
$table_check_query = "SHOW TABLES LIKE 'folder'";
$table_check_result = $mysqli->query($table_check_query);

if (!$table_check_result) {
    echo json_encode(['success' => false, 'message' => "Error checking table: " . $mysqli->error]);
    exit;
}

// Si la table n'existe pas, on la crée
if ($table_check_result->num_rows == 0) {
    $create_table_query = "CREATE TABLE folder (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        parent_id INT DEFAULT NULL,
        size BIGINT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if (!$mysqli->query($create_table_query)) {
        echo json_encode(['success' => false, 'message' => "Error creating table: " . $mysqli->error]);
        exit;
    }
}

// ✅ Vérifie si la table est vide, et insère "home" si nécessaire
$check_empty_query = "SELECT COUNT(*) as count FROM folder";
$check_empty_result = $mysqli->query($check_empty_query);

if ($check_empty_result) {
    $row = $check_empty_result->fetch_assoc();
    if ((int)$row['count'] === 0) {
        $insertResult = insertHomeFolder($mysqli);
        if (!$insertResult['success']) {
            echo json_encode($insertResult);
            exit;
        }
    }
}

// Requête pour obtenir les dossiers
$sql = "SELECT * FROM folder ORDER BY parent_id ASC, name ASC";
$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => "Query failed: " . $mysqli->error]);
    exit;
}

$data = $result->fetch_all(MYSQLI_ASSOC);

// Réponse JSON
echo json_encode([
    'success' => true,
    'message' => 'Folders retrieved successfully.',
    'data' => $data
]);

$mysqli->close();
?>
