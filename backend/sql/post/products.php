<?php


try {

header("Content-Type: application/json; charset=UTF-8");

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



// Création des tables si elles n'existent pas
$createTables = [
    "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        image TEXT NOT NULL,
        label VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        is_description BOOLEAN DEFAULT 0,
        description TEXT,
        youtube_link TEXT,
        isActive TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS product_models (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        ref VARCHAR(255) NOT NULL,
        buy_price DECIMAL(10, 2) NOT NULL,
        sell_price DECIMAL(10, 2) NOT NULL,
        quantity INT NOT NULL,
        active_color VARCHAR(50),
        active_size VARCHAR(50),
        image_url TEXT,
        sku TEXT,
        infinit_stock TEXT,
        isActive TEXT,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )",

    "CREATE TABLE IF NOT EXISTS product_descriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_url TEXT NOT NULL,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS product_catalogs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_url TEXT NOT NULL,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS model_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        model_id INT NOT NULL,
        color TEXT NOT NULL,
        size TEXT NOT NULL,
        quantity INT NOT NULL,
        FOREIGN KEY (model_id) REFERENCES product_models(id) ON DELETE CASCADE
    )"
];

// Exécution des requêtes de création des tables
foreach ($createTables as $query) {
    if (!$mysqli->query($query)) {
        response(false, "Error creating table: " . $mysqli->error, 500);
    }
}

$alters = [
  "ALTER TABLE products           ADD COLUMN IF NOT EXISTS slug VARCHAR(255) NOT NULL AFTER label",
  "ALTER TABLE products           ADD COLUMN IF NOT EXISTS colors JSON NULL               AFTER slug",
  "ALTER TABLE products           ADD COLUMN IF NOT EXISTS sizes  JSON NULL               AFTER colors",
  "ALTER TABLE products           ADD COLUMN IF NOT EXISTS ytb_active TINYINT(1) NOT NULL DEFAULT 0 AFTER youtube_link",
  "ALTER TABLE products           ADD COLUMN IF NOT EXISTS cata_active TINYINT(1) NOT NULL DEFAULT 0 AFTER ytb_active",
  "ALTER TABLE product_models     ADD COLUMN IF NOT EXISTS breakable TINYINT(1) NOT NULL DEFAULT 0 AFTER isActive",
  "ALTER TABLE product_models     ADD COLUMN IF NOT EXISTS weight    DECIMAL(10,2) NOT NULL DEFAULT 1.00 AFTER breakable",
  "ALTER TABLE product_models     ADD COLUMN IF NOT EXISTS volume    DECIMAL(10,2) NOT NULL DEFAULT 1.00 AFTER weight",
  "ALTER TABLE product_models     ADD COLUMN IF NOT EXISTS promo    DECIMAL(10,2) NULL AFTER sell_price",
  "ALTER TABLE model_details     ADD COLUMN IF NOT EXISTS color_name VARCHAR(255) NOT NULL AFTER color",
  "ALTER TABLE model_details     ADD COLUMN IF NOT EXISTS catalog_index TINYINT(1) NULL AFTER quantity",
  "ALTER TABLE model_details     ADD COLUMN IF NOT EXISTS catalog_image VARCHAR(255) NOT NULL AFTER catalog_index",

  "ALTER TABLE products ADD COLUMN IF NOT EXISTS rating_active TINYINT(1) DEFAULT 0",
  "ALTER TABLE products ADD COLUMN IF NOT EXISTS live_chat_active TINYINT(1) DEFAULT 0",
  "ALTER TABLE products ADD COLUMN IF NOT EXISTS countdown_active TINYINT(1) DEFAULT 0",
  "ALTER TABLE products ADD COLUMN IF NOT EXISTS gambling_active TINYINT(1) DEFAULT 0",
  "ALTER TABLE products ADD COLUMN IF NOT EXISTS ai_helper_active TINYINT(1) DEFAULT 0",
  "ALTER TABLE products ADD COLUMN IF NOT EXISTS aspa_active TINYINT(1) DEFAULT 0",
  "ALTER TABLE products ADD COLUMN IF NOT EXISTS sales_canal_active TINYINT(1) DEFAULT 0",

  "ALTER TABLE products ADD COLUMN IF NOT EXISTS factory_name VARCHAR(255) NULL",
  "ALTER TABLE products ADD COLUMN IF NOT EXISTS production_time VARCHAR(255) NULL",
  "ALTER TABLE products ADD COLUMN IF NOT EXISTS target_audience VARCHAR(255) NULL",
  "ALTER TABLE products ADD COLUMN IF NOT EXISTS genre VARCHAR(255) NULL",
  "ALTER TABLE products ADD COLUMN IF NOT EXISTS internal_info TEXT NULL"
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

// Lecture des données JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    response(false, "Invalid input data.", 400);
}

// Extraction des paramètres
$name = $data['name'] ?? '';
$image = $data['image'] ?? '';
$label = $data['label'] ?? '';
$category = $data['category'] ?? '';
$prodActive = $data['prodActive'] ?? '';
$isDescription = $data['isDescription'] ?? '';
$description = $data['description'] ?? '';
$youtubeUrl = $data['youtubeUrl'] ?? '';
$catalogUrls = $data['catalogUrl'] ?? [];
$models = $data['models'] ?? [];
$descriptionUrls = $data['descriptionUrl'] ?? [];
$slug         = $data['slug']         ?? '';
$colors       = isset($data['colors']) ? json_encode($data['colors'], JSON_UNESCAPED_UNICODE) : null;
$sizes        = isset($data['sizes'])  ? json_encode($data['sizes'],  JSON_UNESCAPED_UNICODE) : null;
$ytbActive    = !empty($data['ytbActive'])   ? 1 : 0;
$cataActive   = !empty($data['cataActive'])  ? 1 : 0;

$ratingActive     = !empty($data['ratingActive'])     ? 1 : 0;
$liveChatActive   = !empty($data['liveChatActive'])   ? 1 : 0;
$countdownActive  = !empty($data['countdownActive'])  ? 1 : 0;
$gamblingActive   = !empty($data['gamblingActive'])   ? 1 : 0;
$aiHelperActive   = !empty($data['aiHelperActive'])   ? 1 : 0;
$aspaActive       = !empty($data['aspaActive'])       ? 1 : 0;
$salesCanalActive = !empty($data['salesCanalActive']) ? 1 : 0;

$factoryName     = $data['factoryName']     ?? '';
$productionTime  = $data['productionTime']  ?? '';
$targetAudience  = $data['targetAudience']  ?? '';
$genre           = $data['genre']           ?? '';
$internalInfo    = $data['internalInfo']    ?? '';

$id = isset($data['id']) ? intval($data['id']) : -1;


//response(false, $data['name']);


// Validation de l'image
if ($image && !in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), ['webp', 'png', 'jpg', 'jpeg', 'avif'])) {
    response(false, "Invalid image format. Allowed formats: webp, png, jpg, jpeg.");
}

// Vérification si le produit existe
$check_query = $mysqli->prepare("SELECT id FROM products WHERE id = ?");
$check_query->bind_param("i", $id);
$check_query->execute();
$product_result = $check_query->get_result();
$check_query->close();

if ($product_result->num_rows > 0) {
    // Mise à jour du produit existant
    $product_id = $product_result->fetch_assoc()['id'];
    $update_query = $mysqli->prepare(
        "UPDATE products SET
            name = ?, image = ?, youtube_link = ?, label = ?, category = ?, is_description = ?, description = ?, isActive = ?, slug = ?, colors = ?, sizes = ?, ytb_active = ?, cata_active = ?,
            rating_active = ?, live_chat_active = ?, countdown_active = ?, gambling_active = ?, ai_helper_active = ?, aspa_active = ?, sales_canal_active = ?,
            factory_name = ?, production_time = ?, target_audience = ?, genre = ?, internal_info = ?
        WHERE id = ?"
    );
    $update_query->bind_param(
        "sssssssssssiiiiiiiiisssssi",
        $name, $image, $youtubeUrl, $label, $category, $isDescription, $description, $prodActive, $slug, $colors, $sizes, $ytbActive, $cataActive,
        $ratingActive, $liveChatActive, $countdownActive, $gamblingActive, $aiHelperActive, $aspaActive, $salesCanalActive,
        $factoryName, $productionTime, $targetAudience, $genre, $internalInfo,
        $product_id
    );
    executeQuery($update_query, "Error updating product.", "1", $product_id);
    updateModels($models, $product_id, $mysqli);
    
    updateImages("product_descriptions", $descriptionUrls, $product_id, $mysqli);
    updateImages("product_catalogs", $catalogUrls, $product_id, $mysqli);

    response(true, "Product updated with product_id: $product_id");
} else {
    // Insertion d'un nouveau produit
    $insert_query = $mysqli->prepare(
        "INSERT INTO products (
            name, image, youtube_link, label, category, is_description, description, isActive, slug, colors, sizes, ytb_active, cata_active,
            rating_active, live_chat_active, countdown_active, gambling_active, ai_helper_active, aspa_active, sales_canal_active,
            factory_name, production_time, target_audience, genre, internal_info
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insert_query->bind_param(
        "sssssssssssiiiiiiiiisssss",
        $name, $image, $youtubeUrl, $label, $category, $isDescription, $description, $prodActive, $slug, $colors, $sizes, $ytbActive, $cataActive,
        $ratingActive, $liveChatActive, $countdownActive, $gamblingActive, $aiHelperActive, $aspaActive, $salesCanalActive,
        $factoryName, $productionTime, $targetAudience, $genre, $internalInfo
    );
    
    if ($insert_query->execute()) {
        // Récupérer l'ID du produit ajouté
        $product_id = $mysqli->insert_id;
    
        // Vérifier si un ID valide est retourné
        if ($product_id > 0) {
            // L'insertion a réussi et l'ID a été récupéré
            insertModels($models, $product_id, $mysqli);
            insertImages("product_descriptions", $descriptionUrls, $product_id, $mysqli);
            insertImages("product_catalogs", $catalogUrls, $product_id, $mysqli);
    
            response(true, "Product saved with product_id: $product_id");
        } else {
            // Si l'ID est 0, il y a un problème avec la récupération de l'ID
            response(false, "Failed to retrieve product ID.");
        }
    } else {
        // Si l'insertion échoue
        response(false, "Error inserting product: " . $insert_query->error);
    }

    
    }

} catch (Exception $e) {
        // Capture l'erreur et récupère la trace de l'exception
    $error_message = $e->getMessage();
    $error_trace = $e->getTraceAsString();

    // Vous pouvez inclure l'exception complète pour aider au débogage
    response(false, "Error: $error_message\nStack Trace:\n$error_trace");
}



/**
 * Fonctions utilitaires
 */
function response($success, $message, $statusCode = 200, $data = null)
{
    http_response_code($statusCode);
    
    $response = ['success' => $success, 'message' => $message];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response);
    exit();
}


function executeQuery($stmt, $errorMessage, $int, $value)
{
    if (!$stmt->execute()) {
        response(false, $errorMessage . " " . $int . " " . $value);
    }
}


function insertDetails($details, $model_id, $mysqli)
{
    if ($mysqli === null || $mysqli->connect_error) {
        response(false, "Database connection failed.");
        return;
    }

    if (!empty($details)) {
        
        $stmt = $mysqli->prepare(
            "INSERT INTO model_details (model_id, color, color_name, size, quantity, catalog_index, catalog_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        
        foreach ($details as $detail) {
            if (
                isset($detail['color'], $detail['size'], $detail['qty'], $detail['colorName'], $detail['catalog_index'], $detail['catalog_image'])
                      
            ) {
                
                $stmt->bind_param(
                    "isssiis",  // Types attendus : string, string, double, double, integer, string, string, string, string, string, integer
                    $model_id,
                    $detail['color'],
                    $detail['colorName'],
                    $detail['size'],
                    $detail['qty'],
                    $detail['catalog_index'],
                    $detail['catalog_image']
                    
                );
                
                if (!$stmt->execute()) {
                    response(false, "erreur 1");
                    
                }

            } else {

                response(false, "Missing required fields in detail data.");
                return;
            }
        }
        
        $stmt->close();
        


    } else {
        updateDetails($details, $model_id, $mysqli);
    }
}

function updateDetails($details, $model_id, $mysqli)
{
    if (!empty($details)) {

        // 🔥 Supprimer toutes les valeurs existantes avec le même model_id
        $deleteStmt = $mysqli->prepare(
            "DELETE FROM model_details WHERE model_id = ?"
        );
        $deleteStmt->bind_param("i", $model_id);

        if (!$deleteStmt->execute()) {
            response(false, "Erreur lors de la suppression : " . $deleteStmt->error);
            return;
        }
        $deleteStmt->close();

        foreach ($details as $detail) {
            if (isset($detail['color'], $detail['size'], $detail['qty'], $detail['colorName'], $detail['catalog_index'], $detail['catalog_image'])) {
                // 🔄 Insérer les nouvelles valeurs après la suppression
                $stmt = $mysqli->prepare(
                    "INSERT INTO model_details (model_id, color, color_name, size, quantity, catalog_index, catalog_image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "isssiis",
                    $model_id,
                    $detail['color'],
                    $detail['colorName'],
                    $detail['size'],
                    $detail['qty'],
                    $detail['catalog_index'],
                    $detail['catalog_image']
                );

                if (!$stmt->execute()) {
                    response(false, "Erreur lors de l'insertion : " . $stmt->error);
                    return;
                }

                $stmt->close();
            } else {
                response(false, "Données manquantes dans le modèle");
                return;
            }
        }

    }
}


function insertModels($models, $product_id, $mysqli)
{
    if ($mysqli === null || $mysqli->connect_error) {
        response(false, "Database connection failed.");
        return;
    }

    if (!empty($models)) {
        $stmt = $mysqli->prepare(
            "INSERT INTO product_models 
            (product_id, name, ref, buy_price, sell_price, promo, quantity, active_color, active_size, image_url, sku, infinit_stock, isActive, breakable, weight, volume) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            response(false, "Prepare failed: " . $mysqli->error);
            return;
        }

        foreach ($models as $model) {
            // Valeurs par défaut pour éviter les NULL
            $name          = $model['name'] ?? '';
            $ref           = $model['ref'] ?? '';
            $buy           = $model['buy'] ?? 0;
            $sell          = $model['sell'] ?? 0;
            $promo         = $model['promo'] ?? 0;
            $qty           = $model['qty'] ?? 0;
            $activeColor   = $model['activeColor'] ?? '';
            $activeSize    = $model['activeSize'] ?? '';
            $imageUrls     = $model['imageUrls'] ?? '';
            $sku           = $model['sku'] ?? '';
            $infinit_stock = $model['infinit_stock'] ?? 0;
            $isActive      = $model['isActive'] ?? 1;
            $breakable     = $model['breakable'] ?? 0; // ⚡ plus jamais null
            $weight        = $model['weight'] ?? 0;
            $volume        = $model['volume'] ?? 0;

            // Types : i=int, d=double, s=string
            $stmt->bind_param(
                "issdddissssiiidd",
                $product_id,
                $name,
                $ref,
                $buy,
                $sell,
                $promo,
                $qty,
                $activeColor,
                $activeSize,
                $imageUrls,
                $sku,
                $infinit_stock,
                $isActive,
                $breakable,
                $weight,
                $volume
            );

            if (!$stmt->execute()) {
                response(false, "MySQL Error: " . $stmt->error);
            } else {
                $model_id = $mysqli->insert_id;
                insertDetails($model['details'] ?? [], $model_id, $mysqli);
            }
        }

        $stmt->close();
    }
}


function updateModels($models, $product_id, $mysqli)
{
    // Supprimer tous les anciens modèles du produit
    $deleteStmt = $mysqli->prepare("DELETE FROM product_models WHERE product_id = ?");
    $deleteStmt->bind_param("i", $product_id);
    if (!$deleteStmt->execute()) {
        response(false, "Erreur suppression anciens modèles: " . $deleteStmt->error);
    }
    $deleteStmt->close();

    // Réinsérer les modèles
    insertModels($models, $product_id, $mysqli);
}


function insertImages($table, $urls, $product_id, $mysqli)
{
    if (!empty($urls)) {
        $stmt = $mysqli->prepare("INSERT INTO $table (product_id, image_url) VALUES (?, ?)");
        
        if (!$stmt) {
            die("Prepare failed for table $table: " . $mysqli->error);
        }

        // Déclare la variable à lier ici
        $image_url = "";

        // Bind une seule fois
        $stmt->bind_param("is", $product_id, $image_url);

        foreach ($urls as $url) {
            $image_url = $url; // Met à jour la variable liée
            executeQuery($stmt, "Error inserting image into $table", "5", $product_id);
        }

        $stmt->close(); // Toujours fermer après
    }
}


function updateImages($table, $urls, $product_id, $mysqli)
{

    // Supprimer les anciennes images
    $deleteStmt = $mysqli->prepare("DELETE FROM $table WHERE product_id = ?");
    $deleteStmt->bind_param("i", $product_id);
    $deleteStmt->execute();
    $deleteStmt->close();

    // Réinsertion des nouvelles images
    insertImages($table, $urls, $product_id, $mysqli);
}



?>
