<?php


$ups = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/upsConfig.php';

if (file_exists($ups)) {
    $dataUps = include $ups;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'File not found.',
    ]);
    die;
}

// Vérifie si des données ont été retournées
if (empty($dataUps)) {
    echo json_encode([
        'success' => false,
        'message' => 'No ups api key found.',
    ]);
    die;
}

$api_key = $dataUps[0]['key'];

if (empty($api_key)) {
    echo json_encode([
        'success' => false,
        'message' => 'API key is missing.'
    ]);
    exit;
}

$url = "https://app.conexlog-dz.com/api/v1/get/wilayas?api_token=$api_key";


$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([
        'success' => false,
        'message' => 'Curl error: ' . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode([
        'success' => false,
        'message' => "HTTP $http_code: $response"
    ]);
    exit;
}

echo $response;

?>
