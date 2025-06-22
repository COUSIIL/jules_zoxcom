


<?php
// Inclure le fichier de configuration de la base de données
$ups = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/upsConfig.php';

if (file_exists($ups)) {
    $dataUps = include $ups;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'File not found.',
    ]);
    die;
}

// Vérifie si des données ont été retournées
if (empty($dataUps)) {
    echo json_encode([
        'success' => false,
        'message' => 'No ups api key found.',
    ]);
    die;
}

$api_key = $dataUps[0]['key'];

$url = "https://app.conexlog-dz.com/api/v1/get/wilayas";


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
