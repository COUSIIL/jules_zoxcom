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

$prompt   = safeStr($_POST["prompt"] ?? "");
$imageUrl = safeStr($_POST["image_url"] ?? "");
$model    = safeStr($_POST["model"] ?? "kling-2.6/image-to-video"); // <= tu peux changer ici si KIE attend un autre nom
$duration = safeStr($_POST["duration"] ?? "5");      // doc: "5" ou "10"
$soundStr = safeStr($_POST["sound"] ?? "false");     // "true"/"false"

// validations
if ($prompt === "") respond(400, ["success" => false, "error" => "prompt requis"]);
if (mb_strlen($prompt) > 2500) respond(400, ["success" => false, "error" => "prompt trop long (max 2500)"]);

if ($imageUrl === "" || !preg_match('#^https?://#i', $imageUrl)) {
  respond(400, ["success" => false, "error" => "image_url invalide (http/https requis)"]);
}

if ($duration !== "5" && $duration !== "10") {
  respond(400, ["success" => false, "error" => "duration invalide (valeurs: '5' ou '10')"]);
}

$sound = in_array(strtolower($soundStr), ['1','true','yes','on'], true);

$endpoint = "https://api.kie.ai/api/v1/jobs/createTask";

$payload = [
  "model" => $model,
  // "callBackUrl" => "https://ton-domaine.com/api/kie-callback", // optionnel
  "input" => [
    "prompt" => $prompt,
    "image_urls" => [$imageUrl],
    "sound" => $sound,
    "duration" => $duration
  ]
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
  CURLOPT_TIMEOUT => 120,
]);
$body = curl_exec($ch);
$err  = curl_error($ch);
$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) respond(502, ["success" => false, "error" => "Erreur cURL", "detail" => $err]);

$decoded = json_decode($body ?: '', true);
if (!is_array($decoded)) {
  respond(502, ["success" => false, "error" => "Réponse KIE non-JSON", "http" => $http, "raw" => $body]);
}

if (($decoded["code"] ?? null) !== 200) {
  respond(502, [
    "success" => false,
    "error" => "KIE createTask failed",
    "http" => $http,
    "kie_code" => $decoded["code"] ?? null,
    "message" => $decoded["message"] ?? null,
    "raw" => $decoded
  ]);
}

$taskId = $decoded["data"]["taskId"] ?? "";
if (!is_string($taskId) || $taskId === "") {
  respond(502, ["success" => false, "error" => "taskId manquant dans la réponse KIE", "raw" => $decoded]);
}

respond(200, [
  "success" => true,
  "taskId" => $taskId
]);
