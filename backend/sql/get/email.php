<?php
header('Content-Type: application/json');

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

// Vérifie si la table 'emails' existe
$table_check_query = "SHOW TABLES LIKE 'emails'";
$table_check_result = $mysqli->query($table_check_query);

if ($table_check_result->num_rows == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Table "emails" does not exist.',
    ]);
    $mysqli->close();
    exit();
}

// Requête pour obtenir les données de la table 'emails'
$sql = "SELECT * FROM emails ORDER BY data_time DESC";
$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'No data found.',
    ]);
    $mysqli->close();
    exit();
}

$data = $result->fetch_all(MYSQLI_ASSOC);

// Convertir les données en JSON et les envoyer au client
echo json_encode([
    'success' => true,
    'message' => 'Emails retrieved successfully.',
    'data' => $data
]);

$mysqli->close();
?>
