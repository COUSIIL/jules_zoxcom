<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}

require_once $configPath;

function sanitizeFilename($filename) {
    $info = pathinfo($filename);
    $name = $info['filename'];
    $ext = isset($info['extension']) ? '.' . $info['extension'] : '';
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
    $name = preg_replace('/_+/', '_', $name);
    return $name . $ext;
}

function isSafeUrl($url) {
    $parsed = parse_url($url);
    if (!$parsed || !isset($parsed['scheme']) || !isset($parsed['host'])) {
        return false;
    }

    if (!in_array(strtolower($parsed['scheme']), ['http', 'https'])) {
        return false;
    }

    $host = $parsed['host'];

    // Resolve to IP
    $ip = gethostbyname($host);
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false; // Could not resolve
    }

    // Check for private IPs
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $url = $input['url'] ?? '';
    $folderId = $input['folderId'] ?? 1; // Default to root folder (1) or a specific ID

    if (empty($url)) {
        echo json_encode(['success' => false, 'message' => 'URL is required.']);
        exit;
    }

    if (!isSafeUrl($url)) {
        echo json_encode(['success' => false, 'message' => 'Invalid or unsafe URL.']);
        exit;
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/generated/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Determine filename
    $filename = basename(parse_url($url, PHP_URL_PATH));
    if (empty($filename)) {
        $filename = 'generated_' . time() . '.bin';
    }

    // Add extension if missing
    if (!pathinfo($filename, PATHINFO_EXTENSION)) {
        $filename .= '.png'; // Fallback
    }

    $sanitizedName = sanitizeFilename($filename);
    $fileExtension = strtolower(pathinfo($sanitizedName, PATHINFO_EXTENSION));
    $fileNameWithoutExt = pathinfo($sanitizedName, PATHINFO_FILENAME);

    $finalName = $sanitizedName;
    $filePath = $uploadDir . $finalName;

    // Handle duplicates
    $counter = 1;
    while (file_exists($filePath)) {
        $finalName = $fileNameWithoutExt . '_' . $counter . '.' . $fileExtension;
        $filePath = $uploadDir . $finalName;
        $counter++;
    }

    // Download file
    $fileContent = @file_get_contents($url);
    if ($fileContent === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to download file from URL.']);
        exit;
    }

    if (file_put_contents($filePath, $fileContent) === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file to disk.']);
        exit;
    }

    $relativePath = '/uploads/generated/' . $finalName;

    // Insert into DB
    $stmt = $mysqli->prepare("INSERT INTO images (folder_id, name, path) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $folderId, $finalName, $relativePath);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'File saved successfully.',
            'data' => [
                'path' => $relativePath,
                'name' => $finalName,
                'folder_id' => $folderId,
                'id' => $stmt->insert_id
            ],
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database insertion failed: ' . $stmt->error,
        ]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
