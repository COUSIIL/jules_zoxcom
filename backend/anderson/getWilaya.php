


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

$url = " https://anderson-ecommerce.ecotrack.dz/api/v1/get/wilayas";


$headers = [
    "Authorization: Bearer $api_key",
    "Content-Type: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
