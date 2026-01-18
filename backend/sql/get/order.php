<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}

require_once $configPath;

// --- MIGRATIONS ---
$alters = [
  "CREATE TABLE IF NOT EXISTS pinned_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
  )",
  "ALTER TABLE orders ADD COLUMN IF NOT EXISTS ip_adresse VARCHAR(45) NULL AFTER status",
  "ALTER TABLE orders ADD COLUMN IF NOT EXISTS tracking_code VARCHAR(45) NOT NULL DEFAULT '' AFTER ip_adresse",
  "ALTER TABLE orders ADD COLUMN IF NOT EXISTS reminder_id INT NULL AFTER tracking_code",
  "ALTER TABLE orders ADD COLUMN IF NOT EXISTS owner VARCHAR(45) NULL AFTER reminder_id",
  "ALTER TABLE orders ADD COLUMN IF NOT EXISTS owner_conf_date TIMESTAMP NULL AFTER owner",
  "ALTER TABLE orders ADD COLUMN IF NOT EXISTS owner_conf_state VARCHAR(45) NULL AFTER owner_conf_date",
  "ALTER TABLE product_items ADD COLUMN IF NOT EXISTS total DECIMAL(10,2) NOT NULL AFTER qty",
  "ALTER TABLE product_items ADD COLUMN IF NOT EXISTS promo DECIMAL(10,2) NOT NULL AFTER total",
  "ALTER TABLE product_items ADD COLUMN IF NOT EXISTS color_name VARCHAR(255) NULL AFTER color",
  "ALTER TABLE product_items ADD COLUMN IF NOT EXISTS indx INT(11) AFTER ids"
];

foreach ($alters as $sql) {
    if (!$mysqli->query($sql)) {
        if ($mysqli->errno === 1060) continue;
    }
}

// --- FILTER LOGIC ---
$where = ["1=1"];

// Search
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $s = $mysqli->real_escape_string(trim($_GET['search']));
    $searchId = preg_replace('/[^0-9]/', '', $s);
    $searchCondition = "(o.name LIKE '%$s%' OR o.phone LIKE '%$s%'";
    if (!empty($searchId)) {
        $searchCondition .= " OR o.id = '$searchId'";
    }
    $searchCondition .= ")";
    $where[] = $searchCondition;
}

// Status
if (isset($_GET['status']) && !empty($_GET['status']) && $_GET['status'] !== 'all') {
    $s = $mysqli->real_escape_string($_GET['status']);
    $where[] = "o.status = '$s'";
}

// Date Range
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $d = $mysqli->real_escape_string($_GET['start_date']);
    $where[] = "o.created_at >= '$d 00:00:00'";
}
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $d = $mysqli->real_escape_string($_GET['end_date']);
    $where[] = "o.created_at <= '$d 23:59:59'";
}

// Wilaya
if (isset($_GET['wilaya']) && !empty($_GET['wilaya'])) {
    $w = $mysqli->real_escape_string($_GET['wilaya']);
    $where[] = "o.delivery_zone LIKE '%$w%'";
}

// Commune
if (isset($_GET['commune']) && !empty($_GET['commune'])) {
    $c = $mysqli->real_escape_string($_GET['commune']);
    $where[] = "o.s_zone LIKE '%$c%'";
}

// Method
if (isset($_GET['method']) && !empty($_GET['method'])) {
    $m = $mysqli->real_escape_string($_GET['method']);
    $where[] = "o.method LIKE '%$m%'";
}

// Price
if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $p = (float)$_GET['min_price'];
    $where[] = "o.total_price >= $p";
}
if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
    $p = (float)$_GET['max_price'];
    $where[] = "o.total_price <= $p";
}

$whereClause = implode(' AND ', $where);

// Fetch Orders with Pinned Info
// We join pinned_orders to get reason and sort pinned first
$sqlOrders = "SELECT o.*, p.reason as pin_reason, p.created_at as pin_date, (p.id IS NOT NULL) as is_pinned
              FROM orders o
              LEFT JOIN pinned_orders p ON o.id = p.order_id
              WHERE $whereClause
              ORDER BY is_pinned DESC, o.id DESC";

$resultOrders = $mysqli->query($sqlOrders);
if (!$resultOrders) {
    echo json_encode(['success' => false, 'message' => "DB Error: " . $mysqli->error]);
    exit;
}

$ordersData = $resultOrders->fetch_all(MYSQLI_ASSOC);
$orderIds = array_column($ordersData, 'id');

if (empty($orderIds)) {
    echo json_encode(['success' => true, 'message' => 'No orders found', 'data' => []]);
    exit;
}

// Fetch Order Items
$idsString = implode(',', array_map('intval', $orderIds));
$sqlItems = "SELECT * FROM order_items WHERE order_id IN ($idsString)";
$resultItems = $mysqli->query($sqlItems);
$itemsData = $resultItems->fetch_all(MYSQLI_ASSOC);
$orderItemIds = array_column($itemsData, 'id');

// Fetch Product Items
$productItemsData = [];
if (!empty($orderItemIds)) {
    $itemIdsString = implode(',', array_map('intval', $orderItemIds));
    $sqlProducts = "SELECT * FROM product_items WHERE indx IN ($itemIdsString)";
    $resultProducts = $mysqli->query($sqlProducts);
    $productItemsData = $resultProducts->fetch_all(MYSQLI_ASSOC);
}

// Assemble Data
$productItemsMap = [];
foreach ($productItemsData as $item) {
    if (!isset($item['indx']) || !$item['indx']) continue;
    $productItemsMap[$item['indx']][] = [
        'color' => $item['color'],
        'color_name' => $item['color_name'],
        'size' => $item['size'],
        'qty' => $item['qty'],
        'total' => $item['total'],
        'promo' => $item['promo'],
        'id' => $item['ids'],
        'indx' => $item['indx'],
    ];
}

$groupedModels = [];
foreach ($itemsData as $model) {
    $orderItemId = $model['id'];
    $groupedModels[$model['order_id']][] = [
        'id' => $model['product_id'],
        'productName' => $model['product_name'],
        'image' => $model['image'],
        'price' => $model['price'],
        'qty' => $model['qty'],
        'ref' => $model['ref'],
        'items' => $productItemsMap[$orderItemId] ?? [],
    ];
}

$finalOrders = [];
foreach ($ordersData as $orderData) {
    $orderId = $orderData['id'];
    $finalOrders[] = [
        'id' => $orderId,
        'name' => $orderData['name'],
        'phone' => $orderData['phone'],
        'totalQty' => $orderData['total_qty'],
        'country' => $orderData['country'],
        'method' => $orderData['method'],
        'deliveryZone' => $orderData['delivery_zone'],
        'deliveryValue' => $orderData['delivery_value'],
        'type' => $orderData['type'],
        'items' => $groupedModels[$orderId] ?? [],
        'sZone' => $orderData['s_zone'],
        'mZone' => $orderData['m_zone'],
        'discount' => $orderData['discount_code'],
        'note' => $orderData['note'],
        'total' => $orderData['total_price'],
        'status' => $orderData['status'],
        'ip' => $orderData['ip_adresse'] ?? '',
        'tracking' => $orderData['tracking_code'] ?? '',
        'reminder_id' => $orderData['reminder_id'] ?? '',
        'delegated' => $orderData['delegated'] ?? '',
        'create' => $orderData['created_at'],
        'owner' => $orderData['owner'] ?? '',
        'owner_conf_date' => $orderData['owner_conf_date'] ?? '',
        'owner_conf_state' => $orderData['owner_conf_state'] ?? '',
        // Pinned Info
        'is_pinned' => (bool)$orderData['is_pinned'],
        'pin_reason' => $orderData['pin_reason'] ?? null,
    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data' => $finalOrders,
]);

$mysqli->close();
?>