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

$taskId = safeStr($_GET["taskId"] ?? "");
if ($taskId === "") respond(400, ["success" => false, "error" => "taskId requis"]);

$endpoint = "https://api.kie.ai/api/v1/jobs/recordInfo?taskId=" . rawurlencode($taskId);

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => ["Authorization: Bearer {$apiKey}"],
  CURLOPT_TIMEOUT => 60,
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
    "error" => "KIE recordInfo failed",
    "http" => $http,
    "kie_code" => $decoded["code"] ?? null,
    "message" => $decoded["message"] ?? null,
    "raw" => $decoded
  ]);
}

$data = $decoded["data"] ?? [];
$state = $data["state"] ?? "unknown";

// KIE renvoie resultJson en string JSON: "{\"resultUrls\":[\"...\"]}"
$resultUrls = [];
if (isset($data["resultJson"]) && is_string($data["resultJson"]) && trim($data["resultJson"]) !== "") {
  $rj = json_decode($data["resultJson"], true);
  if (is_array($rj) && isset($rj["resultUrls"]) && is_array($rj["resultUrls"])) {
    $resultUrls = array_values(array_filter($rj["resultUrls"], fn($u) => is_string($u) && $u !== ""));
  }
}

if ($state === "fail") {
  respond(200, [
    "success" => true,
    "done" => true,
    "state" => "fail",
    "failCode" => $data["failCode"] ?? "",
    "failMsg" => $data["failMsg"] ?? "",
    "resultUrls" => $resultUrls
  ]);
}

if ($state === "success") {
  respond(200, [
    "success" => true,
    "done" => true,
    "state" => "success",
    "resultUrls" => $resultUrls,
    "video_url" => $resultUrls[0] ?? null
  ]);
}

// waiting / queuing / generating
respond(200, [
  "success" => true,
  "done" => false,
  "state" => $state
]);
