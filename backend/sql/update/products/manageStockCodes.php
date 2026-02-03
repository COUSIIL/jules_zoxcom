<?php

/**
 * Assigns unique stock codes to an order and decrements the global sellable quantity.
 * Prevents double assignment if stock is already assigned.
 */
function assignAndDecrementStock($mysqli, $orderId, $targetStatus = 'reserved') {
    // 0. Check if stock is already assigned to this order
    // We count how many items are currently assigned
    $stmtCheck = $mysqli->prepare("SELECT COUNT(*) as cnt FROM product_stock WHERE order_id = ?");
    $stmtCheck->bind_param("i", $orderId);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result()->fetch_assoc();
    $assignedCount = $resCheck['cnt'];
    $stmtCheck->close();

    // Fetch required quantities from product_items or order_items
    $sqlPI = "SELECT product_id, qty, ids, indx FROM product_items WHERE order_id = ?";
    $stmtPI = $mysqli->prepare($sqlPI);
    $stmtPI->bind_param("i", $orderId);
    $stmtPI->execute();
    $pItems = $stmtPI->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtPI->close();

    // If product_items is empty, try order_items (Fallback)
    if (empty($pItems)) {
        $sqlOI = "SELECT product_id, qty, model_id, id FROM order_items WHERE order_id = ?";
        $stmtOI = $mysqli->prepare($sqlOI);
        $stmtOI->bind_param("i", $orderId);
        $stmtOI->execute();
        $oItems = $stmtOI->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtOI->close();

        $itemsToProcess = [];
        foreach ($oItems as $oi) {
            $itemsToProcess[] = [
                'product_id' => $oi['product_id'],
                'detail_id'  => 0, // order_items usually doesn't have detail link directly unless parsed
                'model_id'   => $oi['model_id'],
                'qty'        => $oi['qty']
            ];
        }
    } else {
        $itemsToProcess = [];
        foreach ($pItems as $pi) {
            $modelId = (int)$pi['ids'];
            $detailId = (int)$pi['indx'];

            // Resolving 0 IDs if necessary (legacy logic)
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

            // If modelId is still 0, try to recover from order_items (not implemented fully here as in original, assuming valid data)

            $itemsToProcess[] = [
                'product_id' => $pi['product_id'],
                'detail_id'  => $detailId,
                'model_id'   => $modelId,
                'qty'        => $pi['qty']
            ];
        }
    }

    $totalRequired = 0;
    foreach ($itemsToProcess as $itm) $totalRequired += $itm['qty'];

    // If we already have enough assigned stock, we assume it's done.
    // (Simplistic check: if assigned count >= required count, we skip to avoid double decrement)
    if ($assignedCount >= $totalRequired && $totalRequired > 0) {
         // Just ensure status is correct if needed
         updateStockStatus($mysqli, $orderId, $targetStatus);
         return ['success' => true, 'message' => 'Stock already assigned'];
    }

    // Process Stock Assignment
    foreach ($itemsToProcess as $item) {
        $qty = $item['qty'];
        if ($qty <= 0) continue;

        $productId = $item['product_id'];
        $modelId   = $item['model_id'];
        $detailId  = $item['detail_id'];

        // Check Infinite Stock Setting
        $infinitStock = "0";
        $stmtP = $mysqli->prepare("SELECT infinit_stock FROM product_models WHERE product_id = ?"); // Assuming product_models table link, or products table? Original code queried product_models with product_id.
        // NOTE: In original code: SELECT infinit_stock FROM product_models WHERE product_id = ?
        // Usually product_models table has 'id', 'product_id', 'quantity'.
        // Let's stick to original query structure.
        $stmtP->bind_param("i", $productId);
        $stmtP->execute();
        $resP = $stmtP->get_result()->fetch_assoc();
        if ($resP) $infinitStock = $resP['infinit_stock'];
        $stmtP->close();

        // Find available codes (Multi-step Search)
        $foundIds = [];
        $remainingQty = $qty;

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

        // Assign Found Codes
        if (!empty($foundIds)) {
            $idList = implode(',', $foundIds);
            $mysqli->query("UPDATE product_stock SET order_id = $orderId, status = '$targetStatus' WHERE id IN ($idList)");
        }

        // Decrement Counters (Product & Model & Detail)
        // Only decrement if infinite stock is OFF OR if we want to track negative stock.
        // Assuming we always track stock:

        if ($modelId > 0) {
            $stmtUpd = $mysqli->prepare("UPDATE product_models SET quantity = quantity - ? WHERE id = ?");
            $stmtUpd->bind_param("ii", $qty, $modelId);
            $stmtUpd->execute();
            $stmtUpd->close();
        }

        if ($detailId > 0) {
            $stmtUpdD = $mysqli->prepare("UPDATE model_details SET quantity = quantity - ? WHERE id = ?");
            $stmtUpdD->bind_param("ii", $qty, $detailId);
            $stmtUpdD->execute();
            $stmtUpdD->close();
        }
    }

    return ['success' => true];
}

/**
 * Updates the status of all stock items assigned to an order.
 * e.g., 'reserved' -> 'sold'
 */
function updateStockStatus($mysqli, $orderId, $status) {
    $stmt = $mysqli->prepare("UPDATE product_stock SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $orderId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Handles 'Returned' orders.
 * Sets unique codes to 'returned' status (damaged/returned state).
 * Does NOT increment available quantity (since it's not available).
 */
function handleReturnStock($mysqli, $orderId) {
    // Set status to 'returned'
    // We keep order_id for history or clear it?
    // Usually, we might want to keep it to know which order returned it,
    // OR we clear it so it's not linked to an active order in the UI.
    // The user said "returned et autre a removed ou retourned".
    // Let's set status to 'returned' and keep order_id to NULL to detach from the active order flow,
    // OR keep it attached.
    // If we detach (order_id = NULL), it appears in the stock list as a "Returned" item.

    $stmt = $mysqli->prepare("UPDATE product_stock SET status = 'returned', order_id = NULL WHERE order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $stmt->close();

    // We do NOT increment product_models/model_details quantity because it is not 'available' for sale.
}

/**
 * Releases stock (e.g., Canceled order).
 * Sets status to 'available' and increments sellable quantity.
 */
function releaseUniqueCodes($mysqli, $orderId) {
    // Find codes that are currently held by this order (reserved, sold, returned? no, only those that were holding stock)
    // If it was 'returned', it wasn't holding 'available' stock calculation if logic is correct.
    // We strictly look for statuses that imply the item was 'out': 'reserved', 'sold', 'shipping'.

    $stmt = $mysqli->prepare("SELECT id, model_id, detail_id FROM product_stock WHERE order_id = ? AND status IN ('reserved', 'sold', 'shipping', 'completed')");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $codes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($codes)) return;

    // Group by model/detail for bulk counter update
    $restocks = [];
    foreach ($codes as $code) {
        $key = $code['model_id'] . '-' . ($code['detail_id'] ?? '0');
        if (!isset($restocks[$key])) {
            $restocks[$key] = [
                'model_id' => $code['model_id'],
                'detail_id' => $code['detail_id'],
                'count' => 0
            ];
        }
        $restocks[$key]['count']++;
    }

    // Update DB Counters (Increment)
    foreach ($restocks as $stock) {
        $qty = $stock['count'];
        $modelId = $stock['model_id'];
        $detailId = $stock['detail_id'];

        if ($modelId > 0) {
            $stmtM = $mysqli->prepare("UPDATE product_models SET quantity = quantity + ? WHERE id = ?");
            $stmtM->bind_param("ii", $qty, $modelId);
            $stmtM->execute();
            $stmtM->close();
        }

        if ($detailId > 0) {
            $stmtD = $mysqli->prepare("UPDATE model_details SET quantity = quantity + ? WHERE id = ?");
            $stmtD->bind_param("ii", $qty, $detailId);
            $stmtD->execute();
            $stmtD->close();
        }
    }

    // Release codes to 'available'
    $stmtRel = $mysqli->prepare("UPDATE product_stock SET order_id = NULL, status = 'available' WHERE order_id = ?");
    $stmtRel->bind_param("i", $orderId);
    $stmtRel->execute();
    $stmtRel->close();
}

// Alias for compatibility if needed, but assignUniqueCodes was empty in previous file.
function assignUniqueCodes($mysqli, $orderId) {
    return assignAndDecrementStock($mysqli, $orderId);
}

?>
