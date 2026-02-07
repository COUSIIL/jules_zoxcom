<?php
$configPath = __DIR__ . '/../config/dbConfig.php';
if (!file_exists($configPath)) {
    die("Config not found at $configPath");
}
require_once $configPath;

$sql = "CREATE TABLE IF NOT EXISTS stock_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_id INT NULL,
    unique_code VARCHAR(255) NULL,
    action VARCHAR(50) NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NULL,
    order_id INT NULL,
    user VARCHAR(100) NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (stock_id),
    INDEX (unique_code),
    INDEX (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($mysqli->query($sql) === FALSE) {
    echo "Error creating table: " . $mysqli->error . "\n";
}
?>
