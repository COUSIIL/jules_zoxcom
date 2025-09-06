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

$data = json_decode(file_get_contents('php://input'), true);

// Vérifier si les données nécessaires sont présentes
if (isset($data['id'], $data['status'], $data['value'])) {
    $id = $data['id'];
    $status = $data['status'];
    $value = $data['value'];

    // Liste des champs autorisés pour éviter les injections SQL
    $allowed_fields = ['active']; // Ajoutez d'autres champs si nécessaire
    if (!in_array($status, $allowed_fields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid field name.',
        ]);
        die;
    }

    // Vérifier si l'entrée existe
    $check_query = $mysqli->prepare("SELECT * FROM deliver_method WHERE id = ?");
    $check_query->bind_param("i", $id);
    $check_query->execute();
    $check_result = $check_query->get_result();

    if ($check_result->num_rows != 0) {
        // L'entrée existe, effectuer une mise à jour
        $sql = "UPDATE deliver_method SET $status = ? WHERE id = ?";
        $update_query = $mysqli->prepare($sql);
        $update_query->bind_param("si", $value, $id);

        if ($update_query->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Status updated successfully.',
                'data' => $id,
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Error updating status: " . $mysqli->error,
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'status not found.' . $id,
        ]);
    }
}

// Fermer la connexion
$mysqli->close();
die;
?>
