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
    'products' => "SELECT * FROM products",
    'models' => "SELECT * FROM product_models",
    'catalogs' => "SELECT * FROM product_catalogs",
    'descriptions' => "SELECT * FROM product_descriptions",
    'details' => "SELECT * FROM model_details"
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

$groupedDetails = [];
foreach ($data['details'] as $detail) {
    $groupedDetails[$detail['model_id']][] = [
        'id' => $detail['id'],
        'color' => $detail['color'],
        'size' => $detail['size'],
        'qty' => $detail['quantity'],
        'colorName' => $detail['color_name'],
     ];
}

// Organisation des données pour éviter les boucles imbriquées
$groupedModels = [];
foreach ($data['models'] as $model) {
    $model_id = $model['id'];
    $groupedModels[$model['product_id']][] = [
        'id' => $model_id,
        'modelName' => $model['name'],
        'ref' => $model['ref'],
        'buy' => $model['buy_price'],
        'sell' => $model['sell_price'],
        'qty' => $model['quantity'],
        'aColor' => $model['active_color'],
        'aSize' => $model['active_size'],
        'image' => $model['image_url'],
        'sku' => $model['sku'],
        'modelActive' => $model['isActive'],
        'infinit_stock' => $model['infinit_stock'],
        'details' => $groupedDetails[$model_id] ?? [],
        'breakable' => $model['breakable'],
        'weight' => $model['weight'],
        'volume' => $model['volume'],
        'promo' => $model['promo']
    ];
}

$groupedCatalogs = [];
foreach ($data['catalogs'] as $catalog) {
    $groupedCatalogs[$catalog['product_id']][] = ['image' => $catalog['image_url']];
}



$groupedDescriptions = [];
foreach ($data['descriptions'] as $description) {
    $groupedDescriptions[$description['product_id']][] = ['image' => $description['image_url']];
}

// Construction de la réponse finale
$product = [];
foreach ($data['products'] as $productData) {
    $productId = $productData['id'];
    $product[] = [
        'id' => $productId,
        'name' => $productData['name'],
        'youtubeUrl' => $productData['youtube_link'],
        'image' => $productData['image'],
        'label' => $productData['label'],
        'category' => $productData['category'],
        'models' => $groupedModels[$productId] ?? [],
        'catalog' => $groupedCatalogs[$productId] ?? [],
        'descriptionImage' => $groupedDescriptions[$productId] ?? [],
        'aDescription' => $productData['is_description'],
        'description' => $productData['description'],
        'creation' => $productData['created_at'],
        'prodActive' => $productData['isActive'],
        'slug' => $productData['slug'],
        'colors' => $productData['colors'],
        'sizes' => $productData['sizes'],
        'ytb_active' => $productData['ytb_active'],
        'cata_active' => $productData['cata_active'],
    ];
}

// Envoi de la réponse JSON
echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data' => $product,
]);

// Fermeture de la connexion
$mysqli->close();
?>
