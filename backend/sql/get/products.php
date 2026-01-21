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

$alters = [
  "ALTER TABLE model_details     ADD COLUMN IF NOT EXISTS catalog_index TINYINT(1) NULL AFTER quantity",
  "ALTER TABLE model_details     ADD COLUMN IF NOT EXISTS catalog_image VARCHAR(255) NOT NULL AFTER catalog_index"
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
            'message' => "Failed to fetch data from",
        ]);
        $mysqli->close();
        exit;
    }
}

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
    $groupedDetails[$detail['model_id'] ?? 0][] = [
        'id'            => $detail['id'] ?? null,
        'color'         => $detail['color'] ?? null,
        'size'          => $detail['size'] ?? null,
        'qty'           => $detail['quantity'] ?? null,
        'buy'           => $detail['buy_price'] ?? null,
        'sell'          => $detail['sell_price'] ?? null,
        'colorName'     => $detail['color_name'] ?? null,
        'catalog_index' => $detail['catalog_index'] ?? null,
        'catalog_image' => $detail['catalog_image'] ?? null,
    ];
}

// Organisation des données pour éviter les boucles imbriquées
$groupedModels = [];
foreach ($data['models'] as $model) {
    $model_id = $model['id'] ?? 0;
    $groupedModels[$model['product_id'] ?? 0][] = [
        'id'            => $model_id,
        'name'          => $model['name'] ?? null,
        'ref'           => $model['ref'] ?? null,
        'buy'           => $model['buy_price'] ?? null,
        'sell'          => $model['sell_price'] ?? null,
        'qty'           => $model['quantity'] ?? null,
        'activeColor'   => $model['active_color'] ?? null,
        'activeSize'    => $model['active_size'] ?? null,
        'imageUrls'     => $model['image_url'] ?? null,
        'sku'           => $model['sku'] ?? null,
        'isActive'      => $model['isActive'] ?? null,
        'infinit_stock' => $model['infinit_stock'] ?? null,
        'details'       => $groupedDetails[$model_id] ?? [],
        'breakable'     => $model['breakable'] ?? null,
        'weight'        => $model['weight'] ?? null,
        'volume'        => $model['volume'] ?? null,
        'promo'         => $model['promo'] ?? null,
    ];
}

$groupedCatalogs = [];
foreach ($data['catalogs'] as $catalog) {
    $groupedCatalogs[$catalog['product_id'] ?? 0][] = [
        'image' => $catalog['image_url'] ?? null
    ];
}

$groupedDescriptions = [];
foreach ($data['descriptions'] as $description) {
    $groupedDescriptions[$description['product_id'] ?? 0][] = [
        'image' => $description['image_url'] ?? null
    ];
}

// Construction de la réponse finale
$product = [];
foreach ($data['products'] as $productData) {
    $productId = $productData['id'] ?? 0;
    $product[] = [
        'id'              => $productId,
        'name'            => $productData['name'] ?? null,
        'youtubeUrl'      => $productData['youtube_link'] ?? null,
        'image'           => $productData['image'] ?? null,
        'label'           => $productData['label'] ?? null,
        'category'        => $productData['category'] ?? null,
        'models'          => $groupedModels[$productId] ?? [],
        'catalog'         => $groupedCatalogs[$productId] ?? [],
        'descriptionImage'=> $groupedDescriptions[$productId] ?? [],
        'aDescription'    => $productData['is_description'] ?? null,
        'description'     => $productData['description'] ?? null,
        'creation'        => $productData['created_at'] ?? null,
        'prodActive'      => $productData['isActive'] ?? null,
        'slug'            => $productData['slug'] ?? null,
        'colors'          => $productData['colors'] ?? null,
        'sizes'           => $productData['sizes'] ?? null,
        'ytbActive'       => $productData['ytb_active'] ?? null,
        'cataActive'      => $productData['cata_active'] ?? null,

        // Missing fields mapping
        'factoryName'      => $productData['factory_name'] ?? null,
        'productionTime'   => $productData['production_time'] ?? null,
        'targetAudience'   => $productData['target_audience'] ?? null,
        'genre'            => $productData['genre'] ?? null,
        'internalInfo'     => $productData['internal_info'] ?? null,
        'ratingActive'     => $productData['rating_active'] ?? null,
        'liveChatActive'   => $productData['live_chat_active'] ?? null,
        'countdownActive'  => $productData['countdown_active'] ?? null,
        'gamblingActive'   => $productData['gambling_active'] ?? null,
        'aiHelperActive'   => $productData['ai_helper_active'] ?? null,
        'aspaActive'       => $productData['aspa_active'] ?? null,
        'salesCanalActive' => $productData['sales_canal_active'] ?? null,
    ];
}

// Envoi de la réponse JSON
echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data'    => $product,
]);

// Fermeture de la connexion
$mysqli->close();
?>