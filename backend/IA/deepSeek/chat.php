<?php
$api_url = "https://api.deepseek.com/chat/completions";
$api_key = "sk-6c595ce777834b18bd06b7dc3498fe37"; // Remplacez par votre clé API

$data = [
    "model" => "deepseek-chat",
    "messages" => [
        ["role" => "system", "content" => "You are a helpful assistant."],
        ["role" => "user", "content" => "Hello!"]
    ],
    "stream" => false
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
