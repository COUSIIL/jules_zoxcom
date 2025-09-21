<?php

header("Content-Type: application/json; charset=UTF-8");

// Inclure le fichier de configuration de la base de données
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
$configPath2 = __DIR__ . '/../update/products/confirmOrder.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'dbConfig not found.',
    ]);
    exit;
}

if (!file_exists($configPath2)) {
    echo json_encode([
        'success' => false,
        'message' => 'confirmOrder not found.',
    ]);
    exit;
}

require_once $configPath;
require_once $configPath2;

$data = json_decode(file_get_contents('php://input'), true);

// Vérifier si les données nécessaires sont présentes
if (isset($data['id'], $data['status'], $data['value'])) {
    $id = $data['id'];
    $status = $data['status'];
    $value = $data['value'];

    // Liste des champs autorisés pour éviter les injections SQL
    $allowed_fields = ['status', 'note']; // Ajoutez d'autres champs si nécessaire
    if (!in_array($status, $allowed_fields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid field name.',
        ]);
        die;
    }

    // Vérifier si l'entrée existe
    $check_query = $mysqli->prepare("SELECT * FROM orders WHERE id = ?");
    $check_query->bind_param("i", $id);
    $check_query->execute();
    $check_result = $check_query->get_result();
    $fetchedResult1 = $check_result->fetch_assoc();
    $check_query->close();

    $check_query_item = $mysqli->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $check_query_item->bind_param("i", $id);
    $check_query_item->execute();
    $check_result_item = $check_query_item->get_result();

    $orderItems = [];
    while ($row = $check_result_item->fetch_assoc()) {
        $orderItems[] = $row;
    }
    $check_query_item->close();

    if ($check_result->num_rows != 0) {
        $sql = "UPDATE orders SET $status = ? WHERE id = ?";
        $update_query = $mysqli->prepare($sql);
        $update_query->bind_param("si", $value, $id);

        if ($update_query->execute()) {

            if($value == "confirmed") {
                // Appeler la mise à jour du stock pour chaque item
                foreach ($orderItems as $item) {
                    $stockResult = updateStock($mysqli, $item['model_id'], $item['product_id'], $item['qty']);
                    if (!$stockResult['success']) {
                        echo json_encode($stockResult);
                        die;
                    } else {
                        echo json_encode($stockResult);
                    }
                }
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Order updated',
                    'data' => $value,
                ]);
            }
            

            
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Error updating order: " . $mysqli->error,
            ]);
        }
    }

}

// Fermer la connexion
$mysqli->close();
die;
?>
