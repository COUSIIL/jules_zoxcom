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
    'delivery' => "SELECT * FROM delivery_methods",
    'options' => "SELECT * FROM delivery_options",
    'price' => "SELECT * FROM delivery_prices",
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


// Organisation des données pour éviter les boucles imbriquées
$groupedModels = [];

foreach ($data['price'] as $model) {
    $groupedModels[$model['method_id']][] = [
        'name' => $model['city_name'],
        'home_price' => $model['home_price'],
        'desk_price' => $model['desk_price'],
        'isActive' => $model['isActive'],
        
    ];
}



$groupedCatalogs = [];
foreach ($data['options'] as $catalog) {
    $id = $catalog['id'];
    $groupedCatalogs[$catalog['delivery_id']][] = [
        'id' => $id,
        'name' => $catalog['method_name'],
        'price' => $groupedModels[$id] ?? []];
}




// Construction de la réponse finale
$delivery = [];
foreach ($data['delivery'] as $deliveryData) {
    $deliveryId = $deliveryData['id'];
    $delivery[] = [
        'name' => $deliveryData['country_name'],
        'city_name' => $deliveryData['admin_city'],
        'hall_name' => $deliveryData['admin_hall'],
        'adress_name' => $deliveryData['admin_adress'],
        'options' => $groupedCatalogs[$deliveryId] ?? [],

    ];
}

// Envoi de la réponse JSON
echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data' => $delivery,
]);

// Fermeture de la connexion
$mysqli->close();
?>
