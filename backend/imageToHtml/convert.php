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

// --- 4. Gemini API Setup ---
$apiKey = $_ENV['API_GEMINI'];
if (!$apiKey) {
    unlink($filepath);
    send_error("API key not configured.");
}

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:streamGenerateContent?key=" . urlencode($apiKey);

$prompt = "Transforme cette image en un seul fichier HTML avec le CSS intégré. 
Le CSS doit être dans une balise <style> dans le <head>. 
Le code doit être aussi fidèle que possible à l'image, responsive et utiliser des pratiques modernes. 
Ne retourne que le code HTML, sans texte supplémentaire avant ou après.";

$data = [
    "contents" => [
        [
            "parts" => [
                ["inline_data" => [
                    "mime_type" => $file_mime_type,
                    "data" => $image_base64
                ]],
                ["text" => $prompt]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.4,
        "topK" => 32,
        "topP" => 1,
        "maxOutputTokens" => 8192
    ]
];

// --- 5. Stream with cURL ---
$ch = curl_init($url);
if (!$ch) {
    unlink($filepath);
    send_error("Failed to initialize cURL.");
}

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-goog-api-key: $apiKey"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $chunk) {
    $lines = explode("\n", $chunk);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        // Gemini stream renvoie "data: ..."
        if (strpos($line, 'data: ') === 0) {
            $jsonStr = substr($line, 6);

            if ($jsonStr === "[DONE]") {
                // ✅ Fin du stream
                echo "event: end\n";
                echo "data: {\"message\": \"Stream ended\"}\n\n";
                @ob_flush();
                @flush();
                continue;
            }

            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "data: " . json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n\n";
            } else {
                echo "data: " . $jsonStr . "\n\n";
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

if ($httpCode !== 200) {
    send_error("Gemini API returned HTTP $httpCode");
}
?>
