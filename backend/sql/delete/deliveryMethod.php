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

$data = json_decode(file_get_contents("php://input"), true);
$ids = $data['id'] ?? null;

if (!$ids || !is_array($ids)) {
    echo json_encode(["success" => false, "message" => "Valid delivery_method IDs are required."]);
    exit;
}

$mysqli->begin_transaction();
try {
    // Création dynamique de la requête pour supprimer plusieurs IDs
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $mysqli->prepare("DELETE FROM deliver_method WHERE id IN ($placeholders)");

    // Liaison des paramètres dynamiquement
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();
    echo json_encode(["success" => true, "message" => "delivery_method deleted successfully."]);
} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(["success" => false, "message" => "Error deleting method.", "error" => $e->getMessage()]);
}

$mysqli->close();
?>
