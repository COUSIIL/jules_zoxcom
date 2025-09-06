<?php

header("Content-Type: application/json; charset=UTF-8");

// Inclure la configuration de la BDD
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Vérifie si la table folder existe
$table_check_query = "SHOW TABLES LIKE 'folder'";
$table_check_result = $mysqli->query($table_check_query);

if ($table_check_result->num_rows == 0) {
    // Création de la table folder avec utf8mb4 directement
    $create_table_query = "CREATE TABLE folder (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        parent_id INT DEFAULT NULL,
        size BIGINT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if (!$mysqli->query($create_table_query)) {
        echo json_encode(['success' => false, 'message' => "Error creating table: " . $mysqli->error]);
        exit;
    }
} else {
    // Si la table existe, on s’assure que la colonne est bien en utf8mb4
    $alter_query = "ALTER TABLE folder MODIFY name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if (!$mysqli->query($alter_query)) {
        echo json_encode(['success' => false, 'message' => "Error altering table: " . $mysqli->error]);
        exit;
    }
}



// Récupérer les données
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name'])) {
    echo json_encode(['success' => false, 'message' => "Missing folder name."]);
    exit;
}

$name = $data['name'];
$parent_id = isset($data['parent_id']) ? (int)$data['parent_id'] : null;
$size = isset($data['size']) ? (int)$data['size'] : 0;

// Vérifie si le dossier existe déjà (par nom et parent_id)
$check_query = $mysqli->prepare("SELECT id FROM folder WHERE name = ? AND parent_id " . ($parent_id === null ? "IS NULL" : "= ?"));
if ($parent_id === null) {
    $check_query->bind_param("s", $name);
} else {
    $check_query->bind_param("si", $name, $parent_id);
}
$check_query->execute();
$check_result = $check_query->get_result();

if ($check_result->num_rows > 0) {
    // Le dossier existe déjà → mise à jour
    $row = $check_result->fetch_assoc();
    $folder_id = $row['id'];
    $update_query = $mysqli->prepare("UPDATE folder SET size = ?, updated_at = NOW() WHERE id = ?");
    $update_query->bind_param("ii", $size, $folder_id);

    if ($update_query->execute()) {
        echo json_encode(['success' => true, 'message' => "Folder updated successfully.", 'folder_id' => $folder_id]);
    } else {
        echo json_encode(['success' => false, 'message' => "Error updating folder: " . $mysqli->error]);
    }
} else {
    // Nouveau dossier
    if ($parent_id === null) {
        $insert_query = $mysqli->prepare("INSERT INTO folder (name, size, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $insert_query->bind_param("si", $name, $size);
    } else {
        $insert_query = $mysqli->prepare("INSERT INTO folder (name, parent_id, size, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $insert_query->bind_param("sii", $name, $parent_id, $size);
    }

    if ($insert_query->execute()) {
        echo json_encode(['success' => true, 'message' => "Folder created successfully.", 'folder_id' => $mysqli->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => "Error inserting folder: " . $mysqli->error]);
    }
}

$mysqli->close();
