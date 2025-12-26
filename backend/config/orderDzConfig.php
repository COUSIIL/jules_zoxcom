<?php
header('Content-Type: application/json');

// Inclure le fichier de configuration de la base de données
$configPath = __DIR__ . '/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}

require_once $configPath;

// Requête pour obtenir les données de la table 'insta'
$sql = "SELECT * FROM orderdz_module";
$result = $mysqli->query($sql);

if (!$result || $result->num_rows === 0) {
    return []; // Retourne un tableau vide si aucune donnée n'est trouvée
}

$data = $result->fetch_all(MYSQLI_ASSOC);

// Fermer la connexion
$mysqli->close();

// Retourner les données
return $data;
?>
