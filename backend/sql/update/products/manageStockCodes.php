<?php

function assignUniqueCodes($mysqli, $orderId) {
    // 1. Fetch product_items
    $sqlPI = "SELECT product_id, qty, ids as detail_id, indx FROM product_items WHERE order_id = ?";
    $stmtPI = $mysqli->prepare($sqlPI);
    $stmtPI->bind_param("i", $orderId);
    $stmtPI->execute();
    $pItems = $stmtPI->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtPI->close();

    // 2. Fetch order_items (for model_id lookup)
    $sqlOI = "SELECT id, model_id, product_id, qty FROM order_items WHERE order_id = ?";
    $stmtOI = $mysqli->prepare($sqlOI);
    $stmtOI->bind_param("i", $orderId);
    $stmtOI->execute();
    $oItems = $stmtOI->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtOI->close();

    // Index order_items by ID for fast lookup
    $orderItemsById = [];
    foreach ($oItems as $oi) {
        $orderItemsById[$oi['id']] = $oi;
    }

    $itemsToProcess = [];

    if (!empty($pItems)) {
        // We have detailed items
        foreach ($pItems as $pi) {
            $modelId = 0;
            // Try to find model_id via indx
            if (isset($orderItemsById[$pi['indx']])) {
                $modelId = $orderItemsById[$pi['indx']]['model_id'];
            } else {
                // indx might be broken or 0. Try to match by product_id?
                foreach ($oItems as $oi) {
                    if ($oi['product_id'] == $pi['product_id']) {
                        $modelId = $oi['model_id'];
                        break;
                    }
                }
            }

            $itemsToProcess[] = [
                'product_id' => $pi['product_id'],
                'detail_id' => $pi['detail_id'],
                'model_id' => $modelId,
                'qty' => $pi['qty']
            ];
        }
    } else {
        // Fallback: No product_items, use order_items directly (Legacy/Generic)
        foreach ($oItems as $oi) {
            $itemsToProcess[] = [
                'product_id' => $oi['product_id'],
                'detail_id' => 0,
                'model_id' => $oi['model_id'],
                'qty' => $oi['qty']
            ];
        }
    }

    foreach ($itemsToProcess as $item) {
        $productId = $item['product_id'];
        $modelId = $item['model_id'];
        $detailId = $item['detail_id'];
        $qty = $item['qty'];

        // Find available codes
        $query = "SELECT id FROM product_stock
                  WHERE product_id = ?
                  AND status = 'available'";

        $params = ["i", $productId];

        if ($detailId && $detailId > 0) {
             // If we bought a specific variant, look for that variant's stock
             $query .= " AND detail_id = ?";
             $params[0] .= "i";
             $params[] = $detailId;
        } else {
             // If we bought a simple model (or product_items has 0), look for matching model_id
             // AND detail_id IS NULL (to ensure we don't pick a variant code for a generic order if mixed)
             if ($modelId > 0) {
                $query .= " AND model_id = ?";
                $params[0] .= "i";
                $params[] = $modelId;
             }
             $query .= " AND (detail_id IS NULL OR detail_id = 0)";
        }

        $query .= " LIMIT ?";
        $params[0] .= "i";
        $params[] = $qty;

        $stmtSearch = $mysqli->prepare($query);
        $stmtSearch->bind_param(...$params);
        $stmtSearch->execute();
        $res = $stmtSearch->get_result();

        $idsToUpdate = [];
        while ($row = $res->fetch_assoc()) {
            $idsToUpdate[] = $row['id'];
        }
        $stmtSearch->close();

        // Update found codes
        if (!empty($idsToUpdate)) {
            $idList = implode(',', $idsToUpdate);
            $updateSql = "UPDATE product_stock SET order_id = ?, status = 'sold' WHERE id IN ($idList)";
            $stmtUpdate = $mysqli->prepare($updateSql);
            $stmtUpdate->bind_param("i", $orderId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    }
}

function releaseUniqueCodes($mysqli, $orderId) {
    // Find sold codes for this order
    $stmt = $mysqli->prepare("SELECT id, model_id, detail_id, product_id FROM product_stock WHERE order_id = ? AND status = 'sold'");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $codes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($codes)) return;

    // We need to restore stock count (Quantity) because the legacy system decremented it on confirm.
    // It likely does NOT auto-increment on return (based on read files).
    // So we assume responsibility to increment it back.

    // Group by model/detail to minimize queries
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

    // Update DB
    foreach ($restocks as $stock) {
        $qty = $stock['count'];

        // 1. Update product_models
        $stmtM = $mysqli->prepare("UPDATE product_models SET quantity = quantity + ? WHERE id = ?");
        $stmtM->bind_param("ii", $qty, $stock['model_id']);
        $stmtM->execute();
        $stmtM->close();

        // 2. Update model_details if exists
        if ($stock['detail_id']) {
            $stmtD = $mysqli->prepare("UPDATE model_details SET quantity = quantity + ? WHERE id = ?");
            $stmtD->bind_param("ii", $qty, $stock['detail_id']);
            $stmtD->execute();
            $stmtD->close();
        }
    }

    // Release codes
    $stmtRel = $mysqli->prepare("UPDATE product_stock SET order_id = NULL, status = 'available' WHERE order_id = ?");
    $stmtRel->bind_param("i", $orderId);
    $stmtRel->execute();
    $stmtRel->close();
}
?>
