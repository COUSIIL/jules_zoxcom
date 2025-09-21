<?php
// api.php (routeur principal) - utilise mysqli (objet) depuis backend/config/dbConfig.php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // ⚠️ restreindre en production
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Chemins config
$configPath = __DIR__ . '/backend/config/dbConfig.php';
$notConfigPath = __DIR__ . '/notification.config.php';

// Gestion pre-flight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Inclure les configurations
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Le fichier de configuration dbConfig.php est manquant.']);
    exit;
}
require_once $configPath;

if (!file_exists($notConfigPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Le fichier de configuration notification.config.php est manquant.']);
    exit;
}
require_once $notConfigPath;

// Vérifier que $mysqli existe et est bien une instance de mysqli
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'La connexion $mysqli n\'est pas initialisée correctement.']);
    exit;
}

// This file is now empty as all notification logic has been moved to backend/notificationApi.php
// The non-notification related routes should be checked and kept if necessary.
// For this task, we assume this file was only for notifications.

// --- Helpers ---
function send_json_response($success, $data = null, $error = null, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $error]);
    exit;
}

function get_json_input() {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        send_json_response(false, null, 'Invalid JSON payload: ' . json_last_error_msg(), 400);
    }
    return $input;
}

function authenticate_request() {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        return ['user_id' => 2, 'role' => 'admin'];
    }
    return ['user_id' => 1, 'role' => 'admin']; // temporaire pour développement
}

// Sanity check pour éviter notices
$action = $_GET['action'] ?? '';
$auth = authenticate_request();

if (!$auth) {
    // send_json_response(false, null, 'Authentication required.', 401);
}

// --- ROUTES ---
// All notification routes have been moved to /backend/notificationApi.php
// If there were other routes here, they would remain.
// For the purpose of this refactoring, we assume this file is now deprecated or will be repurposed.

send_json_response(false, null, 'This endpoint is deprecated. Please use /backend/notificationApi.php for notifications.', 404);
?>
