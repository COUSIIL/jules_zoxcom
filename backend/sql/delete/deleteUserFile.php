<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
require_once $configPath;

$input = json_decode(file_get_contents('php://input'), true);
$fileId = isset($input['file_id']) ? intval($input['file_id']) : 0;
$token = isset($input['token']) ? trim($input['token']) : '';

if ($fileId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid File ID']);
    exit;
}

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Token required']);
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

// Fetch file info
$stmt = $mysqli->prepare("SELECT stored_name, user_id FROM user_files WHERE id = ?");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'File not found']);
    exit;
}

$file = $result->fetch_assoc();

// Check permission: Owner OR Admin
if ($file['user_id'] !== $currentUserId && strtolower($currentUserRole) !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

$filePath = __DIR__ . '/../../../backend/uploads/user_files/' . $file['stored_name'];

if (file_exists($filePath)) {
    unlink($filePath);
}

$delStmt = $mysqli->prepare("DELETE FROM user_files WHERE id = ?");
$delStmt->bind_param("i", $fileId);

if ($delStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'File deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB Error']);
}
?>
