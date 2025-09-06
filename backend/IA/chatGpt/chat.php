<?php
$api_url = "https://api.openai.com/v1/chat/completions";
$apiKey = getenv("API_CHAT_GPT"); 

$data = [
    "model" => "gpt-3.5-turbo", // Ou "gpt-4" si vous voulez GPT-4
    "messages" => [
        ["role" => "system", "content" => "Tu es un assistant utile."],
        ["role" => "user", "content" => "Bonjour !"]
    ],
    "temperature" => 0.7
];

$headers = [
    "Content-Type: application/json",
    "Authorization: Bearer " . $api_key
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
