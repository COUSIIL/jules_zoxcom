<?php
header("Content-Type: application/json; charset=UTF-8");

// Charger config
$anderson = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/andersonConfig.php';
if (!file_exists($anderson)) {
    echo json_encode(['success' => false, 'message' => 'File not found.']);
    exit;
}

$dataAnderson = include $anderson;
if (empty($dataAnderson)) {
    echo json_encode(['success' => false, 'message' => 'No anderson api key found.']);
    exit;
}

$api_key = $dataAnderson[0]['key'] ?? null;
if (!$api_key) {
    echo json_encode(['success' => false, 'message' => 'API key missing']);
    exit;
}

// Récupérer tracking depuis GET
$tracking = $_GET['tracking'] ?? '';
if (!$tracking) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Tracking number required"]);
    exit;
}

// Préparer GET vers l'API Anderson
$url = "https://anderson-ecommerce.ecotrack.dz/api/v1/get/tracking/info?tracking=" . urlencode($tracking);
$headers = [
    "Authorization: Bearer $api_key",
    "Accept: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Retourner la réponse brute d'Anderson
http_response_code($httpCode);
echo $response;
