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

function sanitizeFilename($filename) {
    // Remove extension
    $info = pathinfo($filename);
    $name = $info['filename'];
    $ext = isset($info['extension']) ? '.' . $info['extension'] : '';

    // Replace special chars with underscores
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

    // Remove multiple underscores
    $name = preg_replace('/_+/', '_', $name);

    return $name . $ext;
}

// Traitement de l'upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {

    $folderId = $_POST['folderId'] ?? null;
    if ($folderId === null || $folderId === '') { // folderId can be 0 (root)
        echo json_encode(['success' => false, 'message' => 'Missing folderId.']);
        exit;
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/images/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $image = $_FILES['image'];

    // Sanitize the original name
    $sanitizedName = sanitizeFilename($image['name']);
    $fileExtension = strtolower(pathinfo($sanitizedName, PATHINFO_EXTENSION));
    $fileNameWithoutExt = pathinfo($sanitizedName, PATHINFO_FILENAME);

    $finalName = $sanitizedName;
    $filePath = $uploadDir . $finalName;

    // Handle duplicates
    $counter = 1;
    while (file_exists($filePath)) {
        $finalName = $fileNameWithoutExt . '_' . $counter . '.' . $fileExtension;
        $filePath = $uploadDir . $finalName;
        $counter++;
    }

    $relativePath = '/uploads/images/' . $finalName;

    if (move_uploaded_file($image['tmp_name'], $filePath)) {
        // Insertion BDD
        $stmt = $mysqli->prepare("INSERT INTO images (folder_id, name, path) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $folderId, $finalName, $relativePath);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Image uploaded and saved to database.',
                'data' => [
                    'path' => $relativePath,
                    'name' => $finalName,
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
