<?php
$url = "https://api.mistral.ai/v1/chat/completions";
$apiKey = getenv("API_MISTRAL"); 

$value = json_decode(file_get_contents('php://input'), true);

$message = $value['message'] ?? 'Bonjour.';

$data = [
    "model" => "mistral-small-latest",  // Modèle Mistral AI
    "messages" => [
        ["role" => "system", "content" => "Tu es un assistant utile."],
        ["role" => "user", "content" => $message]
    ]
];

$headers = [
    "Authorization: Bearer $api_key",
    "Content-Type: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
