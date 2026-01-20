<?php
// backend/sql/update/moduleActivator.php

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
$table = $input['table'] ?? null;

// Handle boolean true/false or 1/0
$value = isset($input['value']) ? ($input['value'] ? 1 : 0) : null;

// Validate Inputs
if (!$token || !$table || $value === null) {
    echo json_encode(['success' => false, 'message' => 'Missing token, table or value']);
    exit;
}

// Validate Token
$userId = verifyToken($mysqli, $token);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

// Check Permission
// We check for 'manage_modules' or fallback to 'manage_settings'
if (!hasPermission($mysqli, $userId, 'manage_modules') && !hasPermission($mysqli, $userId, 'manage_settings')) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// Allowed tables whitelist to prevent SQL Injection
$allowedTables = [
    'ups_module',
    'yal_module',
    'gpx_module',
    'anderson_module',
    'orderdz_module'
];

if (!in_array($table, $allowedTables)) {
    echo json_encode(['success' => false, 'message' => 'Invalid module table']);
    exit;
}

// Update the module status
// This updates the 'work' column for ALL records in the module table.
$stmt = $mysqli->prepare("UPDATE `$table` SET work = ?");
if ($stmt) {
    $stmt->bind_param("i", $value);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Module status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $mysqli->error]);
}

$mysqli->close();
?>
