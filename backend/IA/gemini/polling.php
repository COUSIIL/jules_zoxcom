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
$type   = safeStr($_GET["type"] ?? "image"); // "image" or "video"

if ($taskId === "") respond(400, ["success" => false, "error" => "taskId requis"]);

if ($type === 'video') {
    // Video Polling (Veo, Kling, etc.)
    $endpoint = "https://api.kie.ai/api/v1/jobs/fetch?taskId=" . rawurlencode($taskId);
} else {
    // Flux Polling (Default/Image)
    $endpoint = "https://api.kie.ai/api/v1/flux/kontext/record-info?taskId=" . rawurlencode($taskId);
}

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
    "error" => "KIE Polling failed",
    "http" => $http,
    "kie_code" => $decoded["code"] ?? null,
    "message" => $decoded["msg"] ?? ($decoded["message"] ?? null),
    "raw" => $decoded
  ]);
}

$data = $decoded["data"] ?? [];

if ($type === 'video') {
    // --- Video Logic ---
    // status: 0=created/generating, 1=success, 2=failed
    $status = isset($data["status"]) ? (int)$data["status"] : -1;

    if ($status === 1) {
        $result = $data["result"] ?? [];
        // Attempt to find video url
        $videoUrl = $result["videoUrl"] ?? ($result["video_url"] ?? ($result["url"] ?? ""));

        respond(200, [
            "success" => true,
            "done" => true,
            "state" => "success",
            "video_url" => $videoUrl
        ]);
    }

    if ($status === 2 || $status === 3) {
         respond(200, [
            "success" => true,
            "done" => true,
            "state" => "fail",
            "failCode" => $status,
            "failMsg" => "Video generation failed (Status $status)"
         ]);
    }

    // Generating
    respond(200, [
        "success" => true,
        "done" => false,
        "state" => "generating",
        "detail" => $status
    ]);

} else {
    // --- Flux/Image Logic ---
    /*
       Status Values (Flux):
         0: GENERATING
         1: SUCCESS
         2: CREATE_TASK_FAILED
         3: GENERATE_FAILED
    */
    $successFlag = isset($data["successFlag"]) ? (int)$data["successFlag"] : -1;

    if ($successFlag === 1) {
        // Success
        $resp = $data["response"] ?? [];
        $resultImageUrl = $resp["resultImageUrl"] ?? "";

        // Frontend expects resultUrls array
        $resultUrls = [];
        if ($resultImageUrl) {
            $resultUrls[] = $resultImageUrl;
        }

        respond(200, [
            "success" => true,
            "done" => true,
            "state" => "success",
            "resultUrls" => $resultUrls,
            "video_url" => $resultUrls[0] ?? null
        ]);
    }

    if ($successFlag === 2 || $successFlag === 3) {
        // Fail
        $errorMsg = $data["errorMessage"] ?? "Erreur inconnue";
        respond(200, [
            "success" => true,
            "done" => true,
            "state" => "fail",
            "failCode" => $successFlag,
            "failMsg" => $errorMsg,
            "resultUrls" => []
        ]);
    }

    // Otherwise: Generating (0) or Unknown
    respond(200, [
      "success" => true,
      "done" => false,
      "state" => "generating",
      "detail" => $successFlag
    ]);
}
?>
