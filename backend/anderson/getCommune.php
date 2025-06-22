<?php
// Inclure le fichier de configuration de la base de données
$anderson = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/andersonConfig.php';

if (file_exists($anderson)) {
    $dataAnderson = include $anderson;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'File not found.',
    ]);
    die;
}

// Vérifie si des données ont été retournées
if (empty($dataAnderson)) {
    echo json_encode([
        'success' => false,
        'message' => 'No anderson api key found.',
    ]);
    die;
}

$api_key = $dataAnderson[0]['key'];

$url = "https://anderson-ecommerce.ecotrack.dz/api/v1/get/communes";

$headers = [
    "Authorization: Bearer $api_key",
    "Content-Type: application/json"
];

// Récupération de wilaya_id depuis l'URL (ex: ?wilaya_id=2)
$value = json_decode(file_get_contents('php://input'), true);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

$data2 = $value['wilaya_id'];

// Vérifier si les données sont valides
if (!$data || !is_array($data)) {
    echo json_encode(["error" => "Données invalides"]);
    exit;
}

// Filtrer les communes avec le wilaya_id donné
$filteredCommunes = array_filter($data, function ($commune) use ($data2) {
    return isset($commune['wilaya_id']) && $commune['wilaya_id'] == $data2;
});

// Renvoyer les données filtrées en JSON
header('Content-Type: application/json');
echo json_encode(array_values($filteredCommunes)); // Réindexer le tableau
?>
