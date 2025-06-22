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

// Requête pour obtenir les données de la table 'insta'
$sql = "SELECT * FROM discount";
$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'No data yet',
    ]);
    $mysqli->close();
    exit();
}

$data = $result->fetch_all(MYSQLI_ASSOC);

// Convertir les données en JSON et les envoyer au client
echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data' => $data
]);


    // Fermer la connexion
$mysqli->close();
?>