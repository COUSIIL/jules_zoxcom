<?php


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

// Création automatique des tables si elles n'existent pas
$createTables = [
    "CREATE TABLE IF NOT EXISTS delivery_methods (
        id INT AUTO_INCREMENT PRIMARY KEY,
        country_name VARCHAR(255) NOT NULL,
        admin_city VARCHAR(255),
        admin_hall VARCHAR(255),
        admin_adress VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS delivery_options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        delivery_id INT NOT NULL,
        method_name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (delivery_id) REFERENCES delivery_methods(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS delivery_prices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        method_id INT NOT NULL,
        city_name VARCHAR(255) NOT NULL,
        home_price DOUBLE,
        desk_price DOUBLE,
        isActive INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (method_id) REFERENCES delivery_options(id) ON DELETE CASCADE
    )"
];

// Exécution des requêtes de création des tables
foreach ($createTables as $query) {
    if (!$mysqli->query($query)) {
        echo json_encode([
            "success" => false,
            "message" => "Erreur SQL lors de la création de table",
            "data" => $mysqli->error
        ]);
        die;
    }
}

// Lire les données JSON envoyées par Vue.js
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier la présence des champs obligatoires
if (!isset($data['country_name'], $data['administartive_city_name'], $data['administartive_hall_name'], $data['administartive_adresse_name'], $data['methods'])) {
    echo json_encode(['success' => false, 'message' => "Missing required fields."]);
    exit;
}

$country_name = $mysqli->real_escape_string($data['country_name']);
$admin_city = $mysqli->real_escape_string($data['administartive_city_name']);
$admin_hall = $mysqli->real_escape_string($data['administartive_hall_name']);
$admin_adress = $mysqli->real_escape_string($data['administartive_adresse_name']);
$methods = $data['methods'];

$mysqli->begin_transaction(); // Démarrer une transaction

try {
    // Vérifier si une méthode de livraison existe déjà
    $stmt = $mysqli->prepare("SELECT id FROM delivery_methods WHERE country_name = ?");
    $stmt->bind_param("s", $country_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_delivery = $result->fetch_assoc();
    $stmt->close();

    if ($existing_delivery) {
        $delivery_id = $existing_delivery['id'];

        // Supprimer les anciennes méthodes et options associées
        $stmt = $mysqli->prepare("DELETE FROM delivery_prices WHERE method_id IN (SELECT id FROM delivery_options WHERE delivery_id = ?)");
        $stmt->bind_param("i", $delivery_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("DELETE FROM delivery_options WHERE delivery_id = ?");
        $stmt->bind_param("i", $delivery_id);
        $stmt->execute();
        $stmt->close();

        // Mettre à jour les informations de livraison
        $stmt = $mysqli->prepare("UPDATE delivery_methods SET country_name = ?, admin_city = ?, admin_hall = ?, admin_adress = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $country_name, $admin_city, $admin_hall, $admin_adress, $delivery_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insérer une nouvelle méthode de livraison
        $stmt = $mysqli->prepare("INSERT INTO delivery_methods (country_name, admin_city, admin_hall, admin_adress) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $country_name, $admin_city, $admin_hall, $admin_adress);
        $stmt->execute();
        $delivery_id = $stmt->insert_id;
        $stmt->close();
    }

    // Réinsérer les nouvelles méthodes et options de livraison
    foreach ($methods as $method) {
        if (!isset($method['name'])) continue;
        $method_name = $mysqli->real_escape_string($method['name']);

        // Insérer une nouvelle méthode de livraison
        $stmt = $mysqli->prepare("INSERT INTO delivery_options (delivery_id, method_name) VALUES (?, ?)");
        $stmt->bind_param("is", $delivery_id, $method_name);
        $stmt->execute();
        $method_id = $stmt->insert_id;
        $stmt->close();

        // Insérer les options associées
        if (isset($method['options'])) {
            foreach ($method['options'] as $option) {
                if (!isset($option['cityName'], $option['homePrice'], $option['deskPrice'])) continue;
            
                $city_name = $mysqli->real_escape_string($option['cityName']);
                $home_price = (float) $option['homePrice'];
                $desk_price = (float) $option['deskPrice'];
                $is_active = $option['isActive'];
            
                // Vérifier si l'option existe déjà
                $check = $mysqli->prepare("SELECT id FROM delivery_prices WHERE method_id = ? AND city_name = ?");
                $check->bind_param("is", $method_id, $city_name);
                $check->execute();
                $res = $check->get_result();
                $existing_price = $res->fetch_assoc();
                $check->close();
            
                if ($existing_price) {
                    // Mettre à jour
                    $stmt = $mysqli->prepare("UPDATE delivery_prices SET home_price = ?, desk_price = ?, isActive = ? WHERE id = ?");
                    $stmt->bind_param("ddii", $home_price, $desk_price, $is_active, $existing_price['id']);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Insérer
                    $stmt = $mysqli->prepare("INSERT INTO delivery_prices (method_id, city_name, home_price, desk_price, isActive) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("isddi", $method_id, $city_name, $home_price, $desk_price, $is_active);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            
        }
    }

    $mysqli->commit(); // Valider la transaction
    echo json_encode(['success' => true, 'message' => "Delivery methods updated successfully."]);
} catch (Exception $e) {
    $mysqli->rollback(); // Annuler en cas d'erreur
    echo json_encode(['success' => false, 'message' => "Error updating delivery methods.", 'error' => $e->getMessage()]);
}

$mysqli->close();
?>
