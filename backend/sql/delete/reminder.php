<?php
header("Content-Type: application/json; charset=UTF-8");

// Inclure le fichier de configuration
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

if (!$mysqli) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

// Vérifier si la table existe
$table_check_query = "SHOW TABLES LIKE 'reminder'";
$table_check_result = $mysqli->query($table_check_query);
if ($table_check_result === false) {
    echo json_encode(['success' => false, 'message' => 'Error checking table: ' . $mysqli->error]);
    exit;
}
if ($table_check_result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Table reminder does not exist.']);
    exit;
}

// Lire les données JSON
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$orderId = $data['orderId'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing reminder ID.']);
    exit;
}

$stmt = $mysqli->prepare("DELETE FROM `reminder` WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Mettre à jour la colonne reminder_id dans la table 'orders' à NULL SI orderId est fourni
        if ($orderId) {
            $stmt2 = $mysqli->prepare("UPDATE `orders` SET `reminder_id` = NULL WHERE `id` = ?");
            if ($stmt2) {
                $stmt2->bind_param("i", $orderId);
                if (!$stmt2->execute()) {
                    // On log juste l'erreur, mais le reminder est supprimé
                    // echo json_encode(['success' => false, 'message' => 'Error updating order reminder_id: ' . $stmt2->error]);
                }
                $stmt2->close();
            }
        }

        echo json_encode(['success' => true, 'message' => 'Reminder deleted successfully.']);

    } else {
        echo json_encode(['success' => false, 'message' => 'No reminder found with this ID.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting reminder: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
