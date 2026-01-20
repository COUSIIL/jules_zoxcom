<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}

require_once $configPath;

// Double check if empty
$countRes = $mysqli->query("SELECT COUNT(*) as count FROM orders");
$countData = $countRes->fetch_assoc();

if ($countData['count'] == 0) {
    // Reset Auto Increment
    // TRUNCATE is the fastest way and resets auto_increment
    // However, FK constraints might prevent TRUNCATE.
    // We try TRUNCATE first, if fail, we DELETE (already done) and ALTER.

    // Disable FK checks temporarily for TRUNCATE
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

    if ($mysqli->query("TRUNCATE TABLE orders")) {
        $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
        echo json_encode(['success' => true, 'message' => 'Order IDs reset successfully']);
    } else {
        // Fallback
        $mysqli->query("ALTER TABLE orders AUTO_INCREMENT = 1");
        $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
        echo json_encode(['success' => true, 'message' => 'Order IDs reset (via ALTER)']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Cannot reset IDs: Table not empty']);
}

$mysqli->close();
?>