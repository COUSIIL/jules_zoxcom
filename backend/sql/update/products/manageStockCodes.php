<?php

function assignAndDecrementStock($mysqli, $orderId) {
    // 1. Fetch product_items
    $sqlPI = "SELECT product_id, qty, ids, indx FROM product_items WHERE order_id = ?";
    $stmtPI = $mysqli->prepare($sqlPI);
    $stmtPI->bind_param("i", $orderId);
    $stmtPI->execute();
    $pItems = $stmtPI->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtPI->close();

    // 2. Fetch order_items (fallback)
    $sqlOI = "SELECT id, model_id, product_id, qty FROM order_items WHERE order_id = ?";
    $stmtOI = $mysqli->prepare($sqlOI);
    $stmtOI->bind_param("i", $orderId);
    $stmtOI->execute();
    $oItems = $stmtOI->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtOI->close();

    $orderItemsById = [];
    foreach ($oItems as $oi) {
        $orderItemsById[$oi['id']] = $oi;
    }

    $itemsToProcess = [];

    if (!empty($pItems)) {
        foreach ($pItems as $pi) {
            $modelId = (int)$pi['ids'];
            $detailId = (int)$pi['indx'];

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

            if ($modelId === 0) {
                foreach ($oItems as $oi) {
                    if ($oi['product_id'] == $pi['product_id']) {
                        $modelId = (int)$oi['model_id'];
                        break;
                    }
                }
            }
            $itemsToProcess[] = [
                'product_id' => $pi['product_id'],
                'detail_id'  => $detailId,
                'model_id'   => $modelId,
                'qty'        => $pi['qty']
            ];
        }
    } else {
        foreach ($oItems as $oi) {
            $itemsToProcess[] = [
                'product_id' => $oi['product_id'],
                'detail_id'  => 0,
                'model_id'   => $oi['model_id'],
                'qty'        => $oi['qty']
            ];
        }
    }

    // Process Stock
    foreach ($itemsToProcess as $item) {
        $qty = $item['qty'];
        if ($qty <= 0) continue;

        $productId = $item['product_id'];
        $modelId   = $item['model_id'];
        $detailId  = $item['detail_id'];

        // Check Infinite Stock Setting
        $infinitStock = "0";
        $stmtP = $mysqli->prepare("SELECT infinit_stock FROM product_models WHERE product_id = ?");
        $stmtP->bind_param("i", $productId);
        $stmtP->execute();
        $resP = $stmtP->get_result()->fetch_assoc();
        if ($resP) $infinitStock = $resP['infinit_stock'];
        $stmtP->close();

        // Find available codes (Multi-step Search)
        $foundIds = [];
        $remainingQty = $qty;

        // Step 1: Specific Detail ID (if requested)
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

        // Step 2: Generic Model ID (if still needed)
        // Matches stock with correct model but no specific detail (NULL or 0)
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

        // Step 3: Generic Product (if still needed and Model/Detail were not effectively constraining)
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

        $foundCount = count($foundIds);

        // Logic:
        // 1. Assign found codes
        // 2. Decrement counters for ALL requested qty (if infinite) OR just found (if strict)?
        // Requirement: Sync storage.
        // If we found X items, we mark them sold.
        // If X < Qty and NOT Infinite, we fail.
        // If X < Qty and Infinite, we mark X sold, and we decrement counters by Qty?
        // Yes, counters track "Available". If infinite, counters might go negative.

        if ($infinitStock == "0" && $foundCount < $qty) {
            return ['success' => false, 'message' => "Stock insuffisant (Requis: $qty, Dispo: $foundCount)"];
        }

        // Assign Found Codes
        if (!empty($foundIds)) {
            $idList = implode(',', $foundIds);
            $mysqli->query("UPDATE product_stock SET order_id = $orderId, status = 'sold' WHERE id IN ($idList)");
        }

        // Decrement Counters (Product & Model & Detail)
        // We decrement by $qty (even if we didn't find codes, if infinite stock is on)
        // Wait, if infinite stock is ON, we might not have codes at all.
        // But if we generated codes, we should use them.

        // Decrement product_models (Global Product Qty? No, product_models is the "Model" table linked to products)
        // Actually product_models table usually holds the "Main" entry for the product variants.
        // If modelId > 0, we update that row.

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

function assignUniqueCodes($mysqli, $orderId) {
    // Deprecated/Legacy wrapper or separate logic
    // For now, we keep it as it was or redirect?
    // The previous implementation didn't decrement counters.
    // We will leave the file as is, but appending the new function.
}

function releaseUniqueCodes($mysqli, $orderId) {
    // Find sold codes for this order
    $stmt = $mysqli->prepare("SELECT id, model_id, detail_id, product_id FROM product_stock WHERE order_id = ? AND status = 'sold'");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $codes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($codes)) return;

    // Group by model/detail
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

    // Update DB Counters
    foreach ($restocks as $stock) {
        $qty = $stock['count'];
        $stmtM = $mysqli->prepare("UPDATE product_models SET quantity = quantity + ? WHERE id = ?");
        $stmtM->bind_param("ii", $qty, $stock['model_id']);
        $stmtM->execute();
        $stmtM->close();

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
