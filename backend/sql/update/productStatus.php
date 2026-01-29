<?php
// backend/sql/update/productStatus.php

header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
$authPath = __DIR__ . '/../../../backend/security/auth.php';

if (!file_exists($configPath) || !file_exists($authPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration or Auth file not found.']);
    exit;
}

require_once $configPath;
require_once $authPath;

// Get input
$input = json_decode(file_get_contents("php://input"), true);
$token = $input['token'] ?? null;
$id = $input['id'] ?? null;
$isActive = $input['isActive'] ?? null;

// Validate Inputs
if (!$token || !$id || $isActive === null) {
    echo json_encode(['success' => false, 'message' => 'Missing token, id or isActive']);
    exit;
}

// Validate Token
$userId = verifyToken($mysqli, $token);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

// Check Permission
// We check for 'edit_products' or 'manage_products'
if (!hasPermission($mysqli, $userId, 'edit_products') && !hasPermission($mysqli, $userId, 'manage_products')) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// Update the product status
$stmt = $mysqli->prepare("UPDATE products SET isActive = ? WHERE id = ?");
if ($stmt) {
    // isActive is usually stored as '1' or '0' (string) or int.
    $statusStr = (string)$isActive;

    $stmt->bind_param("si", $statusStr, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $mysqli->error]);
}

$mysqli->close();
?>
