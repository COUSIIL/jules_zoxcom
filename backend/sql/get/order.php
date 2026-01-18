<?php
header('Content-Type: application/json');

// Inclure le fichier de configuration de la base de données
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}

require_once $configPath;

// --- MIGRATIONS (Keeping them as in original file just in case) ---
$alters = [
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
    if (! $mysqli->query($sql)) {
        if ($mysqli->errno === 1060) {
            continue;
        }
        // Log error but try to continue
    }
}

// --- FILTER LOGIC ---

$where = ["1=1"];

// Search (ID, Phone, Name)
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $s = $mysqli->real_escape_string(trim($_GET['search']));
    // Check if it looks like an order ID (e.g. order-123 or just 123)
    $searchId = preg_replace('/[^0-9]/', '', $s);

    $searchCondition = "(name LIKE '%$s%' OR phone LIKE '%$s%'";
    if (!empty($searchId)) {
        $searchCondition .= " OR id = '$searchId'";
    }
    $searchCondition .= ")";

    $where[] = $searchCondition;
}

// Status
if (isset($_GET['status']) && !empty($_GET['status']) && $_GET['status'] !== 'all') {
    $s = $mysqli->real_escape_string($_GET['status']);
    $where[] = "status = '$s'";
}

// Date Range
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $d = $mysqli->real_escape_string($_GET['start_date']);
    $where[] = "created_at >= '$d 00:00:00'";
}
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $d = $mysqli->real_escape_string($_GET['end_date']);
    $where[] = "created_at <= '$d 23:59:59'";
}

// Wilaya (Delivery Zone)
if (isset($_GET['wilaya']) && !empty($_GET['wilaya'])) {
    $w = $mysqli->real_escape_string($_GET['wilaya']);
    $where[] = "delivery_zone LIKE '%$w%'";
}

// Commune (S Zone)
if (isset($_GET['commune']) && !empty($_GET['commune'])) {
    $c = $mysqli->real_escape_string($_GET['commune']);
    $where[] = "s_zone LIKE '%$c%'";
}

// Society (Method)
if (isset($_GET['method']) && !empty($_GET['method'])) {
    $m = $mysqli->real_escape_string($_GET['method']);
    $where[] = "method LIKE '%$m%'";
}

// Price Range (Total Price)
if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $p = (float)$_GET['min_price'];
    $where[] = "total_price >= $p";
}
if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
    $p = (float)$_GET['max_price'];
    $where[] = "total_price <= $p";
}


$whereClause = implode(' AND ', $where);

// 1. Fetch Orders
$sqlOrders = "SELECT * FROM orders WHERE $whereClause ORDER BY id DESC"; // Limit?
// If no search/filters, maybe limit to recent? Original fetched all.
// "optimize" implies pagination or limit, but for now let's just use filters.
// If user says "search optimize", likely server-side filtering is key.

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

// 2. Fetch Order Items for these orders
$idsString = implode(',', array_map('intval', $orderIds));
$sqlItems = "SELECT * FROM order_items WHERE order_id IN ($idsString)";
$resultItems = $mysqli->query($sqlItems);
$itemsData = $resultItems->fetch_all(MYSQLI_ASSOC);

$orderItemIds = array_column($itemsData, 'id');

// 3. Fetch Product Items for these order items
$productItemsData = [];
if (!empty($orderItemIds)) {
    $itemIdsString = implode(',', array_map('intval', $orderItemIds));
    $sqlProducts = "SELECT * FROM product_items WHERE indx IN ($itemIdsString)";
    $resultProducts = $mysqli->query($sqlProducts);
    $productItemsData = $resultProducts->fetch_all(MYSQLI_ASSOC);
}

// --- DATA ASSEMBLY (Same logic as before but with filtered data) ---

// Organiser product_items par indx (order_items.id)
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

// Organiser order_items
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

// Construction de la réponse finale
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
    ];
}

// Envoi de la réponse JSON
echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data' => $finalOrders,
]);

$mysqli->close();
?>
