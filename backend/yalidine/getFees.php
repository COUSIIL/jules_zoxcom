<?php

header('Content-Type: application/json');

// 📌 Récupération des données envoyées
$value = [];

// Si c'est un POST avec JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $value = json_decode($raw, true);
    }
}

// Si rien en POST JSON, on regarde GET
if (empty($value) && isset($_GET['wilaya_id'])) {
    $value = [
        'wilaya_id' => $_GET['wilaya_id']
    ];
}

// Vérifie que les données ont bien été reçues
if (empty($value)) {
    echo json_encode([
        'success' => false,
        'message' => 'no data',
    ]);
    exit;
}

if (!isset($value['wilaya_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'no wilaya selected',
    ]);
    exit;
}

// 📌 Inclure la config
$ups = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/yalConfig.php';

if (file_exists($ups)) {
    $dataUps = include $ups;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Fichier de configuration non trouvé.',
    ]);
    exit;
}

// Vérifie si les identifiants API sont présents
if (empty($dataUps) || !isset($dataUps[0]['api_id']) || !isset($dataUps[0]['api_token'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Clés API manquantes dans yalConfig.',
    ]);
    exit;
}

// 📌 Récupération des clés
$api_id = $dataUps[0]['api_id'];
$api_token = $dataUps[0]['api_token'];

// URL de l’API Yalidine
$url = "https://api.yalidine.app/v1/fees/?from_wilaya_id=42&to_wilaya_id={$value['wilaya_id']}";

// 📌 cURL
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
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
        'message' => 'Erreur lors de la récupération des fees.',
    ]);
    exit;
}

// Convertit la réponse JSON en tableau PHP
$response_array = json_decode($response_json, true);

// Retourne les données
echo json_encode([
    'success' => true,
    'data' => $response_array
]);
