<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



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



$createTables = [
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        total_qty INT NOT NULL,
        country VARCHAR(100) NOT NULL,
        method VARCHAR(100) NOT NULL,
        delivery_zone VARCHAR(100),
        delivery_value VARCHAR(100) NOT NULL,
        type INT(11) NOT NULL,
        s_zone VARCHAR(100),
        m_zone VARCHAR(100),
        discount_code VARCHAR(50),
        discount_value VARCHAR(100) NOT NULL,
        note TEXT,
        total_price DECIMAL(10,2) NOT NULL,
        status VARCHAR(255) NOT NULL DEFAULT 'waiting',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        image VARCHAR(255),
        qty INT NOT NULL,
        ref VARCHAR(255) NOT NULL,
        product_id INT(11),
        model_id INT(11),
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS product_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id int NOT NULL,
        product_id INT NOT NULL,
        color VARCHAR(255) NOT NULL,
        size VARCHAR(255) NOT NULL,
        qty INT NOT NULL,
        ids INT(11),
        FOREIGN KEY (product_id) REFERENCES order_items(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL UNIQUE,
        power INT NOT NULL,
        image VARCHAR(255)
        
    )",
    "CREATE TABLE IF NOT EXISTS customers_details (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        customers_id INT NOT NULL,
        country VARCHAR(100) NOT NULL,
        method VARCHAR(100) NOT NULL,
        delivery_zone VARCHAR(100),
        sZone VARCHAR(100),
        mZone VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customers_id) REFERENCES customers(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS used_discount (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        phone_code VARCHAR(100) NOT NULL

    )"
];



// Exécution des requêtes de création des tables
foreach ($createTables as $query) {
    if (!$mysqli->query($query)) {
        echo json_encode(["success" => true, "message" => "Erreur SQL : ", "data" => $mysqli->error]);
        die;
    }
}

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
        
    $data_time = $data["time"] ?? 0;
    $name = $data["name"] ?? "";
    $phone = $data["phone"] ?? "";
    $totalQty = $data["qty"] ?? 0;
    $country = $data['country'] ?? "";
    $method = $data["method"] ?? "";
    $deliveryZone = $data["select"] ?? "";
    $deliveryValue = $data["deliveryValue"] ?? "0";
    $type = $data["type"] ?? "0";
    $zone1 = $data["zone1"] ?? 0;
    $zone2 = $data["zone2"] ?? 0;
    $mZone = $data["mZone"] ?? "";
    $sZone = $data["sZone"] ?? "";
    $discountCode = $data["discount"] ?? "";
    $discountValue = $data["discountValue"] ?? "0";
    $note = $data["note"] ?? "";
    $totalPrice = $data["total"] ?? 0;
    $status = "waiting"; // Statut par défaut

    $productsPrice = 0;


    foreach ($data["selectedProd"] as $product) {
            
        foreach ($product['selected'] as $detail) {
            $productsPrice += $product["price"] * $detail["qty"];
        }
        
    }
    
        

    if (empty($name) || empty($phone) || empty($country) || empty($method)) {
        echo json_encode(["success" => true, "message" => "All value needed", "data" => $country]);
        exit;
    }

    $mysqli->begin_transaction();
    

    try {
        // Vérifier si le client existe déjà
        $stmt1 = $mysqli->prepare("SELECT id, power FROM customers WHERE phone = ?");
        $stmt1->bind_param("s", $phone);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $customer = $result1->fetch_assoc();
        $stmt1->close();

        $customerId;
        if (!$customer) {
            // Insérer un nouveau client
            $stmt = $mysqli->prepare("INSERT INTO customers (name, phone, power, image) VALUES (?, ?, 1, '')");
            $stmt->bind_param("ss", $name, $phone);
            $stmt->execute();
            $customerId = $stmt->insert_id;
            $stmt->close();

            $stmt = $mysqli->prepare("INSERT INTO customers_details (customers_id, country, method, delivery_zone, sZone, mZone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $customerId, $country, $method, $deliveryZone, $sZone, $mZone);
            $stmt->execute();
            $stmt->close();
        } else {
            $customerId = $customer['id'];
            $power = $customer['power'] + 1;
            $update_query = $mysqli->prepare(
                "UPDATE customers SET name = ?, phone = ?, power = ? WHERE id = ?"
            );
            $update_query->bind_param("ssii", $name, $phone, $power, $customerId);
            if (!$update_query->execute()) {
                die(json_encode(["success" => false, "message" => "Error updating customer details: " . $update_query->error]));
            }
            //updateModels($models, $product_id, $mysqli);

            $update_query = $mysqli->prepare(
                "UPDATE customers_details SET country = ?, method = ?, delivery_zone = ?, sZone = ?, mZone = ? WHERE customers_id = ?"
            );
            $update_query->bind_param("sssssi", $country, $method, $deliveryZone, $sZone, $mZone, $customerId);
            if (!$update_query->execute()) {
                die(json_encode(["success" => false, "message" => "Error updating customer details: " . $update_query->error]));
            }
            //updateModels($models, $product_id, $mysqli);
        }

        //? Vérifier le code promo
        $stmtPromo = $mysqli->prepare("SELECT * FROM discount WHERE code = ?");
        $stmtPromo->bind_param("s", $discountCode);
        $stmtPromo->execute();
        $resultPromo = $stmtPromo->get_result();
        $codePromo = $resultPromo->fetch_assoc();
        $stmtPromo->close();

        $customCode = $phone . $discountCode;

        $stmtPromo1 = $mysqli->prepare("SELECT phone_code FROM used_discount WHERE phone_code = ?");
        $stmtPromo1->bind_param("s", $customCode);
        $stmtPromo1->execute();
        $resultPromo1 = $stmtPromo1->get_result();
        $codePromo1 = $resultPromo1->fetch_assoc();
        $stmtPromo1->close();

        // Initialiser les variables pour éviter les erreurs
        $valid_until = 0;
        $usage = 0;
        $limitation = 0;
        $type_promo = 0;
        $value = 0;
        $qty = 0;
        $usages = 0;
        $work = 1;
        $permission = 1;
        $discountValue = 0;

        if ($codePromo) {
            $valid_until = $codePromo["valid_until"] ?? null;
            $usage = (int) $codePromo["usage"];
            $limitation = (int) $codePromo["limitation"];
            $type_promo = (int) $codePromo["type"];
            $value = (float) $codePromo["value"];
            $qty = (int) $codePromo["qty"];
            $usages = (int) $codePromo["usages"];
            $work = (int) $codePromo["work"];

            // Vérification de la validité (en utilisant `time()` au lieu de `$data_time`)
            if ($valid_until !== null && ($valid_until < $data_time)) {
                $work = 0;
                $permission = 0;
            }

            if ($usage === 0 && $limitation === 0) {
                if ($usages >= $qty) {
                    $work = 0;
                } else {
                    if ($codePromo1) {
                        $permission = 0;
                    } else {
                        $usages++;
                        $stmt = $mysqli->prepare("INSERT INTO used_discount (phone_code) VALUES (?)");
                        $stmt->bind_param("s", $customCode);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            } elseif ($usage === 1 && $limitation === 0) {
                if ($codePromo1) {
                    $permission = 0;
                } else {
                    $usages++;
                    $stmt = $mysqli->prepare("INSERT INTO used_discount (phone_code) VALUES (?)");
                    $stmt->bind_param("s", $customCode);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // Appliquer la réduction uniquement si le code est valide
            if ($work === 1 && $permission === 1) {
                if ($type_promo === 0) {
                    $discountValue = ($productsPrice * $value) / 100;
                } else {
                    $discountValue = $value;
                }
                $deliveryValue1 = preg_replace('/[^0-9.]/', '', $deliveryValue);
                $deliveryValueT = floatval($deliveryValue1);
                $totalPrice = $productsPrice + $deliveryValueT - $discountValue;

                if (($usage === 1 && $limitation === 1) || ($usage === 0 && $limitation === 1)) {
                    $work = 0;
                }

            } else {
                // Si le code n'est pas valide, ne pas appliquer de réduction
                $discountValue = 0;
                $deliveryValue1 = preg_replace('/[^0-9.]/', '', $deliveryValue);
                $deliveryValueT = floatval($deliveryValue1);

                $totalPrice = $productsPrice + $deliveryValueT - $discountValue;
            }

            // Mettre à jour le code promo (usages + statut)
            $update_query = $mysqli->prepare(
                "UPDATE discount SET work = ?, usages = ? WHERE code = ?"
            );
            $update_query->bind_param("iis", $work, $usages, $discountCode);
            $update_query->execute();
            $update_query->close();
        }



        // Insérer la commande
        $stmt = $mysqli->prepare("INSERT INTO orders (name, phone, total_qty, country, method, delivery_zone, delivery_value, type, s_zone, m_zone, discount_code, discount_value, note, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissssisssssds", $name, $phone, $totalQty, $country, $method, $deliveryZone, $deliveryValue, $type, $sZone, $mZone, $discountCode, $discountValue, $note, $totalPrice, $status);
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();

        // Insérer les produits commandés
        if (!empty($data["selectedProd"])) {
            $stmtItem = $mysqli->prepare("INSERT INTO order_items (order_id, product_name, price, image, qty, ref, product_id, model_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtProduct = $mysqli->prepare("INSERT INTO product_items (order_id, product_id, color, size, qty, ids) VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($data["selectedProd"] as $product) {
                $productName = $product["name"];
                $productImage = $product["image"];
                $productPrice = $product["price"];
                $productQty = $product["qty"];
                $productRef = $product["ref"] ?? "";
                $product_id = $product["idP"];
                $model_id = $product["idM"];

                // Insérer dans order_items
                $stmtItem->bind_param("isdsisii", $orderId, $productName, $productPrice, $productImage, $productQty, $productRef, $product_id, $model_id);
                $stmtItem->execute();
                $orderItemId = $stmtItem->insert_id;

                // Insérer les détails de modèle dans product_items
                foreach ($product["selected"] as $model) {
                    
                    $model_size = $model['size'];
                    $model_color = $model['color'];
                    $model_qty = $model['qty'];
                    $id = $model['index'];
                    $stmtProduct->bind_param("iissii", $orderId, $orderItemId, $model_color, $model_size, $model_qty, $id);
                    $stmtProduct->execute();
                }
            }

            $stmtItem->close();
            $stmtProduct->close();
        }

        // Valider la transaction
        $mysqli->commit();

        try{
            $sendData = http_build_query([
                'name' => $name,
                'phone' => $phone,
                'deliveryZone' => $deliveryZone,
                'totalPrice' => $totalPrice,
                'orderID' => $orderId
            ]);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded",
                    'content' => $sendData,
                    'timeout' => 5 // ou 10 secondes selon besoin
                ]
            ]);
            
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://management.hoggari.com/backend/email/sendOrder.php",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false, // facultatif selon certificat
            ]);
            
            $response = curl_exec($curl);
            $curl_error = curl_error($curl);
            curl_close($curl);
            
            if ($response === false) {
                echo json_encode([
                    "success" => false,
                    "message" => "Erreur lors de l’appel de sendOrder.php",
                    "error" => $curl_error
                ]);
                exit;
            }


            echo $response;
            exit;
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Erreur inattendue",
                "error" => $e->getMessage()
            ]);
            exit;
        }
        
                


    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(["success" => false, "message" => "error exception" . $e]);
        exit;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Pleas try using post method.',
    ]);
    exit;
}
?>