<?php
header('Content-Type: application/json');
include __DIR__ . '/../../../backend/config/dbConfig.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->order_id)) {
    echo json_encode(["success" => false, "message" => "Missing order_id"]);
    exit;
}

$orderId = $mysqli->real_escape_string($data->order_id);

$sql = "DELETE FROM pinned_orders WHERE order_id = '$orderId'";

if ($mysqli->query($sql)) {
    echo json_encode(["success" => true, "message" => "Order unpinned successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $mysqli->error]);
}
?>