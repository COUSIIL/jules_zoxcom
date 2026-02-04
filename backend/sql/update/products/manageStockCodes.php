<?php

/**
 * Helper to fetch items to process for an order.
 */
function getItemsForOrder($mysqli, $orderId) {
    // Fetch from product_items
    $sqlPI = "SELECT product_id, qty, ids, indx FROM product_items WHERE order_id = ?";
    $stmtPI = $mysqli->prepare($sqlPI);
    $stmtPI->bind_param("i", $orderId);
    $stmtPI->execute();
    $pItems = $stmtPI->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtPI->close();

    $itemsToProcess = [];

    if (empty($pItems)) {
        // Fallback to order_items
        $sqlOI = "SELECT product_id, qty, model_id, id FROM order_items WHERE order_id = ?";
        $stmtOI = $mysqli->prepare($sqlOI);
        $stmtOI->bind_param("i", $orderId);
        $stmtOI->execute();
        $oItems = $stmtOI->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtOI->close();

        foreach ($oItems as $oi) {
            $itemsToProcess[] = [
                'product_id' => $oi['product_id'],
                'detail_id'  => 0,
                'model_id'   => $oi['model_id'],
                'qty'        => $oi['qty']
            ];
        }
    } else {
        foreach ($pItems as $pi) {
            $modelId = (int)$pi['ids'];
            $detailId = (int)$pi['indx'];

            // Resolve 0 IDs
            if ($modelId === 0 && $detailId > 0) {
                $stmtD = $mysqli->prepare("SELECT model_id FROM model_details WHERE id = ?");
                if ($stmtD) {
                    $stmtD->bind_param("i", $detailId);
                    $stmtD->execute();
                    $resD = $stmtD->get_result();
                    if ($rowD = $resD->fetch_assoc()) $modelId = (int)$rowD['model_id'];
                    $stmtD->close();
                }
            }

            $itemsToProcess[] = [
                'product_id' => $pi['product_id'],
                'detail_id'  => $detailId,
                'model_id'   => $modelId,
                'qty'        => $pi['qty']
            ];
        }
    }
    return $itemsToProcess;
}

/**
 * 1. Decrement Quantity Counters (Reservation)
 * WITHOUT assigning physical stock.
 */
function reserveStockQuantity($mysqli, $orderId) {
    $items = getItemsForOrder($mysqli, $orderId);

    foreach ($items as $item) {
        $qty = $item['qty'];
        if ($qty <= 0) continue;

        $modelId = $item['model_id'];
        $detailId = $item['detail_id'];

        // Decrement Product Model
        if ($modelId > 0) {
            $stmtUpd = $mysqli->prepare("UPDATE product_models SET quantity = quantity - ? WHERE id = ?");
            $stmtUpd->bind_param("ii", $qty, $modelId);
            $stmtUpd->execute();
            $stmtUpd->close();
        }

        // Decrement Detail
        if ($detailId > 0) {
            $stmtUpdD = $mysqli->prepare("UPDATE model_details SET quantity = quantity - ? WHERE id = ?");
            $stmtUpdD->bind_param("ii", $qty, $detailId);
            $stmtUpdD->execute();
            $stmtUpdD->close();
        }
    }
    return true;
}

/**
 * 2. Assign Physical Stock Codes
 * Finds available codes and assigns them. Does NOT decrement counters.
 */
function assignPhysicalStock($mysqli, $orderId, $targetStatus = 'reserved') {
    // Check if already assigned enough?
    $stmtCheck = $mysqli->prepare("SELECT COUNT(*) as cnt FROM product_stock WHERE order_id = ?");
    $stmtCheck->bind_param("i", $orderId);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result()->fetch_assoc();
    $assignedCount = $resCheck['cnt'];
    $stmtCheck->close();

    $items = getItemsForOrder($mysqli, $orderId);
    $totalRequired = 0;
    foreach ($items as $itm) $totalRequired += $itm['qty'];

    if ($assignedCount >= $totalRequired && $totalRequired > 0) {
         updateStockStatus($mysqli, $orderId, $targetStatus);
         return ['success' => true, 'message' => 'Stock already assigned'];
    }

    foreach ($items as $item) {
        $qty = $item['qty'];
        if ($qty <= 0) continue;

        $productId = $item['product_id'];
        $modelId   = $item['model_id'];
        $detailId  = $item['detail_id'];

        $foundIds = [];
        $remainingQty = $qty;

        // Search steps (Detail -> Model -> Product)
        // Step 1: Specific Detail ID
        if ($remainingQty > 0 && $detailId && $detailId > 0) {
            $query = "SELECT id FROM product_stock WHERE product_id = ? AND status = 'available' AND detail_id = ? LIMIT ?";
            $stmtSearch = $mysqli->prepare($query);
            $stmtSearch->bind_param("iii", $productId, $detailId, $remainingQty);
            $stmtSearch->execute();
            $res = $stmtSearch->get_result();
            while ($row = $res->fetch_assoc()) {
                $foundIds[] = $row['id'];
                $remainingQty--;
            }
            $stmtSearch->close();
        }
        // Step 2: Generic Model ID
        if ($remainingQty > 0 && $modelId > 0) {
             $query = "SELECT id FROM product_stock WHERE product_id = ? AND status = 'available' AND model_id = ? AND (detail_id IS NULL OR detail_id = 0) LIMIT ?";
             $stmtSearch = $mysqli->prepare($query);
             $stmtSearch->bind_param("iii", $productId, $modelId, $remainingQty);
             $stmtSearch->execute();
             $res = $stmtSearch->get_result();
             while ($row = $res->fetch_assoc()) {
                 $foundIds[] = $row['id'];
                 $remainingQty--;
             }
             $stmtSearch->close();
        }
        // Step 3: Generic Product
        if ($remainingQty > 0 && $modelId == 0 && ($detailId == 0 || $detailId == null)) {
             $query = "SELECT id FROM product_stock WHERE product_id = ? AND status = 'available' AND (detail_id IS NULL OR detail_id = 0) LIMIT ?";
             $stmtSearch = $mysqli->prepare($query);
             $stmtSearch->bind_param("ii", $productId, $remainingQty);
             $stmtSearch->execute();
             $res = $stmtSearch->get_result();
             while ($row = $res->fetch_assoc()) {
                 $foundIds[] = $row['id'];
                 $remainingQty--;
             }
             $stmtSearch->close();
        }

        if (!empty($foundIds)) {
            $idList = implode(',', $foundIds);
            $mysqli->query("UPDATE product_stock SET order_id = $orderId, status = '$targetStatus' WHERE id IN ($idList)");
        }
    }
    return ['success' => true];
}

/**
 * 3. Restore Stock Counts (Increment)
 * Used when cancelling an order that had RESERVED stock (counters decremented).
 */
function restoreStockCounts($mysqli, $orderId) {
    $items = getItemsForOrder($mysqli, $orderId);

    foreach ($items as $item) {
        $qty = $item['qty'];
        if ($qty <= 0) continue;

        $modelId = $item['model_id'];
        $detailId = $item['detail_id'];

        if ($modelId > 0) {
            $stmtUpd = $mysqli->prepare("UPDATE product_models SET quantity = quantity + ? WHERE id = ?");
            $stmtUpd->bind_param("ii", $qty, $modelId);
            $stmtUpd->execute();
            $stmtUpd->close();
        }

        if ($detailId > 0) {
            $stmtUpdD = $mysqli->prepare("UPDATE model_details SET quantity = quantity + ? WHERE id = ?");
            $stmtUpdD->bind_param("ii", $qty, $detailId);
            $stmtUpdD->execute();
            $stmtUpdD->close();
        }
    }
}

/**
 * 4. Release Physical Stock (Set to Available)
 */
function releasePhysicalStock($mysqli, $orderId) {
    // Release codes to 'available'
    $stmtRel = $mysqli->prepare("UPDATE product_stock SET order_id = NULL, status = 'available' WHERE order_id = ? AND status != 'returned'");
    // Note: If status was 'returned', we don't release to 'available'.
    // If status was 'sold' or 'reserved' or 'shipping', we release.
    $stmtRel->bind_param("i", $orderId);
    $stmtRel->execute();
    $stmtRel->close();
}

// Helpers
function updateStockStatus($mysqli, $orderId, $status) {
    $stmt = $mysqli->prepare("UPDATE product_stock SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $orderId);
    $stmt->execute();
    $stmt->close();
}

function handleReturnStock($mysqli, $orderId) {
    $stmt = $mysqli->prepare("UPDATE product_stock SET status = 'returned', order_id = NULL WHERE order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $stmt->close();
}

// Legacy / Compatibility
function assignAndDecrementStock($mysqli, $orderId, $targetStatus = 'reserved') {
    reserveStockQuantity($mysqli, $orderId);
    return assignPhysicalStock($mysqli, $orderId, $targetStatus);
}

function releaseUniqueCodes($mysqli, $orderId) {
    // Smart release: Check if order status implies reservation
    $stmtS = $mysqli->prepare("SELECT status FROM orders WHERE id = ?");
    $stmtS->bind_param("i", $orderId);
    $stmtS->execute();
    $resS = $stmtS->get_result()->fetch_assoc();
    $status = $resS['status'] ?? '';
    $stmtS->close();

    // Statuses that imply stock was reserved/decremented
    $reservedStatuses = ['confirmed', 'shipping', 'completed'];

    if (in_array($status, $reservedStatuses)) {
        restoreStockCounts($mysqli, $orderId);
    }

    releasePhysicalStock($mysqli, $orderId);
}

function assignUniqueCodes($mysqli, $orderId) {
    return assignAndDecrementStock($mysqli, $orderId);
}
?>
