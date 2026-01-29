<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function safeStr($v, string $default = ''): string {
  return is_string($v) ? trim($v) : $default;
}

$autoload = __DIR__ . '/../../../vendor/autoload.php';
if (!file_exists($autoload)) {
  respond(500, ["success" => false, "error" => "Autoload introuvable : $autoload"]);
}
require $autoload;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$apiKey = $_ENV['KIE_API_KEY'] ?? '';
if (!$apiKey) respond(500, ["success" => false, "error" => "KIE_API_KEY manquante (.env)"]);

$prompt = safeStr($_POST['prompt'] ?? '');
$instrumental = ($_POST['instrumental'] ?? 'false') === 'true';

if (empty($prompt)) {
    respond(400, ["success" => false, "error" => "Prompt is required"]);
}

$model = "suno"; // Using Suno via Kie
$endpoint = "https://api.kie.ai/api/v1/jobs/createTask";

$input = [
    "prompt" => $prompt,
    "make_instrumental" => $instrumental
];

$payload = [
    "model" => $model,
    "input" => $input
];

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer {$apiKey}",
    "Content-Type: application/json"
  ],
  CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
  CURLOPT_TIMEOUT => 60,
]);
$body = curl_exec($ch);
$err  = curl_error($ch);
$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) respond(502, ["success" => false, "error" => "Erreur cURL", "detail" => $err]);

$decoded = json_decode($body ?: '', true);

if (!is_array($decoded) || ($decoded["code"] ?? null) !== 200) {
    respond(502, [
        "success" => false,
        "error" => "KIE createTask failed",
        "kie_code" => $decoded["code"] ?? null,
        "message" => $decoded["message"] ?? null,
        "raw" => $decoded
    ]);
}

$taskId = $decoded["data"]["taskId"] ?? "";
if ($taskId === "") {
    respond(502, ["success" => false, "error" => "taskId missing", "raw" => $decoded]);
}

respond(200, [
    "success" => true,
    "taskId" => $taskId,
    "info" => "Music generation task started"
]);
?>
