<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable buffering for Nginx

require_once __DIR__ . '/../config/dbConfig.php';

// --- 1. Get Input Data ---
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
$conversationId = $input['conversation_id'] ?? null;
$userId = $input['user_id'] ?? null; // We'll need this for saving the message

if (empty($userMessage) || empty($conversationId) || empty($userId)) {
    echo "event: error\n";
    echo 'data: {"error": "Missing message, conversation_id, or user_id"}\n\n';
    flush();
    exit;
}

// --- 2. Save User's Message to DB ---
$stmt = $mysqli->prepare("INSERT INTO messages (conversation_id, role, content) VALUES (?, 'user', ?)");
$stmt->bind_param("is", $conversationId, $userMessage);
$stmt->execute();
$stmt->close();

// --- 3. Prepare and Call Gemini API ---
$apiKey = getenv("API_GEMINI");
if (!$apiKey) {
    echo "event: error\n";
    echo 'data: {"error": "API key not configured."}\n\n';
    flush();
    exit;
}
// Note: Using a specific streaming endpoint. And fixed model name.
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:streamGenerateContent?key=$apiKey";

$data = [
    "contents" => [
        ["role" => "user", "parts" => [["text" => $userMessage]]],
    ],
];

$fullAiResponse = ''; // To accumulate the full response for saving later

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // Set to false to stream output directly
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $chunk) use (&$fullAiResponse) {
    // Gemini's streaming response often sends multiple JSON objects prefixed with "data: " in a single chunk.
    // We need to process them carefully.
    $lines = explode("\n", trim($chunk));
    foreach ($lines as $line) {
        if (strpos($line, 'data: ') === 0) {
            $jsonStr = substr($line, 6);
            $decoded = json_decode($jsonStr, true);

            if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                $textChunk = $decoded['candidates'][0]['content']['parts'][0]['text'];
                $fullAiResponse .= $textChunk;

                // Send the chunk to the client, formatted as an SSE event
                echo "event: message\n";
                // The data payload should be a single line, so we JSON encode it.
                echo 'data: ' . json_encode(['text' => $textChunk]) . "\n\n";

                // Flush the output buffer to send the data immediately
                ob_flush();
                flush();
            }
        }
    }
    return strlen($chunk); // Required by cURL
});

curl_exec($ch);

if (curl_errno($ch)) {
    echo "event: error\n";
    echo 'data: {"error": "cURL Error: ' . curl_error($ch) . '"}\n\n';
    flush();
}

curl_close($ch);

// --- 4. Save Full AI Response to DB ---
if (!empty($fullAiResponse)) {
    $stmt = $mysqli->prepare("INSERT INTO messages (conversation_id, role, content) VALUES (?, 'assistant', ?)");
    $stmt->bind_param("is", $conversationId, $fullAiResponse);
    $stmt->execute();
    $stmt->close();
}

// --- 5. Signal End of Stream ---
echo "event: end\n";
echo "data: Stream ended\n\n";
flush();

$mysqli->close();
?>
