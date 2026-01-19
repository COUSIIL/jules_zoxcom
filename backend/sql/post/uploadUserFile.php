<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
require_once $configPath;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$token = isset($_POST['token']) ? trim($_POST['token']) : '';

if ($userId <= 0 || !isset($_FILES['file']) || empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Missing data or token']);
    exit;
}

// Verify User by Token
$stmtUser = $mysqli->prepare("SELECT id, role FROM users WHERE token = ?");
$stmtUser->bind_param("s", $token);
$stmtUser->execute();
$resUser = $stmtUser->get_result();

if ($resUser->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Token']);
    exit;
}

$currentUser = $resUser->fetch_assoc();
$currentUserId = intval($currentUser['id']);
$currentUserRole = $currentUser['role'];

// Check permission
if ($userId !== $currentUserId && strtolower($currentUserRole) !== 'admin') {
     echo json_encode(['success' => false, 'message' => 'Permission denied']);
     exit;
}

$file = $_FILES['file'];
$uploadDir = __DIR__ . '/../../../backend/uploads/user_files/';
$allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
$maxSize = 5 * 1024 * 1024; // 5MB

$fileName = $file['name'];
$fileTmp = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($fileExt, $allowedExts)) {
    echo json_encode(['success' => false, 'message' => 'File type not allowed']);
    exit;
}

if ($fileSize > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File too large (Max 5MB)']);
    exit;
}

if ($fileError !== 0) {
    echo json_encode(['success' => false, 'message' => 'Upload error code: ' . $fileError]);
    exit;
}

$storedName = uniqid('doc_', true) . '.' . $fileExt;
$destination = $uploadDir . $storedName;

if (move_uploaded_file($fileTmp, $destination)) {
    $stmt = $mysqli->prepare("INSERT INTO user_files (user_id, original_name, stored_name, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $userId, $fileName, $storedName, $fileExt, $fileSize);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'File uploaded', 'file' => [
            'id' => $stmt->insert_id,
            'original_name' => $fileName,
            'stored_name' => $storedName,
            'created_at' => date('Y-m-d H:i:s')
        ]]);
    } else {
        unlink($destination); // Rollback
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
?>
