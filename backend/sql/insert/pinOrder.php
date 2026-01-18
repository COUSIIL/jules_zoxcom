<?php
header('Content-Type: application/json');
include __DIR__ . '/../../../backend/config/dbConfig.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->order_id) || !isset($data->reason)) {
    echo json_encode(["success" => false, "message" => "Missing order_id or reason"]);
    exit;
}

$orderId = $mysqli->real_escape_string($data->order_id);
$reason = $mysqli->real_escape_string($data->reason);

// Check if already pinned
$check = $mysqli->query("SELECT id FROM pinned_orders WHERE order_id = '$orderId'");
if ($check && $check->num_rows > 0) {
    // Update reason
    $sql = "UPDATE pinned_orders SET reason = '$reason' WHERE order_id = '$orderId'";
} else {
    // Insert
    $sql = "INSERT INTO pinned_orders (order_id, reason) VALUES ('$orderId', '$reason')";
}

if ($mysqli->query($sql)) {
    echo json_encode(["success" => true, "message" => "Order pinned successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $mysqli->error]);
}
?>