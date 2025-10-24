<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function scrapeInstagram($keyword) {
    $apifyToken = $_ENV['APIFY_API_TOKEN'] ?? null;
    if (!$apifyToken) {
        return json_encode(['status'=>'error','message'=>'APIFY_API_TOKEN not set']);
    }

    // Nettoyage du keyword
    $keyword = preg_replace('/[^a-zA-Z0-9._]/', '', $keyword);

    $actorUrl = "https://api.apify.com/v2/acts/shu8hvrXbJbY3Eb9W/runs?token={$apifyToken}";
    $payload = [
        "directUrls" => ["https://www.instagram.com/{$keyword}/"],
        "resultsType" => "posts",
        "resultsLimit" => 10,
        "searchType" => "user",
        "searchLimit" => 1,
        "addParentData" => false
    ];

    $ch = curl_init($actorUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 180
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        return json_encode(['status'=>'error','message'=>"cURL Error: $err"]);
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if (empty($data)) {
        return json_encode(['status'=>'error','message'=>'Empty response from Apify']);
    }

    $datasetId = $data['data']['defaultDatasetId'] ?? null;
    if ($datasetId) {
        $datasetUrl = "https://api.apify.com/v2/datasets/{$datasetId}/items?token={$apifyToken}";
        $ch = curl_init($datasetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $datasetResult = curl_exec($ch);
        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            return json_encode(['status'=>'error','message'=>"cURL Error fetching dataset: $err"]);
        }
        curl_close($ch);

        $items = json_decode($datasetResult, true);
        return json_encode([
            'status'=>'success',
            'count'=>count($items),
            'data'=>$items
        ]);
    }

    return json_encode(['status'=>'error','message'=>'No datasetId found in run data']);
}

// 🔹 Appel automatique si keyword fourni via GET ou POST
$keyword = $_GET['keyword'] ?? $_POST['keyword'] ?? null;
if ($keyword) {
    echo scrapeInstagram($keyword);
}
