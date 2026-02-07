<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
$managePath = __DIR__ . '/products/manageStockCodes.php';
$stockHistory = __DIR__ . '/../../../backend/sql/create_stock_history_table.php';

if (!file_exists($configPath) || !file_exists($managePath) || !file_exists($stockHistory)) {
    echo json_encode(['success' => false, 'message' => 'Config or Helper not found.']);
    exit;
}

require_once $configPath;
require_once $managePath;
require_once $stockHistory;


$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing order_id.']);
    exit;
}
if (!isset($data['code']) && !isset($data['stock_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing code or stock_id.']);
    exit;
}

$orderId = (int)$data['order_id'];
$targetStatus = $data['target_status'] ?? 'reserved'; // Default to sold for compatibility, but frontend should specify
$user = $data['user'] ?? 'System';

if (!in_array($targetStatus, ['reserved', 'sold'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid target status.']);
    exit;
}

// 1. Fetch the stock item
if (isset($data['stock_id'])) {
    $stmt = $mysqli->prepare("SELECT id, product_id, model_id, detail_id, status, order_id, unique_code FROM product_stock WHERE id = ?");
    $stmt->bind_param("i", $data['stock_id']);
} else {
    $code = trim($data['code']);
    $stmt = $mysqli->prepare("SELECT id, product_id, model_id, detail_id, status, order_id, unique_code FROM product_stock WHERE unique_code = ?");
    $stmt->bind_param("s", $code);
}
$stmt->execute();
$stockItem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$stockItem) {
    echo json_encode(['success' => false, 'message' => 'Item not found.']);
    exit;
}

// 2. Logic based on status

// A. Already assigned to this order
if ($stockItem['order_id'] == $orderId) {
    if ($stockItem['status'] === $targetStatus) {
        echo json_encode(['success' => true, 'message' => 'Already assigned and status matches.']);
        exit;
    }
    // Update status (e.g. reserved -> sold)
    $upd = $mysqli->prepare("UPDATE product_stock SET status = ? WHERE id = ?");
    $upd->bind_param("si", $targetStatus, $stockItem['id']);
    $upd->execute();
    $upd->close();

    logStockHistory($mysqli, $stockItem['id'], $stockItem['unique_code'], 'update_status', $stockItem['status'], $targetStatus, $orderId, $user);

    echo json_encode(['success' => true, 'message' => 'Status updated.']);
    exit;
}

// B. Assigned to another order
if ($stockItem['order_id'] && $stockItem['order_id'] != $orderId) {
    // If it's sold, we can't take it.
    if ($stockItem['status'] === 'sold') {
        echo json_encode(['success' => false, 'message' => 'Item sold to another order.']);
        exit;
    }
    // If reserved, we technically could steal it, but usually not.
    echo json_encode(['success' => false, 'message' => 'Item reserved for another order.']);
    exit;
}

// C. Available (or Reserved/Sold but NULL order_id which shouldn't happen, or Returned)
if ($stockItem['status'] === 'available') {
    // Check if we need to SWAP with an existing reserved item for this order
    // (e.g. Order reserved generic code A, user scans code B)

    // Find a reserved item for this order matching the product/model/detail of the scanned item
    $search = "SELECT id, unique_code, status FROM product_stock
               WHERE order_id = ?
               AND status = 'available'
               AND product_id = ?
               AND model_id = ?
               AND (detail_id = ? OR (detail_id IS NULL AND ? = 0) OR (detail_id = 0 AND ? = 0))
               LIMIT 1";

    $stmtS = $mysqli->prepare($search);
    $dId = $stockItem['detail_id'] ?? 0;
    $stmtS->bind_param("iiiiii", $orderId, $stockItem['product_id'], $stockItem['model_id'], $dId, $dId, $dId);
    $stmtS->execute();
    $reservedItem = $stmtS->get_result()->fetch_assoc();
    $stmtS->close();

    $mysqli->begin_transaction();
    try {
        if ($reservedItem) {
            // Swap: Release reserved item
            $mysqli->query("UPDATE product_stock SET status = 'available', order_id = NULL WHERE id = " . $reservedItem['id']);
            logStockHistory($mysqli, $reservedItem['id'], $reservedItem['unique_code'], 'swap_release', $reservedItem['status'], 'available', $orderId, $user);
        } else {
             // If no reserved item to swap, verify we strictly need this item
             // (Logic from before: check requirements)
             $items = getItemsForOrder($mysqli, $orderId);
             $strictNeeded = 0;
             $d0 = $stockItem['detail_id'] ?? 0;

             foreach ($items as $itm) {
                 if ($itm['product_id'] == $stockItem['product_id'] &&
                     $itm['model_id'] == $stockItem['model_id'] &&
                     $itm['detail_id'] == $d0) {
                     $strictNeeded += $itm['qty'];
                 }
             }
             
             // Count currently assigned (reserved or sold)
             $strictAssigned = 0;
             $stmtSA = $mysqli->prepare("SELECT COUNT(*) as cnt FROM product_stock WHERE order_id = ? AND product_id = ? AND model_id = ? AND detail_id = ?");
             $stmtSA->bind_param("iiii", $orderId, $stockItem['product_id'], $stockItem['model_id'], $d0);
             $stmtSA->execute();
             $resSA = $stmtSA->get_result()->fetch_assoc();
             $strictAssigned = $resSA['cnt'];
             $stmtSA->close();

             if ($strictNeeded <= $strictAssigned) {
                 throw new Exception("Order fully assigned for this item. Release an item first or increase quantity.");
             }
        }

        // Assign new item
        $stmtUpd = $mysqli->prepare("UPDATE product_stock SET status = ?, order_id = ? WHERE id = ?");
        $stmtUpd->bind_param("sii", $targetStatus, $orderId, $stockItem['id']);
        $stmtUpd->execute();
        
        logStockHistory($mysqli, $stockItem['id'], $stockItem['unique_code'], 'assign_'.$targetStatus, $stockItem['status'], $targetStatus, $orderId, $user);

        $mysqli->commit();
        echo json_encode(['success' => true, 'message' => 'Item assigned.']);
        exit;

    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Item status is ' . $stockItem['status']]);
?>
