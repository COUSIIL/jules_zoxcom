<?php
header("Content-Type: application/json; charset=UTF-8");

// Inclure fonctions de notification
$functionsPath = __DIR__ . '/../../../backend/notificationFunction.php';
if (file_exists($functionsPath)) require_once $functionsPath;

// Inclure config DB
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Récupérer données
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Please use POST method.']);
    exit;
}

// Champs principaux
$orderId = intval($data["orderId"] ?? 0);
$name = $data["name"] ?? "";
$phone = $data["phone"] ?? "";
$wilaya = $data["wilaya"] ?? "";
$commune = $data["commune"] ?? "";
$adresse = $data["adresse"] ?? "";
$deliveryMethod = $data["deliveryMethod"] ?? "";
$deliveryFees = floatval($data["deliveryFees"] ?? 0);
$deliveryType = intval($data["deliveryType"] ?? 0);
$discountCode = $data["discountCode"] ?? "";
$discountValue = floatval($data["discountValue"] ?? 0);
$totalPrice = floatval($data["total"] ?? 0);
$products = $data["products"] ?? [];

if (!$orderId || empty($name) || empty($phone) || empty($wilaya) || empty($commune)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$mysqli->begin_transaction();

try {
    $country = "Algeria";

    // --- Mettre à jour la commande ---
    $stmtOrder = $mysqli->prepare("
        UPDATE orders SET
            name = ?, 
            phone = ?, 
            country = ?, 
            method = ?, 
            delivery_zone = ?, 
            delivery_value = ?, 
            type = ?, 
            s_zone = ?, 
            m_zone = ?, 
            discount_code = ?, 
            discount_value = ?, 
            total_price = ?
        WHERE id = ?
    ");

    $stmtOrder->bind_param(
        "ssssssissssdi",
        $name,
        $phone,
        $country,
        $deliveryMethod,
        $wilaya,
        $deliveryFees,
        $deliveryType,
        $commune,
        $adresse,
        $discountCode,
        $discountValue,
        $totalPrice,
        $orderId
    );
    $stmtOrder->execute();
    $stmtOrder->close();

    // --- Supprimer produits existants ---
    $stmtDelProducts = $mysqli->prepare("DELETE FROM product_items WHERE order_id = ?");
    $stmtDelProducts->bind_param("i", $orderId);
    $stmtDelProducts->execute();
    $stmtDelProducts->close();

    $stmtDelItems = $mysqli->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmtDelItems->bind_param("i", $orderId);
    $stmtDelItems->execute();
    $stmtDelItems->close();

    // --- Insérer produits mis à jour ---
    if (!empty($products)) {
        $stmtItem = $mysqli->prepare("
            INSERT INTO order_items (order_id, product_name, price, image, qty, ref, product_id, model_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtProduct = $mysqli->prepare("
            INSERT INTO product_items (order_id, product_id, color, color_name, size, qty, total, promo, ids, indx)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($products as $product) {
            $productName = $product["name"];
            $productImage = $product["image"];
            $productPriceVar = floatval($product["price"]);
            $productQtyVar = intval($product["qty"]);
            $productRef = $product["ref"] ?? "";
            $product_idVar = intval($product["idP"]);
            $model_idVar = intval($product["idM"]);

            $stmtItem->bind_param("isdsisii", $orderId, $productName, $productPriceVar, $productImage, $productQtyVar, $productRef, $product_idVar, $model_idVar);
            $stmtItem->execute();
            $orderItemId = $stmtItem->insert_id;

            $selectedModels = is_array($product["selected"]) ? $product["selected"] : [];

            foreach ($selectedModels as $model) {
                $model_size = $model['size'] ?? "";
                $model_color = $model['color'] ?? "";
                $model_color_name = $model['colorName'] ?? "";
                $model_qty = intval($model['qty'] ?? 0);
                $model_total = floatval($model['total'] ?? 0);
                $model_promo = floatval($model['promo'] ?? 0);
                $id = intval($model['id'] ?? 0);
                $indx = intval($model['indx'] ?? $orderItemId);

                $stmtProduct->bind_param(
                    "iisssiddii",
                    $orderId,
                    $product_idVar,
                    $model_color,
                    $model_color_name,
                    $model_size,
                    $model_qty,
                    $model_total,
                    $model_promo,
                    $id,
                    $orderItemId
                );
                $stmtProduct->execute();
            }

        }

        $stmtItem->close();
        $stmtProduct->close();
    }

    $mysqli->commit();

    // Trigger global update for SSE
    include_once __DIR__ . '/../../trigger_update.php';
    triggerOrderUpdate(['id' => $orderId, 'action' => 'update'], $mysqli);

    echo json_encode(['success' => true, 'message' => 'Order updated successfully', 'data' => $orderId]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => 'Error updating order: ' . $e->getMessage()]);
}
?>
