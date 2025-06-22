<?php
// URL de l'API Google Apps Script (remplacez par l'URL réelle de votre API)
$apiUrl = 'https://script.google.com/macros/s/AKfycbx6yo5sr-dkFAG0IigN6EuZTrHxHQBalUQRWUh9iGIWUxWDa2BD3336kRjsxGe8RA/exec';

// Utilisation de file_get_contents pour récupérer les données JSON
$jsonData = file_get_contents($apiUrl);

// Vérifier si la récupération des données a réussi
if ($jsonData === FALSE) {
    echo json_encode([
        'success' => false,
        'message' => 'ERROR getting data',
    ]);
    die;
}

echo json_encode([
    'success' => true,
    'message' => 'Data received',
    'data' => [
        $jsonData
    ]
]);


?>