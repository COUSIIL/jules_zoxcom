<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

$id = (int)$data['id'];

$mysqli->begin_transaction();

try {
    // Get info before delete
    $stmt = $mysqli->prepare("SELECT product_id, model_id, detail_id, status FROM product_stock WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$res) {
        throw new Exception("Stock item not found");
    }

    // Only decrement count if it was strictly 'available' (meaning it was counted in stock).
    // If 'sold', it's already out of stock count (presumably, depending on how stock is tracked).
    // Wait, earlier I said 'qty' is total stock.
    // If I sell an item, 'qty' goes down in legacy system.
    // If I generate stock, 'qty' goes up.
    // If I have a unique code 'sold', the physical item is gone.
    // So 'available' codes represent the 'quantity' number.
    // 'sold' codes represent history.
    // So if I delete a 'sold' code, I just remove history. I do NOT change quantity.
    // If I delete an 'available' code, I MUST decrement quantity.

    if ($res['status'] === 'available') {
        // Decrement product_models
        $stmtUpdModel = $mysqli->prepare("UPDATE product_models SET quantity = quantity - 1 WHERE id = ?");
        $stmtUpdModel->bind_param("i", $res['model_id']);
        $stmtUpdModel->execute();
        $stmtUpdModel->close();

        // Decrement model_details if exists
        if ($res['detail_id']) {
            $stmtUpdDetail = $mysqli->prepare("UPDATE model_details SET quantity = quantity - 1 WHERE id = ?");
            $stmtUpdDetail->bind_param("i", $res['detail_id']);
            $stmtUpdDetail->execute();
            $stmtUpdDetail->close();
        }
    }

    $stmtDel = $mysqli->prepare("DELETE FROM product_stock WHERE id = ?");
    $stmtDel->bind_param("i", $id);
    $stmtDel->execute();
    $stmtDel->close();

    $mysqli->commit();
    echo json_encode(['success' => true, 'message' => 'Stock removed']);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$mysqli->close();
?>
