<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$settings = $input['settings'] ?? null;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

if ($settings === null) {
    echo json_encode(['success' => false, 'message' => 'Settings are required.']);
    exit;
}

// Ensure settings is a JSON string
if (is_array($settings)) {
    $settings = json_encode($settings);
}

$stmt = $mysqli->prepare("UPDATE users SET settings = ? WHERE id = ?");
$stmt->bind_param("si", $settings, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Settings updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $mysqli->error]);
}
?>
