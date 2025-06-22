<?php
header('Content-Type: application/json');

// Inclure le fichier de configuration de la base de données
$path = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/yalConfig.php';

if (file_exists($path)) {
    $data = include $path;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'File not found.',
    ]);
    die;
}

// Vérifie si des données ont été retournées
if (empty($data)) {
    echo json_encode([
        'success' => false,
        'message' => 'No data found.',
    ]);
    die;
}


// Masquer toutes les lettres sauf les 4 premières
$api = $data[0]['api_id'];
$id = $data[0]['api_token'];
$visibleApi = substr($api, 0, 4);
$visibleId = substr($id, 0, 4);
$hiddenApi = str_repeat('*', max(0, strlen($api) - 4));
$hiddenId = str_repeat('*', max(0, strlen($id) - 4));

echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data' => [
        'api' => $visibleApi . $hiddenApi,
        'token' => $visibleId . $hiddenId,
        'work' => $data[0]['work']
    ]
]);
?>
