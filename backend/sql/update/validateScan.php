<?php

header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Required config not found.'
    ]);
    exit;
}

require_once $configPath;

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id'], $data['code'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing order_id or code.'
    ]);
    exit;
}

$orderId = (int)$data['order_id'];
$code = $mysqli->real_escape_string($data['code']);

// 1. Fetch the Code
$stmt = $mysqli->prepare("SELECT id, product_id, model_id, detail_id, status, order_id, unique_code FROM product_stock WHERE unique_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$res = $stmt->get_result();
$stockItem = $res->fetch_assoc();
$stmt->close();

if (!$stockItem) {
    echo json_encode(['success' => false, 'message' => 'Code introuvable.']);
    exit;
}

// 2. Check Availability
if ($stockItem['status'] === 'sold') {
    echo json_encode(['success' => false, 'message' => 'Ce code est déjà vendu.']);
    exit;
}

if ($stockItem['status'] !== 'available' && $stockItem['order_id'] != $orderId) {
    // Reserved for someone else?
    echo json_encode(['success' => false, 'message' => 'Ce code est réservé pour une autre commande.']);
    exit;
}

if ($stockItem['order_id'] == $orderId) {
    // Already assigned to this order
    echo json_encode(['success' => true, 'message' => 'Déjà scanné pour cette commande.', 'already_scanned' => true]);
    exit;
}

// 3. Check Order Requirements
// We need to see if the order needs this specific product/variant.

// Fetch required items
$itemsToProcess = [];

// Try product_items first (preferred source of truth for variants)
$sqlPI = "SELECT product_id, qty, ids, indx FROM product_items WHERE order_id = ?";
$stmtPI = $mysqli->prepare($sqlPI);
$stmtPI->bind_param("i", $orderId);
$stmtPI->execute();
$pItems = $stmtPI->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtPI->close();

if (empty($pItems)) {
    // Fallback to order_items
    $sqlOI = "SELECT product_id, qty, model_id FROM order_items WHERE order_id = ?";
    $stmtOI = $mysqli->prepare($sqlOI);
    $stmtOI->bind_param("i", $orderId);
    $stmtOI->execute();
    $oItems = $stmtOI->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtOI->close();

    foreach ($oItems as $oi) {
        $itemsToProcess[] = [
            'product_id' => $oi['product_id'],
            'detail_id'  => 0,
            'model_id'   => $oi['model_id'], // fallback
            'qty'        => $oi['qty']
        ];
    }
} else {
    foreach ($pItems as $pi) {
        $modelId = (int)$pi['ids'];
        $detailId = (int)$pi['indx'];

        // Resolve 0 IDs if needed (e.g. if indx points to model_details but ids is 0)
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

// Check matching requirement
$matchedRequirement = null;
$totalRequired = 0;
$totalAssigned = 0;

// Helper to check match
function isMatch($req, $stock) {
    if ($req['product_id'] != $stock['product_id']) return false;

    // Strict check for variants
    // If requirement specifies detail_id > 0, stock must match exactly
    if ($req['detail_id'] > 0) {
        if ($req['detail_id'] != $stock['detail_id']) return false;
    }

    // If requirement specifies model_id > 0 (and no detail or detail match passed)
    if ($req['model_id'] > 0) {
        if ($req['model_id'] != $stock['model_id']) return false;
    }

    // Note: If req has model_id=0, detail_id=0 (generic product), any stock of that product matches?
    // Usually yes.
    return true;
}

// Find if this stock item matches ANY requirement
foreach ($itemsToProcess as $req) {
    if (isMatch($req, $stockItem)) {
        // Found a potential match. Now check if we still need one.
        // Count how many assigned for this specific requirement?
        // This is tricky because assigned stock might match multiple generic requirements.
        // We need to count assigned stock that *matches this requirement* and see if < qty.

        // Count assigned stock for this variant
        $assignedCount = 0;
        $sqlCount = "SELECT COUNT(*) as cnt FROM product_stock WHERE order_id = ? AND product_id = ? AND status IN ('reserved', 'shipping')";
        
        $types = "ii";
        $params = [$orderId, $req['product_id']];

        if ($req['detail_id'] > 0) {
            $sqlCount .= " AND detail_id = ?";
            $types .= "i";
            $params[] = $req['detail_id'];
        } elseif ($req['model_id'] > 0) {
            $sqlCount .= " AND model_id = ?";
            $types .= "i";
            $params[] = $req['model_id'];
        }

        $stmtC = $mysqli->prepare($sqlCount);
        $stmtC->bind_param($types, ...$params);
        $stmtC->execute();
        $resC = $stmtC->get_result()->fetch_assoc();
        $assignedCount = $resC['cnt'];
        $stmtC->close();

        if ($assignedCount < $req['qty']) {
            $matchedRequirement = $req;
            break;
        }
    }
}

if (!$matchedRequirement) {
    echo json_encode(['success' => false, 'message' => 'Ce produit/variante n\'est pas requis ou la quantité est déjà atteinte.']);
    exit;
}

// 4. Update Stock
$updateSql = "UPDATE product_stock SET order_id = ?, status = 'reserved' WHERE id = ?";
$stmtU = $mysqli->prepare($updateSql);
$stmtU->bind_param("ii", $orderId, $stockItem['id']);

if ($stmtU->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Code validé et assigné.',
        'stock' => [
            'id' => $stockItem['id'],
            'unique_code' => $stockItem['unique_code'],
            'product_id' => $stockItem['product_id'],
            'model_id' => $stockItem['model_id'],
            'detail_id' => $stockItem['detail_id']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour: ' . $stmtU->error]);
}

$stmtU->close();
$mysqli->close();
