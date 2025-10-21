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

$data = json_decode(file_get_contents('php://input'), true);

$order_id = intval($data['order_id'] ?? 1);

require_once $configPath;



$alters = [
  "ALTER TABLE orders ADD COLUMN IF NOT EXISTS ip_adresse VARCHAR(45) NULL AFTER status",
  "ALTER TABLE product_items ADD COLUMN IF NOT EXISTS total DECIMAL(10,2) NOT NULL AFTER qty",
  "ALTER TABLE product_items ADD COLUMN IF NOT EXISTS promo DECIMAL(10,2) NOT NULL AFTER total",
  "ALTER TABLE product_items ADD COLUMN IF NOT EXISTS color_name VARCHAR(255) NULL AFTER color",
  "ALTER TABLE product_items ADD COLUMN IF NOT EXISTS indx INT(11) AFTER ids"
];

foreach ($alters as $sql) {
    if (! $mysqli->query($sql)) {
        // Si ta version de MySQL ne supporte pas IF NOT EXISTS, ou si 
        // la colonne existe déjà, tu peux vérifier le code d’erreur 1060
        if ($mysqli->errno === 1060) {
            continue;
        }
        echo json_encode([
            'success' => false,
            'message' => "Migration failed: " . $mysqli->error
        ]);
        $mysqli->close();
        exit;
    }
}

// Requêtes pour obtenir les données des tables
$tables = [
    'orders' => "SELECT * FROM orders WHERE id = $order_id",
    'item' => "SELECT * FROM order_items WHERE order_id = $order_id",
    'product' => "SELECT * FROM product_items WHERE order_id = $order_id",
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
        'color_name' => $model['color_name'],
        'size' => $model['size'],
        'qty' => $model['qty'],
        'total' => $model['total'],
        'promo' => $model['promo'],
        'id' => $model['ids'],
        'indx' => $model['indx']
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
        'ip' => $orderData['ip_adresse'] ?? '',
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
