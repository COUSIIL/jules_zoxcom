<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

$stock_id = isset($_GET['stock_id']) ? (int)$_GET['stock_id'] : 0;

if ($stock_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Stock ID']);
    exit;
}

$sql = "SELECT * FROM stock_history WHERE stock_id = ? ORDER BY date DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $stock_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $data
]);

$mysqli->close();
?>
