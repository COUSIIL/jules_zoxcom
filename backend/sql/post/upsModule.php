<?php
// backend/sql/post/upsModule.php

header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
$authPath = __DIR__ . '/../../../backend/security/auth.php';

if (!file_exists($configPath) || !file_exists($authPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration or Auth file not found.']);
    exit;
}

require_once $configPath;
require_once $authPath;

if (!$mysqli) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

// 1. Get Input
$data = json_decode(file_get_contents('php://input'), true);

// 2. Validate Token
$token = $data['token'] ?? null;
if (!$token) {
     echo json_encode(['success' => false, 'message' => 'Token required']);
     exit;
}
$userId = verifyToken($mysqli, $token);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

// 3. Check Permission
if (!hasPermission($mysqli, $userId, 'manage_modules') && !hasPermission($mysqli, $userId, 'manage_settings')) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// 4. Ensure Table Exists
$table_check_query = "SHOW TABLES LIKE 'ups_module'";
$table_check_result = $mysqli->query($table_check_query);

if ($table_check_result->num_rows == 0) {
    $create_table_query = "CREATE TABLE `ups_module` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        work INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        `key` TEXT
    )";
    if (!$mysqli->query($create_table_query)) {
        echo json_encode(['success' => false, 'message' => 'Error creating table: ' . $mysqli->error]);
        exit;
    }
}

// 5. Process Update/Insert
if (isset($data['name'], $data['key'])) {
    $name = $mysqli->real_escape_string($data['name']);
    $key = $mysqli->real_escape_string($data['key']);
    $work = isset($data['work']) ? (int) $data['work'] : 0;
    $data_time = date('Y-m-d H:i:s');

    $check_query = $mysqli->prepare("SELECT id FROM `ups_module` WHERE name = ?");
    if ($check_query) {
        $check_query->bind_param("s", $name);
        $check_query->execute();
        $check_result = $check_query->get_result();

        if ($check_result->num_rows > 0) {
            $update_query = $mysqli->prepare("UPDATE `ups_module` SET data_time = ?, work = ?, `key` = ? WHERE name = ?");
            if ($update_query) {
                $update_query->bind_param("siss", $data_time, $work, $key, $name);
                if ($update_query->execute()) {
                    echo json_encode(['success' => true, 'message' => 'UPS updated successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error updating UPS: ' . $update_query->error]);
                }
                $update_query->close();
            }
        } else {
            $insert_query = $mysqli->prepare("INSERT INTO `ups_module` (data_time, work, name, `key`) VALUES (?, ?, ?, ?)");
            if ($insert_query) {
                $insert_query->bind_param("siss", $data_time, $work, $name, $key);
                if ($insert_query->execute()) {
                    echo json_encode(['success' => true, 'message' => 'UPS added successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error inserting UPS: ' . $insert_query->error]);
                }
                $insert_query->close();
            }
        }
        $check_query->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
}

$mysqli->close();
?>
