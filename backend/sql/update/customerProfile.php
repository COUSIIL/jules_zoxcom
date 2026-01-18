<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../../backend/config/dbConfig.php';

$data = json_decode(file_get_contents("php://input"), true);
$headers = getallheaders();
$token = $headers['Authorization'] ?? $data['token'] ?? '';

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate Token
$stmt = $mysqli->prepare("SELECT id FROM customers WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}
$user = $res->fetch_assoc();
$id = $user['id'];

// Update fields
$allowed = ['name', 'email', 'wilaya', 'commune', 'address', 'password'];
$updates = [];
$types = "";
$params = [];

foreach ($data as $key => $value) {
    if (in_array($key, $allowed)) {
        if ($key === 'password') {
            $value = password_hash($value, PASSWORD_DEFAULT);
        }
        $updates[] = "$key = ?";
        $types .= "s";
        $params[] = $value;
    }
}

if (empty($updates)) {
    echo json_encode(['success' => false, 'message' => 'No fields to update']);
    exit;
}

$types .= "i";
$params[] = $id;

$sql = "UPDATE customers SET " . implode(", ", $updates) . " WHERE id = ?";
$stmtUpdate = $mysqli->prepare($sql);
$stmtUpdate->bind_param($types, ...$params);

if ($stmtUpdate->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}

$mysqli->close();
?>
