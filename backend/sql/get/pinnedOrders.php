<?php
header('Content-Type: application/json');
include __DIR__ . '/../../../backend/config/dbConfig.php';

// Fetch Only Pinned Orders
$sqlOrders = "SELECT o.*, p.reason as pin_reason, p.created_at as pin_date, 1 as is_pinned
              FROM orders o
              INNER JOIN pinned_orders p ON o.id = p.order_id
              ORDER BY p.created_at DESC";

$resultOrders = $mysqli->query($sqlOrders);
if (!$resultOrders) {
    echo json_encode(['success' => false, 'message' => "DB Error: " . $mysqli->error]);
    exit;
}

$ordersData = $resultOrders->fetch_all(MYSQLI_ASSOC);
$orderIds = array_column($ordersData, 'id');

if (empty($orderIds)) {
    echo json_encode(['success' => true, 'message' => 'No pinned orders found', 'data' => []]);
    exit;
}

// Fetch Items
$idsString = implode(',', array_map('intval', $orderIds));
$sqlItems = "SELECT * FROM order_items WHERE order_id IN ($idsString)";
$resultItems = $mysqli->query($sqlItems);
$itemsData = $resultItems->fetch_all(MYSQLI_ASSOC);
$orderItemIds = array_column($itemsData, 'id');

// Fetch Products
$productItemsData = [];
if (!empty($orderItemIds)) {
    $itemIdsString = implode(',', array_map('intval', $orderItemIds));
    $sqlProducts = "SELECT * FROM product_items WHERE indx IN ($itemIdsString)";
    $resultProducts = $mysqli->query($sqlProducts);
    $productItemsData = $resultProducts->fetch_all(MYSQLI_ASSOC);
}

// Assemble
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
        'is_pinned' => true,
        'pin_reason' => $orderData['pin_reason'] ?? null,
    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Pinned orders received',
    'data' => $finalOrders,
]);

$mysqli->close();
?>