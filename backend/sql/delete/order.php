<?php
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

$data = json_decode(file_get_contents("php://input"), true);
$order_id = $data['id'] ?? null;

if (!$order_id) {
    echo json_encode(["success" => false, "message" => "Order ID is required."]);
    exit;
}

$mysqli->begin_transaction();
try {
    // Supprimer d'abord les éléments associés dans order_items pour éviter une erreur de contrainte étrangère
    $stmt = $mysqli->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // Supprimer ensuite la commande
    $stmt = $mysqli->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();
    echo json_encode(["success" => true, "message" => "Order deleted successfully."]);
} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(["success" => false, "message" => "Error deleting order.", "error" => $e->getMessage()]);
}

$mysqli->close();
?>
