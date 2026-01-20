<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}

require_once $configPath;

// --- FILTER LOGIC (Reused/Simplified) ---
$where = ["1=1"];

// Search
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $s = $mysqli->real_escape_string(trim($_GET['search']));
    $searchId = preg_replace('/[^0-9]/', '', $s);
    $searchCondition = "(name LIKE '%$s%' OR phone LIKE '%$s%'";
    if (!empty($searchId)) {
        $searchCondition .= " OR id = '$searchId'";
    }
    $searchCondition .= ")";
    $where[] = $searchCondition;
}

$whereClause = implode(' AND ', $where);

$sql = "SELECT * FROM orders_archive WHERE $whereClause ORDER BY id DESC";
$result = $mysqli->query($sql);

if (!$result) {
    // Table might not exist yet if no archives
    echo json_encode(['success' => true, 'message' => 'No archives found', 'data' => []]);
    exit;
}

$orders = [];
while ($row = $result->fetch_assoc()) {
    $itemsRaw = json_decode($row['archived_items'] ?? '[]', true);

    // Map items to frontend structure
    $items = [];
    foreach ($itemsRaw as $item) {
        $subItems = [];
        if (isset($item['items']) && is_array($item['items'])) {
            foreach ($item['items'] as $sub) {
                $subItems[] = [
                    'color' => $sub['color'] ?? '',
                    'color_name' => $sub['color_name'] ?? '',
                    'size' => $sub['size'] ?? '',
                    'qty' => $sub['qty'] ?? 0,
                    'total' => $sub['total'] ?? 0,
                    'promo' => $sub['promo'] ?? 0,
                    'id' => $sub['ids'] ?? 0, // 'ids' column in DB
                    'indx' => $sub['indx'] ?? 0,
                ];
            }
        }

        $items[] = [
            'id' => $item['product_id'] ?? 0, // DB uses product_id
            'productName' => $item['product_name'] ?? '',
            'image' => $item['image'] ?? '',
            'price' => $item['price'] ?? 0,
            'qty' => $item['qty'] ?? 0,
            'ref' => $item['ref'] ?? '',
            'items' => $subItems
        ];
    }

    $orders[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'phone' => $row['phone'],
        'totalQty' => $row['total_qty'],
        'country' => $row['country'],
        'method' => $row['method'],
        'deliveryZone' => $row['delivery_zone'],
        'deliveryValue' => $row['delivery_value'],
        'type' => $row['type'],
        'items' => $items,
        'sZone' => $row['s_zone'],
        'mZone' => $row['m_zone'],
        'discount' => $row['discount_code'],
        'note' => $row['note'],
        'total' => $row['total_price'],
        'status' => $row['status'],
        'ip' => $row['ip_adresse'] ?? '',
        'tracking' => $row['tracking_code'] ?? '',
        'create' => $row['created_at'],
        // Archives are static, so we might not need all real-time flags
    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data' => $orders,
]);

$mysqli->close();
?>