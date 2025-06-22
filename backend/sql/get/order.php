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

// Requêtes pour obtenir les données des tables
$tables = [
    'orders' => "SELECT * FROM orders",
    'item' => "SELECT * FROM order_items",
    'product' => "SELECT * FROM product_items",
];

$data = [];
foreach ($tables as $key => $query) {
    $result = $mysqli->query($query);
    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => "Failed to fetch data from $key",
        ]);
        $mysqli->close();
        exit;
    }
    $data[$key] = $result->fetch_all(MYSQLI_ASSOC);
}

$productItems = [];
foreach ($data['product'] as $model) {
    $productItems[$model['order_id']][] = [
        'color' => $model['color'],
        'size' => $model['size'],
        'qty' => $model['qty'],
    ];
}


// Organisation des données pour éviter les boucles imbriquées
$groupedModels = [];
foreach ($data['item'] as $model) {
    $groupedModels[$model['order_id']][] = [
        'productName' => $model['product_name'],
        'image' => $model['image'],
        'price' => $model['price'],
        'qty' => $model['qty'],
        'ref' => $model['ref'],
        'items' => $productItems[$model['order_id']] ?? [],
    ];
}

// Construction de la réponse finale
$orders = [];
foreach ($data['orders'] as $orderData) {
    $orderId = $orderData['id'];
    $orders[] = [
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
        'create' => $orderData['created_at'],
    ];
}

// Envoi de la réponse JSON
echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data' => $orders,
]);

// Fermeture de la connexion
$mysqli->close();
?>
