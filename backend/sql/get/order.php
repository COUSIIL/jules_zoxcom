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
        // Si ta version de MySQL ne supporte pas IF NOT EXISTS, ou si 
        // la colonne existe déjà, tu peux vérifier le code d’erreur 1060
        if ($mysqli->errno === 1060) {
            continue;
        }
        response(false, "Migration failed: " . $mysqli->error, 500);
    }
}

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

// Organiser product_items par indx (order_items.id)
$productItems = [];
foreach ($data['product'] as $item) {
    if (!isset($item['indx']) || !$item['indx']) continue;

    $productItems[$item['indx']][] = [
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


// Organiser order_items : chaque modèle avec ses variantes uniques
$groupedModels = [];
foreach ($data['item'] as $model) {

    $orderItemId = $model['id']; // id in order_items

    $groupedModels[$model['order_id']][] = [
        'id' => $model['product_id'],
        'productName' => $model['product_name'],
        'image' => $model['image'],
        'price' => $model['price'],
        'qty' => $model['qty'],
        'ref' => $model['ref'],
        'items' => $productItems[$orderItemId] ?? [], // ✅ seulement les variantes liées
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
    'data' => $orders,
]);

// Fermeture de la connexion
$mysqli->close();
?>
