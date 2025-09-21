<?php

$value = json_decode(file_get_contents('php://input'), true);

// Vérifie que les données ont bien été reçues
if (empty($value) || !isset($value['url'])) {
    echo json_encode([
        'success' => false,
        'message' => 'url not found',
    ]);
    exit;
}
// Inclure le fichier de configuration de la base de données
$ups = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/yalConfig.php';

if (file_exists($ups)) {
    $dataUps = include $ups;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Fichier de configuration non trouvé.',
    ]);
    die;
}

// Vérifie si les identifiants API sont présents
if (empty($dataUps) || !isset($dataUps[0]['api_id']) || !isset($dataUps[0]['api_token'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Clés API manquantes dans yalConfig.',
    ]);
    die;
}

// Récupération des clés depuis upsConfig
$api_id = $dataUps[0]['api_id'];
$api_token = $dataUps[0]['api_token'];


// URL de l’API
$url = $value['url'];

// Initialisation de la requête cURL
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'X-API-ID: ' . $api_id,
        'X-API-TOKEN: ' . $api_token
    ),
));

$response_json = curl_exec($curl);
curl_close($curl);

// Vérifie si la réponse est vide
if (!$response_json) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des wilayas.',
    ]);
    die;
}

// Convertit la réponse JSON en tableau PHP
$response_array = json_decode($response_json, true);

// Retourne les wilayas
echo json_encode([
    'success' => true,
    'data' => $response_array
]);
?>
