<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

// Mock response for AI Image Generation
// In a real implementation, this would call KIE.ai or similar API
sleep(2); // Simulate processing time

echo json_encode([
    "success" => true,
    "url" => "https://via.placeholder.com/1024x1024.png?text=AI+Generated+Image"
]);
