<?php
// yizzraApi.php - API for Instagram scraping

require_once 'yizzraInstagramV1.php';

header('Content-Type: application/json');

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

if (empty($keyword)) {
    echo json_encode(['error' => 'Keyword is required.']);
    exit;
}

$result = scrapeInstagram($keyword);

echo $result;
