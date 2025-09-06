


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

$url = "https://anderson-ecommerce.ecotrack.dz/api/v1/get/wilayas";


$headers = [
    "Authorization: Bearer $api_key",
    "Content-Type: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([
        'success' => false,
        'message' => 'Curl error: ' . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode([
        'success' => false,
        'message' => "HTTP $http_code: $response"
    ]);
    exit;
}

echo $response;
?>
