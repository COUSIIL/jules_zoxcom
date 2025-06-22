<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

// Inclure le fichier de configuration de la base de données
$configPath = __DIR__ . '/../../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}

require_once $configPath;

$data = json_decode(file_get_contents('php://input'), true);

function response($success, $message, $data, $mysqli) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ]);
    $mysqli->close();
    die;
}

// Vérifier si les données nécessaires sont présentes
if (isset($data['table'], $data['value'])) {
    $table = $data['table'];
    $value = $data['value'];

    // Liste des champs autorisés pour éviter les injections SQL
    $allowed_fields = ['yal_module', 'ups_module', 'gpx_module', 'anderson_module']; // Ajoutez d'autres champs si nécessaire
    if (!in_array($table, $allowed_fields)) {
        response(false, 'Invalid table name.', '', $mysqli);
    }

    // Vérifier si l'entrée existe
    $check_query = $mysqli->prepare("SELECT * FROM $table WHERE id = 1");
    $check_query->execute();
    $check_result = $check_query->get_result();


    if ($check_result->num_rows != 0) {
        // L'entrée existe, effectuer une mise à jour
        $sql = "UPDATE $table SET work = ? WHERE id = 1";
        $update_query = $mysqli->prepare($sql);
        $update_query->bind_param("i", $value);

        if ($update_query->execute()) {
            response(true, 'Module updated successfully.', '', $mysqli);
        } else {
            response(false, 'Error Module Updating.', $mysqli->error, $mysqli);
        }
    } else {
        response(false, 'Module not found.', '', $mysqli);
    }
} else {
    response(false, 'No Entring Data.', '', $mysqli);
}

?>
