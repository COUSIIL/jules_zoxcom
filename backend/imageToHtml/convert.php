<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable buffering for Nginx

// --- 1. Basic Setup & Error Handling ---
function send_error($message) {
    echo "event: error\n";
    echo 'data: ' . json_encode(['error' => $message]) . "\n\n";
    flush();
    exit;
}

// --- 2. Get File ID from Query ---
if (!isset($_GET['fileId'])) {
    send_error('No file ID provided.');
}
$fileId = $_GET['fileId'];

// --- 3. Locate and Validate File ---
$upload_dir = __DIR__ . '/uploads/';
$filepath = $upload_dir . basename($fileId); // Use basename to prevent directory traversal

if (!file_exists($filepath)) {
    send_error('File not found.');
}

// --- 4. Prepare Image Data ---
$file_mime_type = mime_content_type($filepath);
$image_data = file_get_contents($filepath);
$image_base64 = base64_encode($image_data);

// --- 5. Prepare and Call Gemini API ---
$apiKey = getenv("API_GEMINI");
if (!$apiKey) {
    // Don't forget to delete the temp file
    unlink($filepath);
    send_error("API key not configured.");
}

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-vision:streamGenerateContent";
$prompt = "Transforme cette image en un seul fichier HTML avec le CSS intégré. Le CSS doit être dans une balise <style> dans le <head>. Le code doit être aussi fidèle que possible à l'image, responsive et utiliser des pratiques modernes. Ne retourne que le code HTML, sans texte supplémentaire avant ou après.";

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
    // It's good practice to add generationConfig, e.g. to control output
    "generationConfig" => [
        "temperature" => 0.4,
        "topK" => 32,
        "topP" => 1,
        "maxOutputTokens" => 8192, // Increased for potentially large HTML
        "stopSequences" => []
    ]
];

// --- 6. cURL Streaming ---
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-goog-api-key: $apiKey"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $chunk) {
    // Gemini's streaming response is a series of JSON objects.
    // We need to parse them and re-format as valid SSE.
    // A single chunk from curl can contain multiple JSON objects.
    $lines = explode("\n", trim($chunk));
    foreach ($lines as $line) {
        if (strpos($line, 'data: ') === 0) {
            $jsonStr = substr($line, 6);
            // Just forward the data part of the SSE from Gemini
            echo "data: " . $jsonStr . "\n\n";
            ob_flush();
            flush();
        }
    }
    return strlen($chunk);
});

curl_exec($ch);

if (curl_errno($ch)) {
    send_error('cURL Error: ' . curl_error($ch));
}

curl_close($ch);

// --- 7. Cleanup and Signal End ---
unlink($filepath); // Delete the temporary file

echo "event: end\n";
echo "data: Stream ended\n\n";
flush();

?>
