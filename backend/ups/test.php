<?php
header('Content-Type: application/json');

// Inclure le fichier de configuration de la base de données
$path = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/upsConfig.php';

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
$key = $data[0]['key'];
$visible = substr($key, 0, 4);
$hidden = str_repeat('*', max(0, strlen($key) - 4));

echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data' => [
        'key' => $visible . $hidden,
        'work' => $data[0]['work']
    ]
]);
?>
