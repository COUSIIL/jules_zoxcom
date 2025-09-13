<?php
header("Content-Type: application/json; charset=UTF-8");

// --- 1. Basic Setup & Error Handling ---
function send_json_error($message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// --- 2. Handle File Upload ---
if (!isset($_FILES['image'])) {
    send_json_error('No image file uploaded.');
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    send_json_error('File upload error: ' . $file['error']);
}

// --- 3. Validate File Type ---
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp'];
$file_mime_type = mime_content_type($file['tmp_name']);

if (!in_array($file_mime_type, $allowed_mime_types)) {
    send_json_error('Invalid file type. Only JPEG, PNG, and WEBP are allowed.');
}

// --- 4. Save the file with a unique ID ---
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$unique_id = uniqid('img_', true);
$new_filename = $unique_id . '.' . $file_extension;
$destination = $upload_dir . $new_filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    send_json_error('Failed to save the uploaded file.');
}

// --- 5. Return the unique ID ---
echo json_encode(['success' => true, 'fileId' => $new_filename]);

?>
