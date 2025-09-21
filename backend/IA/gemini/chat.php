<?php


header("Content-Type: application/json");

$autoload = __DIR__ . '/../../../vendor/autoload.php';

if (!file_exists($autoload)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Autoload introuvable : $autoload"
    ]);
    exit;
}

require $autoload;


// Charger les variables d'environnement (.env à la racine)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$apiKey = $_ENV['API_GEMINI'] ?? '';

if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Clé API Gemini introuvable dans .env"
    ]);
    exit;
}

// URL Gemini
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";


// Récupérer le message depuis POST JSON
$value = json_decode(file_get_contents('php://input'), true);
$message = $value['message'] ?? 'Bonjour.';

// Structure de la requête
$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $message]
            ]
        ]
    ]
];

// Headers
$headers = [
    "Content-Type: application/json",
    "x-goog-api-key: $apiKey"
];



// cURL
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_FOLLOWLOCATION => true
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([
        "success" => false,
        "message" => 'Curl error: ' . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Extraire le texte généré
$responseData = json_decode($response, true);


$text = $responseData['candidates'][0]['content']['parts'][0]['text']
    ?? null;

if (!$text) {
    echo json_encode([
        'success' => false,
        'message' => 'Réponse indisponible.',
        'raw' => $responseData // debug optionnel
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'reply' => $text
]);
