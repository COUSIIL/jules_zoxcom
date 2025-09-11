<?php
// Clé API Gemini

$apiKey = getenv("API_GEMINI"); 

// URL Gemini avec la clé dans l'URL
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";
$headers = [
  "Content-Type: application/json",
  "X-goog-api-key: $apiKey"
];


// Récupère le message depuis une requête POST JSON
$value = json_decode(file_get_contents('php://input'), true);
$message = $value['message'] ?? 'Bonjour.';

// Structure des données selon la documentation Gemini
$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $message]
            ]
        ]
    ]
];

// Headers pour l'API Gemini
$headers = [
    "Content-Type: application/json"
];

// Appel cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

// Gérer les erreurs cURL
if (curl_errno($ch)) {
    echo json_encode([
        "success" => false,
        "message" => 'Curl error: ' . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Renvoie directement la réponse de Gemini
$responseData = json_decode($response, true);

// Extraire le texte généré
$text = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Réponse indisponible.';

// Retourne uniquement le texte (ou structure personnalisée)
echo json_encode([
    'success' => true,
    'reply' => $text
]);
?>
