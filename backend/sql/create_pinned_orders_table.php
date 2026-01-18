<?php
include __DIR__ . '/../../config/dbConfig.php';

$sql = "CREATE TABLE IF NOT EXISTS pinned_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";

if (mysqli_query($mysqli, $sql)) {
    echo "Table pinned_orders created successfully";
} else {
    echo "Error creating table: " . mysqli_error($mysqli);
}
?>