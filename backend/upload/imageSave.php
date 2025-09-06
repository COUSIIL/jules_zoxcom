<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}

require_once $configPath;

// Vérifie ou crée la table "images"
$table_check_query = "SHOW TABLES LIKE 'images'";
$table_check_result = $mysqli->query($table_check_query);

if ($table_check_result->num_rows == 0) {
    $create_table_query = "
        CREATE TABLE images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            folder_id INT NOT NULL,
            name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
            path TEXT NOT NULL,
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    if (!$mysqli->query($create_table_query)) {
        echo json_encode([
            'success' => false,
            'message' => "Error creating table: " . $mysqli->error,
        ]);
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

// Traitement de l'upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {

    $folderId = $_POST['folderId'] ?? null;
    if (!$folderId) {
        echo json_encode(['success' => false, 'message' => 'Missing folderId.']);
        exit;
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/images/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $image = $_FILES['image'];
    $fileExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

    // Génère un nom unique
    $filePath = $uploadDir . $image['name'];
    $relativePath = '/uploads/images/' . $image['name'];

    if (move_uploaded_file($image['tmp_name'], $filePath)) {
        // Insertion BDD
        $stmt = $mysqli->prepare("INSERT INTO images (folder_id, name, path) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $folderId, $image['name'], $relativePath);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Image uploaded and saved to database.',
                'data' => [
                    'path' => $relativePath,
                    'name' => $image['name'],
                    'folder_id' => $folderId
                ],
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database insertion failed: ' . $stmt->error,
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to upload image.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No image uploaded.'
    ]);
}
?>
