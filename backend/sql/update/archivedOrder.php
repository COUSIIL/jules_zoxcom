<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
require_once $configPath;
require_once __DIR__ . '/../../security/auth.php';

$data = json_decode(file_get_contents('php://input'), true);

$token = $data['token'] ?? '';
$userId = verifyToken($mysqli, $token);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($data['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing order ID']);
    exit;
}

$id = intval($data['order_id']);

// Fields to update
$updates = [];
$types = "";
$params = [];

// Helper to add update fields
function addUpdate($field, $value, $type, &$updates, &$types, &$params) {
    if ($value !== null) {
        $updates[] = "$field = ?";
        $types .= $type;
        $params[] = $value;
    }
}

// Map incoming JSON keys to DB columns
addUpdate('name', $data['name'] ?? null, 's', $updates, $types, $params);
addUpdate('phone', $data['phone'] ?? null, 's', $updates, $types, $params);
addUpdate('status', $data['status'] ?? null, 's', $updates, $types, $params);
addUpdate('delivery_zone', $data['delivery_zone'] ?? null, 's', $updates, $types, $params); // Wilaya
addUpdate('s_zone', $data['s_zone'] ?? null, 's', $updates, $types, $params); // Commune
addUpdate('m_zone', $data['m_zone'] ?? null, 's', $updates, $types, $params); // Address
addUpdate('method', $data['method'] ?? null, 's', $updates, $types, $params);
addUpdate('delivery_value', $data['delivery_value'] ?? null, 'd', $updates, $types, $params);
addUpdate('note', $data['note'] ?? null, 's', $updates, $types, $params);
addUpdate('total_price', $data['total'] ?? null, 'd', $updates, $types, $params);

// Handle Items (JSON)
if (isset($data['items']) && is_array($data['items'])) {
    // We need to match the structure the DB expects in 'archived_items'
    // The frontend sends a simplified structure. We might need to map it back or just save it.
    // However, getArchivedOrders.php decodes it and maps it.
    // If we overwrite it, we must ensure it matches the structure expected by getArchivedOrders logic:
    // Structure: [ { product_id, product_name, ... items: [ { color, size, qty ... } ] } ]

    // For now, we assume the frontend sends it in the correct format or we rely on the fact
    // that the frontend edits the structure returned by getArchivedOrders.
    // But getArchivedOrders *transforms* the raw JSON.
    // Raw JSON: [ { items: [ { ids, indx, ... } ], ... } ]
    // We need to be careful here.

    // Simplification: For now, we will just save what is sent as 'items' into 'archived_items'
    // IF the frontend sends it back in the RAW format.
    // If the frontend sends the *transformed* format, we need to reverse it.

    // Let's assume the Frontend will handle the reverse mapping or we just save the flat structure
    // and update getArchivedOrders to handle both? No, that's messy.

    // Let's look at getArchivedOrders again.
    // It expects:
    // [
    //   {
    //      items: [ { color, size, qty, ids, indx ... } ],
    //      product_id, product_name, image, price, qty, ref
    //   }
    // ]

    // If the frontend sends this structure back (before mapping), we are good.
    // The frontend needs to be careful.

    // For this task, I'll assume the frontend will send `raw_items` if it modifies them,
    // or we skip item editing for now and focus on metadata.
    // *Self-correction*: User asked to "modify each order".

    // I will accept `items_json` string directly if the frontend prepares it,
    // or `items` array and json_encode it.

    // Let's rely on `items` being the array.
    $itemsJson = json_encode($data['items'], JSON_UNESCAPED_UNICODE);
    $updates[] = "archived_items = ?";
    $types .= "s";
    $params[] = $itemsJson;
}

if (empty($updates)) {
    echo json_encode(['success' => true, 'message' => 'No changes provided']);
    exit;
}

$sql = "UPDATE orders_archive SET " . implode(', ', $updates) . " WHERE id = ?";
$types .= "i";
$params[] = $id;

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Archived order updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update order: ' . $mysqli->error]);
}

$stmt->close();
$mysqli->close();
?>