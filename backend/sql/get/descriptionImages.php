<?php
header('Content-Type: application/json');

// Inclure le fichier de configuration de la base de données
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}

require_once $configPath;

// ✅ Définir le bon chemin vers le dossier contenant les images
$directory = __DIR__ . '/../../../uploads/description_catalogue';

// Vérifie si le dossier existe
if (!is_dir($directory)) {
    echo json_encode([
        'success' => false,
        'message' => 'Le dossier des images est introuvable.',
    ]);
    exit;
}

$files = array_diff(scandir($directory), ['.', '..']);
$images = [];

foreach ($files as $file) {
    // Vérifie si le fichier est une image
    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
        $images[] = 'https://management.hoggari.com/uploads/description_catalogue/' . $file;
    }
}

echo json_encode([
    'success' => true,
    'data' => $images
]);

exit();
