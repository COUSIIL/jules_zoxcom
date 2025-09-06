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
    'customers' => "SELECT * FROM customers",
    'details' => "SELECT * FROM customers_details",
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

$data2 = json_decode(file_get_contents('php://input'), true);

if (!$data2 || !isset($data2['phone'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing phone number in request.'
    ]);
    $mysqli->close();
    exit;
}


// Organisation des données pour éviter les boucles imbriquées
$groupedModels = [];
foreach ($data['details'] as $model) {
    $groupedModels[$model['customers_id']][] = [
        'id' => $model['id'],
        'country' => $model['country'],
        'method' => $model['method'],
        'delivery_zone' => $model['delivery_zone'],
        'sZone' => $model['sZone'],
        'mZone' => $model['mZone'],
        'created_at' => $model['created_at'],
    ];
}

// Construction de la réponse finale
$customer = [];
foreach ($data['customers'] as $orderData) {
    if($orderData['phone'] === $data2['phone']) {
        $customerId = $orderData['id'];
        $customer[] = [
            'id' => $customerId,
            'name' => $orderData['name'],
            'phone' => $orderData['phone'],
            'items' => $groupedModels[$customerId] ?? [],
        ];
    }

}

// Envoi de la réponse JSON
echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data' => $customer,
]);

// Fermeture de la connexion
$mysqli->close();
?>
