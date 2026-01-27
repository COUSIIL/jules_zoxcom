<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

$product_id = isset($_GET['product_id']) ? (int)$$_GET['product_id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Product ID']);
    exit;
}

$sql = "SELECT ps.*, o.name as order_ref
        FROM product_stock ps
        LEFT JOIN orders o ON ps.order_id = o.id
        WHERE ps.product_id = ?
        ORDER BY ps.id DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $data
]);

$mysqli->close();
?>
