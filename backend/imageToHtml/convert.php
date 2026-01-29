<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Nginx buffering OFF

// --- Désactiver tous les buffers PHP ---
while (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(true);

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

function send_error($message, $critical = true) {
    echo "event: error\n";
    echo 'data: ' . json_encode(['error' => $message], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n\n";
    @ob_flush();
    @flush();
    if ($critical) exit;
}

// --- 1. Get File ID ---
if (!isset($_GET['fileId']) || empty($_GET['fileId'])) {
    send_error('No file ID provided.');
}
$fileId = basename($_GET['fileId']); // sanitize

// --- 2. Locate File ---
$upload_dir = __DIR__ . '/../../uploads/brands/';
$filepath   = $upload_dir . $fileId;

if (!file_exists($filepath) || !is_file($filepath)) {
    send_error('File not found.');
}

// --- 3. Prepare Image ---
$file_mime_type = @mime_content_type($filepath);
if (!$file_mime_type) {
    unlink($filepath);
    send_error("Could not detect MIME type.");
}

$image_data = @file_get_contents($filepath);
if ($image_data === false) {
    unlink($filepath);
    send_error("Could not read file contents.");
}

$image_base64 = base64_encode($image_data);
$dataUrl = "data:{$file_mime_type};base64,{$image_base64}";

// --- 4. Kie API Setup ---
$apiKey = $_ENV['KIE_API_KEY'] ?? '';
if (!$apiKey) {
    unlink($filepath);
    send_error("KIE_API_KEY not configured.");
}

// Use Kie's Gemini 3 Pro endpoint (OpenAI compatible)
$url = "https://api.kie.ai/gemini-3-pro/v1/chat/completions";

$prompt = "Transforme cette image en un seul fichier HTML avec le CSS et le JS intégrés.
Le CSS doit être dans une balise <style> et le JS dans <script>.
Le code doit être moderne, responsive, avec des animations smooth et agréables (transitions, hover effects, etc).
Le design doit être fidèle à l'image mais amélioré pour une expérience utilisateur fluide.
Ne retourne QUE le code HTML complet, sans balises markdown (```html) ni texte explicatif avant ou après.";

$payload = [
    "model" => "gemini-3-pro", // Nom du modèle (souvent optionnel si endpoint spécifique, mais bon de le mettre)
    "stream" => true,
    "messages" => [
        [
            "role" => "system",
            "content" => "Tu es un expert en développement web frontend (HTML/CSS/JS) et UI/UX design."
        ],
        [
            "role" => "user",
            "content" => [
                [
                    "type" => "text",
                    "text" => $prompt
                ],
                [
                    "type" => "image_url",
                    "image_url" => [
                        "url" => $dataUrl
                    ]
                ]
            ]
        ]
    ]
];

// --- 5. Stream with cURL ---
$ch = curl_init($url);
if (!$ch) {
    unlink($filepath);
    send_error("Failed to initialize cURL.");
}

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $chunk) {
    $lines = explode("\n", $chunk);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        if (strpos($line, 'data: ') === 0) {
            $jsonStr = substr($line, 6);

            if ($jsonStr === "[DONE]") {
                echo "event: end\n";
                echo "data: {\"message\": \"Stream ended\"}\n\n";
                @ob_flush();
                @flush();
                continue;
            }

            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // OpenAI format: choices[0].delta.content
                $content = $decoded['choices'][0]['delta']['content'] ?? '';
                if ($content !== '') {
                    // On envoie le texte partiel
                    // Structure compatible avec le frontend existant qui attendait candidates[0].content.parts[0].text
                    // On va simplifier pour le frontend : renvoyer juste le texte dans une structure simple
                    echo "data: " . json_encode(['text' => $content], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n\n";
                }
            }
            @ob_flush();
            @flush();
        }
    }
    return strlen($chunk);
});

$ok = curl_exec($ch);

if ($ok === false) {
    $err = curl_error($ch);
    curl_close($ch);
    unlink($filepath);
    send_error('cURL Error: ' . $err);
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --- 6. Cleanup ---
if (file_exists($filepath)) {
    unlink($filepath);
}

if ($httpCode >= 400) {
    send_error("Kie API returned HTTP $httpCode");
}
?>
