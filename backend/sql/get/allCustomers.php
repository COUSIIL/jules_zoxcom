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
            'error' => $mysqli->error
        ]);
        $mysqli->close();
        exit;
    }
    $data[$key] = $result->fetch_all(MYSQLI_ASSOC);
}

// Organisation des détails par ID de client
$groupedModels = [];
foreach ($data['details'] as $model) {
    if (!isset($model['customers_id'])) continue;
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

// Construction de la réponse : tous les clients avec leurs détails
$customers = [];
foreach ($data['customers'] as $customerData) {
    $customerId = $customerData['id'];
    $customers[] = [
        'id' => $customerId,
        'name' => $customerData['name'],
        'phone' => $customerData['phone'],
        'power' => $customerData['power'],
        'items' => $groupedModels[$customerId] ?? [],
        
    ];
}

// Envoi de la réponse JSON
echo json_encode([
    'success' => true,
    'message' => 'Customer list with details',
    'data' => $customers,
]);

// Fermeture de la connexion
$mysqli->close();
?>

