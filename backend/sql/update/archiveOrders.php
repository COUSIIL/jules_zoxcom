<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
$triggerPath = __DIR__ . '/../../../backend/trigger_update.php';

if (!file_exists($configPath) || !file_exists($triggerPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration files not found.']);
    exit;
}

require_once $configPath;
require_once $triggerPath;

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_ids']) || !is_array($data['order_ids'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$orderIds = array_map('intval', $data['order_ids']);
if (empty($orderIds)) {
    echo json_encode(['success' => false, 'message' => 'No orders selected']);
    exit;
}

// 1. Create Archive Table if not exists
$checkTable = "SHOW TABLES LIKE 'orders_archive'";
$result = $mysqli->query($checkTable);

if ($result->num_rows == 0) {
    // Create base table structure
    if (!$mysqli->query("CREATE TABLE orders_archive LIKE orders")) {
        echo json_encode(['success' => false, 'message' => 'Failed to create archive table: ' . $mysqli->error]);
        exit;
    }
    // Add extra column for flattened items
    if (!$mysqli->query("ALTER TABLE orders_archive ADD COLUMN archived_items LONGTEXT")) {
        echo json_encode(['success' => false, 'message' => 'Failed to add columns to archive table']);
        exit;
    }
} else {
    // Ensure column exists (idempotency)
    $mysqli->query("ALTER TABLE orders_archive ADD COLUMN IF NOT EXISTS archived_items LONGTEXT");
}

$successCount = 0;
$errors = [];

foreach ($orderIds as $id) {
    // 2. Fetch Order Data
    $orderRes = $mysqli->query("SELECT * FROM orders WHERE id = $id");
    if ($orderRes->num_rows == 0) continue;
    $orderData = $orderRes->fetch_assoc();

    // 3. Fetch Items
    // Fetch Order Items
    $itemsRes = $mysqli->query("SELECT * FROM order_items WHERE order_id = $id");
    $items = $itemsRes->fetch_all(MYSQLI_ASSOC);

    // Fetch Product Items (Sub-items)
    // We fetch all product items for this order to be safe
    $productsRes = $mysqli->query("SELECT * FROM product_items WHERE order_id = $id");
    $products = $productsRes->fetch_all(MYSQLI_ASSOC);

    // Build Hierarchy
    // Map products by 'indx' (which corresponds to order_items.id)
    $productsMap = [];
    foreach ($products as $p) {
        $productsMap[$p['indx']][] = $p;
    }

    $finalItems = [];
    foreach ($items as $item) {
        $item['items'] = $productsMap[$item['id']] ?? [];
        $finalItems[] = $item;
    }

    $itemsJson = $mysqli->real_escape_string(json_encode($finalItems, JSON_UNESCAPED_UNICODE));

    // 4. Insert into Archive
    // Construct columns and values dynamically
    $cols = [];
    $vals = [];
    foreach ($orderData as $key => $val) {
        $cols[] = "`$key`";
        if ($val === null) {
            $vals[] = "NULL";
        } else {
            $vals[] = "'" . $mysqli->real_escape_string($val) . "'";
        }
    }

    // Add archived_items
    $cols[] = "`archived_items`";
    $vals[] = "'$itemsJson'";

    $sqlInsert = "INSERT INTO orders_archive (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";

    if ($mysqli->query($sqlInsert)) {
        // 5. Delete from Orders (Cascades to items usually, but we rely on simple delete)
        $mysqli->query("DELETE FROM orders WHERE id = $id");
        $successCount++;
    } else {
        $errors[] = "Order $id: " . $mysqli->error;
    }
}

// 6. Check if Orders table is empty
$countRes = $mysqli->query("SELECT COUNT(*) as count FROM orders");
$countData = $countRes->fetch_assoc();
$isEmpty = ($countData['count'] == 0);

// Trigger SSE update
triggerOrderUpdate([], $mysqli);

echo json_encode([
    'success' => true,
    'archived_count' => $successCount,
    'is_empty' => $isEmpty,
    'errors' => $errors
]);

$mysqli->close();
?>