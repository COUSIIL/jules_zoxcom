<?php

header("Content-Type: application/json; charset=UTF-8");

$configPath  = __DIR__ . '/../../../backend/config/dbConfig.php';
$configPath2 = __DIR__ . '/../update/products/confirmOrder.php';

if (!file_exists($configPath) || !file_exists($configPath2)) {
    echo json_encode([
        'success' => false,
        'message' => 'Required config not found.'
    ]);
    exit;
}

require_once $configPath;
require_once $configPath2;
require_once __DIR__ . '/products/manageStockCodes.php';

/* =======================
   Ensure columns exist
======================= */

$checks = [
    "tracking_code"    => "ALTER TABLE orders ADD COLUMN tracking_code VARCHAR(45) NOT NULL DEFAULT '' AFTER ip_adresse",
    "reminder_id"      => "ALTER TABLE orders ADD COLUMN reminder_id INT NULL AFTER tracking_code",
    "corrector_price"  => "ALTER TABLE orders ADD COLUMN corrector_price INT NULL AFTER reminder_id",
    "delegated"        => "ALTER TABLE orders ADD COLUMN delegated INT NULL AFTER corrector_price",
    "owner"            => "ALTER TABLE orders ADD COLUMN owner VARCHAR(45) NULL AFTER delegated",
    "owner_conf_date"  => "ALTER TABLE orders ADD COLUMN owner_conf_date TIMESTAMP NULL AFTER owner",
    "owner_conf_state" => "ALTER TABLE orders ADD COLUMN owner_conf_state VARCHAR(45) NULL AFTER owner_conf_date",
    "owner_state"      => "ALTER TABLE orders ADD COLUMN owner_state JSON NULL AFTER owner_conf_state"
];

foreach ($checks as $col => $sql) {
    $r = $mysqli->query("SHOW COLUMNS FROM orders LIKE '$col'");
    if ($r && $r->num_rows === 0) {
        $mysqli->query($sql);
    }
}

/* =======================
   Input
======================= */

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['status'], $data['value'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing parameters.'
    ]);
    exit;
}

$id     = (int)$data['id'];
$status = $data['status'];
$value  = $data['value'];

$owner = $data['owner'] ?? null;
$products = $data['products'] ?? null;

/* =======================
   Allowed fields
======================= */

$allowed_fields = [
    'status',
    'note',
    'tracking_code',
    'reminder_id',
    'delegated',
    'owner',
    'owner_conf_date',
    'owner_conf_state'
];

if (!in_array($status, $allowed_fields, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid field name.'
    ]);
    exit;
}

/* =======================
   Check order
======================= */

$check = $mysqli->prepare("SELECT id, status, phone FROM orders WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$res = $check->get_result();
$currentOrder = $res->fetch_assoc();
$check->close();

if (!$currentOrder) {
    echo json_encode([
        'success' => false,
        'message' => 'Order not found.'
    ]);
    exit;
}

/* =======================
   Determine Actor (User or Bot)
======================= */

$actorName = 'Bot';
$actorImage = ''; // Frontend should handle empty image as default bot or we provide a URL
$actorType = 'bot';

if (!empty($owner)) {
    // It's a user action
    $actorType = 'user';
    $actorName = $owner;
    // Fetch user details
    $uStmt = $mysqli->prepare("SELECT profile_image FROM users WHERE username = ?");
    if ($uStmt) {
        $uStmt->bind_param("s", $owner);
        $uStmt->execute();
        $resUser = $uStmt->get_result();
        if ($uRow = $resUser->fetch_assoc()) {
            $actorImage = $uRow['profile_image'] ?? '';
        }
        $uStmt->close();
    }
}

// Construct owner_state JSON
$ownerStateData = [
    'name' => $actorName,
    'image' => $actorImage,
    'type' => $actorType,
    'action' => $status,
    'value' => $value,
    'date' => date('Y-m-d H:i:s')
];
$ownerStateJson = json_encode($ownerStateData);


/* =======================
   Update main field
======================= */

if ($status === 'owner_conf_date') {
    $sql = "UPDATE orders SET owner_conf_date = CURRENT_TIMESTAMP, owner_state = ? WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("si", $ownerStateJson, $id);
} else {
    $sql = "UPDATE orders SET `$status` = ?, owner_state = ? WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssi", $value, $ownerStateJson, $id);
}

if (!$stmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => $stmt->error
    ]);
    exit;
}

$stmt->close();

/* =======================
   History logic
======================= */
// Always insert history, even for Bot
$historyUser = !empty($owner) ? $owner : 'Bot';

$historyValue = is_array($value) ? json_encode($value) : $value;
// Shorten value if too long
if (strlen($historyValue) > 65000) {
    $historyValue = substr($historyValue, 0, 65000) . '...';
}

$stmtH = $mysqli->prepare("INSERT INTO order_history (order_id, user, action, value) VALUES (?, ?, ?, ?)");
if ($stmtH) {
    $stmtH->bind_param("isss", $id, $historyUser, $status, $historyValue);
    $stmtH->execute();
    $stmtH->close();
}

/* =======================
   Confirmed logic
======================= */

$assignedCodes = [];

if ($value === 'confirmed') {

    $sql2 = "
        UPDATE orders
        SET owner = ?,
            owner_conf_state = 'confirmed',
            owner_conf_date = CURRENT_TIMESTAMP
        WHERE id = ?
    ";

    $stmt2 = $mysqli->prepare($sql2);
    $stmt2->bind_param("si", $owner, $id);
    $stmt2->execute();
    $stmt2->close();

    // Assign unique codes to this order (and consume stock)
    releaseUniqueCodes($mysqli, $id); // Safety: Release any previously assigned codes (and restore stock) to avoid duplicates/double-counting

    // Unified function to Assign Codes + Decrement Stock Counters
    $stockResult = assignAndDecrementStock($mysqli, $id);
    if (!$stockResult['success']) {
        // Rollback confirmation if stock failed?
        // Since we are not in a global transaction here (mysqli->begin_transaction was not called at top level),
        // we might leave the order as 'confirmed' but without stock.
        // Ideally we should revert the status update.
        $mysqli->query("UPDATE orders SET owner_conf_state = NULL WHERE id = $id");
        echo json_encode($stockResult);
        exit;
    }

    // Always refetch assigned codes from DB to ensure response is accurate
    $assignedCodes = [];
    $stmtCodes = $mysqli->prepare("SELECT unique_code, model_id, detail_id, status FROM product_stock WHERE order_id = ?");
    $stmtCodes->bind_param("i", $id);
    $stmtCodes->execute();
    $resCodes = $stmtCodes->get_result();
    while ($row = $resCodes->fetch_assoc()) {
        $assignedCodes[] = $row;
    }
    $stmtCodes->close();
}

/* =======================
   Returned/Canceled logic (Stock Release)
======================= */

// Statuses that hold stock
$stockHoldingStatuses = ['confirmed', 'shipping', 'completed'];

// If changing status AND new status is NOT in holding list, release stock.
if ($status === 'status' && !in_array($value, $stockHoldingStatuses)) {
    releaseUniqueCodes($mysqli, $id);
}

/* =======================
   Completed logic (Power)
======================= */

if ($status === 'status' && $value === 'completed' && $currentOrder['status'] !== 'completed') {
    $phone = $currentOrder['phone'];

    // Increment power for customer with this phone
    $stmtPow = $mysqli->prepare("UPDATE customers SET power = power + 1 WHERE phone = ?");
    $stmtPow->bind_param("s", $phone);
    $stmtPow->execute();
    $stmtPow->close();
}

/* =======================
   Response
======================= */

// Trigger global update for SSE
include_once __DIR__ . '/../../trigger_update.php';
triggerOrderUpdate(['id' => $id, 'action' => 'update', 'field' => $status], $mysqli);

// If assignedCodes is empty (either not confirmed just now, or used fallback), retrieve them from DB
if (empty($assignedCodes) && ($status === 'status' || $value === 'confirmed')) {
    // Fetch assigned codes for this order
    $stmtCodes = $mysqli->prepare("SELECT unique_code, model_id, detail_id, status FROM product_stock WHERE order_id = ?");
    $stmtCodes->bind_param("i", $id);
    $stmtCodes->execute();
    $resCodes = $stmtCodes->get_result();
    while ($row = $resCodes->fetch_assoc()) {
        $assignedCodes[] = $row;
    }
    $stmtCodes->close();
}

echo json_encode([
    'success' => true,
    'message' => 'Order updated successfully.',
    'assigned_codes' => $assignedCodes
]);

$mysqli->close();
exit;
