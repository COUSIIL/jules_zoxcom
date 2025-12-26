<?php
header("Content-Type: application/json; charset=UTF-8");

// ====================================
// CONFIG
// ====================================

// Endpoint GET orders
$url = "https://orderdz.com/api/v1/feeef/get_orders"; // HTTPS conseillé

// Token de test
$token = "vAArSV1EdaTNkD3h7nRpltZTFuLsOkhVyQm6RXYl4jTfKXwShV2OcYqWT9gQ";

// ====================================
// CURL SETUP
// ====================================

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token",
        "Accept: application/json"
    ]
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ====================================
// RESPONSE
// ====================================

if ($error) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur cURL",
        "error" => $error
    ], JSON_PRETTY_PRINT);
    exit;
}

// Essayer de décoder le JSON reçu
$data = json_decode($response, true);

echo json_encode([
    "success" => true,
    "http_code" => $httpcode,
    "response" => $data
], JSON_PRETTY_PRINT);
?>
