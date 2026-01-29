<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

// Mock response for AI Image Generation
// In a real implementation, this would call Kie.ai (e.g. createTask with 'kling' or 'midjourney')

$prompt = $_POST['prompt'] ?? '';
$aspect = $_POST['aspect_ratio'] ?? '1:1';
$imageUrl = $_POST['image_url'] ?? ''; // Support for img-to-img

if (empty($prompt)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Prompt is required"]);
    exit;
}

// Simulate processing time
sleep(2);

// Log param if needed (for debug)
// file_put_contents('debug_gen_img.log', print_r($_POST, true), FILE_APPEND);

echo json_encode([
    "success" => true,
    "url" => "https://via.placeholder.com/1024x1024.png?text=" . urlencode(substr($prompt, 0, 20) . ($imageUrl ? " + IMG" : "")),
    "info" => "Mock generation via Kie"
]);
