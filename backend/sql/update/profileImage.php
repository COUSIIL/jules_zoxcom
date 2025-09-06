<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Vérification que la requête est bien en POST et qu’un fichier est envoyé
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['profile_image'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request or missing file.']);
    exit;
}

// Récupération de l'ID utilisateur
$userId = intval($_POST['id'] ?? 0);
if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

// Vérifier que l’utilisateur existe
$stmt = $mysqli->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

// Gestion de l’upload de la nouvelle image
$profileImage = $_FILES['profile_image'];
if ($profileImage['error'] !== 0) {
    echo json_encode(['success' => false, 'message' => 'Error uploading file.']);
    exit;
}

$uploadDir = __DIR__ . '/../../../uploads/profile/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = strtolower(pathinfo($profileImage['name'], PATHINFO_EXTENSION));
$allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($ext, $allowedExts)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type.']);
    exit;
}

// Suppression de l’ancienne image si elle existe
if (!empty($user['profile_image']) && file_exists($uploadDir . $user['profile_image'])) {
    unlink($uploadDir . $user['profile_image']);
}

// Sauvegarde de la nouvelle image
$newFileName = uniqid('user_') . '.' . $ext;
$targetPath = $uploadDir . $newFileName;
if (!move_uploaded_file($profileImage['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save the new profile image.']);
    exit;
}

// Mise à jour de la base
$updateStmt = $mysqli->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
$updateStmt->bind_param("si", $newFileName, $userId);

if ($updateStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Profile image updated successfully.',
        'profile_image' => $newFileName
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}
