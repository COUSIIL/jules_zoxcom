<?php

function assignUniqueCodes($mysqli, $orderId) {
    // Fetch detailed items to know exactly which variant (detail_id) was bought
    $sql = "SELECT pi.product_id, pi.qty, pi.ids as detail_id, oi.model_id
            FROM product_items pi
            JOIN order_items oi ON pi.indx = oi.id
            WHERE pi.order_id = ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($items)) {
        // Fallback to order_items if product_items is empty (legacy support?)
        $stmt2 = $mysqli->prepare("SELECT product_id, qty, model_id, 0 as detail_id FROM order_items WHERE order_id = ?");
        $stmt2->bind_param("i", $orderId);
        $stmt2->execute();
        $items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();
    }

    foreach ($items as $item) {
        $productId = $item['product_id'];
        $modelId = $item['model_id']; // This comes from order_items (product_models.id)
        $detailId = $item['detail_id']; // This comes from product_items (model_details.id)
        $qty = $item['qty'];

        // Find available codes
        // Logic: specific detail matches detail_id.
        // If detailId is 0 or null, we look for stock with detail_id IS NULL (simple model stock).
        // Or should we fallback?
        // If stock was generated for a model (no variant), detail_id is NULL in DB.

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
             $query .= " AND model_id = ? AND (detail_id IS NULL OR detail_id = 0)";
             $params[0] .= "i";
             $params[] = $modelId;
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
