<?php
header("Content-Type: application/json; charset=UTF-8");

// --- 1. Fonction d'erreur ---
function send_json_error($message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// --- 2. Vérifier l'image uploadée ---
if (!isset($_FILES['image'])) {
    send_json_error('No image file uploaded.');
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    send_json_error('File upload error: ' . $file['error']);
}

// --- 3. Vérifier le type MIME ---
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp'];
if (!function_exists('mime_content_type')) {
    send_json_error('mime_content_type function not available on server.');
}

$file_mime_type = mime_content_type($file['tmp_name']);
if (!in_array($file_mime_type, $allowed_mime_types)) {
    send_json_error('Invalid file type. Only JPEG, PNG, and WEBP are allowed.');
}

// --- 4. Créer le dossier si nécessaire ---
$upload_dir = __DIR__ . '/../../uploads/brands/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        send_json_error('Failed to create upload directory. Check permissions.');
    }
}

// --- 5. Générer un nom unique et déplacer le fichier ---
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$unique_id = uniqid('img_', true);
$new_filename = $unique_id . '.' . $file_extension;
$destination = $upload_dir . $new_filename;

if (!is_writable($upload_dir)) {
    send_json_error("Upload directory not writable: $upload_dir");
}


if (!move_uploaded_file($file['tmp_name'], $destination)) {
    send_json_error('Failed to save the uploaded file. TMP: ' . $file['tmp_name'] . ' DEST: ' . $destination);
}


// --- 6. Retour JSON ---
echo json_encode([
    'success' => true,
    'fileId' => $new_filename
]);
?>
