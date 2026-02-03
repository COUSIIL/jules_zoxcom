<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Config not found.']);
    exit;
}
require_once $configPath;

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id'], $data['code'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
    exit;
}

$orderId = (int)$data['order_id'];
$code = trim($data['code']);

// 1. Fetch the scanned code details
$stmt = $mysqli->prepare("SELECT id, product_id, model_id, detail_id, status, order_id FROM product_stock WHERE unique_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$stockItem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$stockItem) {
    echo json_encode(['success' => false, 'message' => 'Code invalid (not found).']);
    exit;
}

// 2. Logic based on status
if ($stockItem['status'] === 'sold') {
    if ($stockItem['order_id'] == $orderId) {
        echo json_encode(['success' => true, 'message' => 'Already assigned to this order.']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Code already sold to another order.']);
        exit;
    }
}

if ($stockItem['status'] === 'reserved') {
    if ($stockItem['order_id'] == $orderId) {
        // Just mark as sold
        $upd = $mysqli->prepare("UPDATE product_stock SET status = 'sold' WHERE id = ?");
        $upd->bind_param("i", $stockItem['id']);
        $upd->execute();
        $upd->close();

        // Also update order status to shipping if requested?
        // The frontend will likely handle the order status update call separately or we can do it here.
        // User said: "Une fois le shipping est demandé ca ouvre une page... a fin de l'atribuer définitivement comme sold"
        // It implies the order status change might happen after all items are scanned?
        // For now, just validating the code.

        echo json_encode(['success' => true, 'message' => 'Code validated.']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Code reserved for another order.']);
        exit;
    }
}

if ($stockItem['status'] === 'available') {
    // 3. Swap logic
    // We need to find if there is a 'reserved' item in this order matching the scanned item's product/model/variant

    $search = "SELECT id FROM product_stock
               WHERE order_id = ?
               AND status = 'reserved'
               AND product_id = ?
               AND model_id = ?
               AND (detail_id = ? OR (detail_id IS NULL AND ? = 0) OR (detail_id = 0 AND ? = 0))
               LIMIT 1";

    $stmtS = $mysqli->prepare($search);
    $dId = $stockItem['detail_id'] ?? 0;
    $stmtS->bind_param("iiiii", $orderId, $stockItem['product_id'], $stockItem['model_id'], $dId, $dId, $dId);
    $stmtS->execute();
    $reservedItem = $stmtS->get_result()->fetch_assoc();
    $stmtS->close();

    if ($reservedItem) {
        // Swap!
        $mysqli->begin_transaction();
        try {
            // Release the reserved item -> available
            $mysqli->query("UPDATE product_stock SET status = 'available', order_id = NULL WHERE id = " . $reservedItem['id']);

            // Assign the scanned item -> sold
            $stmtUpd = $mysqli->prepare("UPDATE product_stock SET status = 'sold', order_id = ? WHERE id = ?");
            $stmtUpd->bind_param("ii", $orderId, $stockItem['id']);
            $stmtUpd->execute();

            $mysqli->commit();
            echo json_encode(['success' => true, 'message' => 'Code swapped and validated.']);
            exit;

        } catch (Exception $e) {
            $mysqli->rollback();
            echo json_encode(['success' => false, 'message' => 'Swap failed: ' . $e->getMessage()]);
            exit;
        }
    } else {
        // No matching reserved item found.
        // This means the order might not have a reservation for this specific variant?
        // Or maybe it was already sold?
        // Or maybe logic error in assignment.

        // Strictly speaking, we should only allow if there was a reservation.
        // But maybe the order didn't have stock assigned yet? (e.g. Infinite stock but "Available" code scanned?)
        // If we assign an available code to an order without a prior reservation, we are consuming NEW stock.
        // We must decrement counters.

        // Check if the order actually needs this item?
        // Too complex to check `order_items` here easily without re-fetching everything.
        // For now, return error to be safe.
        echo json_encode(['success' => false, 'message' => 'No matching reservation found in this order for this item.']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid status: ' . $stockItem['status']]);
?>
