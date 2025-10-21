<?php
// yizzraInstagramV1.php - Instagram scraping logic

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables from the parent directory of backend/yizzra
$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

function scrapeInstagram($keyword) {
    $apifyToken = $_ENV['APIFY_API_TOKEN'];
    $actorId = $_ENV['APIFY_ACTOR_ID'];

    if (empty($apifyToken) || empty($actorId)) {
        return json_encode(['error' => 'Apify credentials are not configured.']);
    }

    $runUrl = "https://api.apify.com/v2/acts/{$actorId}/runs?token={$apifyToken}";

    $runInput = [
        'keyword' => $keyword,
    ];

    $ch = curl_init($runUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($runInput));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);

    $runResult = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return json_encode(['error' => "cURL Error: " . $error_msg]);
    }

    curl_close($ch);

    $runData = json_decode($runResult, true);
    $runId = $runData['data']['id'] ?? null;
    $datasetId = $runData['data']['defaultDatasetId'] ?? null;

    if (!$runId || !$datasetId) {
        return json_encode(['error' => 'Failed to start scraping run.', 'details' => $runData]);
    }

    // Wait for the run to finish
    $statusUrl = "https://api.apify.com/v2/acts/{$actorId}/runs/{$runId}?token={$apifyToken}&waitForFinish=60";
    $ch = curl_init($statusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $statusResult = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return json_encode(['error' => "cURL Error: " . $error_msg]);
    }

    curl_close($ch);

    $statusData = json_decode($statusResult, true);
    $status = $statusData['data']['status'] ?? 'UNKNOWN';

    if ($status !== 'SUCCEEDED') {
        return json_encode(['error' => 'Scraping run did not succeed.', 'status' => $status, 'details' => $statusData]);
    }

    $datasetUrl = "https://api.apify.com/v2/datasets/{$datasetId}/items?token={$apifyToken}";

    $ch = curl_init($datasetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $datasetResult = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return json_encode(['error' => "cURL Error: " . $error_msg]);
    }

    curl_close($ch);

    return $datasetResult;
}
