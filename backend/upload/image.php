<?php
header('Content-Type: application/json');

// Vérifier si une image est envoyée
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/category/';
    $categoryName = isset($_POST['categoryName']) ? $_POST['categoryName'] : 'default';

    // Créer le dossier si nécessaire
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $image = $_FILES['image'];
    $fileExtension = pathinfo($image['name'], PATHINFO_EXTENSION);
    $fileName = $categoryName . '.webp';
    $filePath = $uploadDir . $fileName;

    // Déplacer le fichier
    if (move_uploaded_file($image['tmp_name'], $filePath)) {
        echo json_encode([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => '/uploads/category/' . $fileName,
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to upload image.',
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No image uploaded.',
    ]);
}
?>
