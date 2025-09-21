<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$autoload = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($autoload)) {
    http_response_code(500);
    echo json_encode(["error" => "Autoload introuvable : $autoload"]);
    exit;
}
require $autoload;

// Charger .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$apiKey = $_ENV['API_GEMINI'] ?? '';
if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(["error" => "Clé API Gemini introuvable dans .env"]);
    exit;
}

// Connexion DB
require_once __DIR__ . '/../config/dbConfig.php';
require_once __DIR__ . '/../config/init_db.php';

// Lire input JSON
$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$conversationId = $input['conversation_id'] ?? null;
$userId = $input['user_id'] ?? null;

if (empty($message) || !$conversationId || !$userId) {
    http_response_code(400);
    echo json_encode(["error" => "Paramètres manquants"]);
    exit;
}

// ✅ 1. Récupérer l’historique existant depuis la DB
$stmt = $mysqli->prepare("SELECT role, content FROM messages WHERE conversation_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $conversationId);
$stmt->execute();
$res = $stmt->get_result();
$history = [];
while ($row = $res->fetch_assoc()) {
    $history[] = [
        "role" => $row['role'] === "user" ? "user" : "model",
        "parts" => [["text" => $row['content']]]
    ];
}
$stmt->close();

// Ajouter le nouveau message utilisateur dans l’historique
$history[] = [
    "role" => "user",
    "parts" => [["text" => $message]]
];

// ✅ 2. Sauvegarder le message utilisateur en DB
$stmt = $mysqli->prepare("INSERT INTO messages (conversation_id, role, content, created_at) VALUES (?, 'user', ?, NOW())");
$stmt->bind_param("is", $conversationId, $message);
$stmt->execute();
$stmt->close();

// ✅ 3. Envoyer l’historique complet à Gemini
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";

$data = ["contents" => $history];
$headers = [
    "Content-Type: application/json",
    "x-goog-api-key: $apiKey"
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true,
]);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["error" => "cURL error: " . curl_error($ch)]);
    exit;
}
curl_close($ch);

$result = json_decode($response, true);
$text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$text) {
    http_response_code(500);
    echo json_encode(["error" => "Réponse invalide de Gemini", "raw" => $result]);
    exit;
}

// ✅ 4. Sauvegarder la réponse de Gemini en DB
$stmt = $mysqli->prepare("INSERT INTO messages (conversation_id, role, content, created_at) VALUES (?, 'model', ?, NOW())");
$stmt->bind_param("is", $conversationId, $text);
$stmt->execute();
$stmt->close();

// ✅ 5. Réponse finale envoyée au frontend
echo json_encode([
    "reply" => $text
]);

$mysqli->close();
