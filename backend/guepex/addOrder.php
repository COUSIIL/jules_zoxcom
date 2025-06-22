<?php
header('Content-Type: application/json');

// Charger les données JSON envoyées
$value = json_decode(file_get_contents('php://input'), true);

// Vérifie que les données ont bien été reçues
if (empty($value) || !isset($value['parcels']) || !is_array($value['parcels'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Données des colis manquantes ou incorrectes.',
    ]);
    exit;
}

// Charger les clés API depuis la config
$configPath = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/gpxConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Fichier de configuration non trouvé.',
    ]);
    exit;
}

$ups = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/gpxConfig.php';

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
        'message' => 'Clés API manquantes dans gpxConfig.',
    ]);
    die;
}

// Récupération des clés depuis upsConfig
$api_id = $dataUps[0]['api_id'];
$api_token = $dataUps[0]['api_token'];

// Encodage des colis en JSON
$postdata = json_encode($value['parcels']);

// URL de l’API guepex pour créer les colis
$url = "https://api.guepex.app/v1/parcels/";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postdata,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => [
        "X-API-ID: $api_id",
        "X-API-TOKEN: $api_token",
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur cURL : ' . curl_error($ch),
        'data' => null
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Vérifie si la réponse est vide ou si elle n’est pas un JSON valide
$decoded = json_decode($response, true);

if (empty($response) || $decoded === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Réponse invalide ou vide de l’API Gupex.',
        'data' => $response
    ]);
    exit;
}

// Réponse OK — retourne dans ton format standardisé
echo json_encode([
    'success' => true,
    'message' => 'Commande envoyée avec succès.',
    'data' => $decoded
]);
