<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

// Mock response for AI Music Generation (Suno via Kie)

$prompt = $_POST['prompt'] ?? '';
$instrumental = $_POST['instrumental'] ?? 'false';

// Check for uploaded audio sample
$audioSampleUrl = '';
if (isset($_FILES['audio_sample']) && $_FILES['audio_sample']['error'] === UPLOAD_ERR_OK) {
    // In a real scenario, we would upload this to the server/cloud
    // and pass the URL to Kie.
    $tmpName = $_FILES['audio_sample']['tmp_name'];
    $name = basename($_FILES['audio_sample']['name']);
    // For mock purposes, we acknowledge the file
    $audioSampleUrl = "uploaded://" . $name;
}

if (empty($prompt) && empty($audioSampleUrl)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Prompt or audio sample is required"]);
    exit;
}

sleep(3); // Simulate processing

echo json_encode([
    "success" => true,
    "url" => "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3",
    "info" => $audioSampleUrl ? "Generated based on sample: $audioSampleUrl" : "Generated from prompt"
]);
