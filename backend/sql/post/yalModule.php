<?php
// backend/sql/post/yalModule.php

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
$table_check_query = "SHOW TABLES LIKE 'yal_module'";
$table_check_result = $mysqli->query($table_check_query);

if ($table_check_result->num_rows == 0) {
    $create_table_query = "CREATE TABLE `yal_module` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        work INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        `api_id` TEXT,
        `api_token` TEXT
    )";
    if (!$mysqli->query($create_table_query)) {
        echo json_encode(['success' => false, 'message' => 'Error creating table: ' . $mysqli->error]);
        exit;
    }
}

// 5. Process Update/Insert
if (isset($data['name'], $data['api_id'], $data['api_token'])) {
    $name = $mysqli->real_escape_string($data['name']);
    $api_id = $mysqli->real_escape_string($data['api_id']);
    $api_token = $mysqli->real_escape_string($data['api_token']);
    $work = isset($data['work']) ? (int) $data['work'] : 0;
    $data_time = date('Y-m-d H:i:s');

    $check_query = $mysqli->prepare("SELECT id FROM `yal_module` WHERE name = ?");
    if ($check_query) {
        $check_query->bind_param("s", $name);
        $check_query->execute();
        $check_result = $check_query->get_result();

        if ($check_result->num_rows > 0) {
            $update_query = $mysqli->prepare("UPDATE `yal_module` SET data_time = ?, work = ?, `api_id` = ?, `api_token` = ? WHERE name = ?");
            if ($update_query) {
                $update_query->bind_param("sisss", $data_time, $work, $api_id, $api_token, $name);
                if ($update_query->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Yalidine updated successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error updating Yalidine: ' . $update_query->error]);
                }
                $update_query->close();
            }
        } else {
            $insert_query = $mysqli->prepare("INSERT INTO `yal_module` (data_time, work, name, `api_id`, `api_token`) VALUES (?, ?, ?, ?, ?)");
            if ($insert_query) {
                $insert_query->bind_param("sisss", $data_time, $work, $name, $api_id, $api_token);
                if ($insert_query->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Yalidine added successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error inserting Yalidine: ' . $insert_query->error]);
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
