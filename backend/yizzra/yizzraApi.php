<?php
// yizzraApi.php - Router pour le scraper Instagram (adapté au pattern de api.php)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Exécute un script PHP en l'incluant et retourne le JSON décodé.
 * Permet d'injecter des variables GET/POST pour le script inclus.
 *
 * @param string $path Chemin vers le fichier à inclure
 * @param array $getVars  Variables $_GET à fournir au script inclus
 * @param array $postVars Variables $_POST à fournir au script inclus
 * @return array|null    Tableau décodé ou null si JSON invalide
 */
function runScriptWithParams($path, $getVars = [], $postVars = []) {
    // Sauvegarde
    $oldGet = $_GET;
    $oldPost = $_POST;

    // Injecte
    $_GET = array_merge($_GET, $getVars);
    $_POST = array_merge($_POST, $postVars);

    ob_start();
    try {
        include $path;
    } catch (Throwable $t) {
        ob_end_clean();
        // restaure avant de lever
        $_GET = $oldGet;
        $_POST = $oldPost;
        throw $t;
    }
    $output = ob_get_clean();

    // Restaure
    $_GET = $oldGet;
    $_POST = $oldPost;

    // Tente de décoder le JSON produit
    $decoded = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $decoded;
    }

    // Si la sortie n'est pas JSON, on retourne la sortie brute dans un tableau
    return ['raw_output' => $output];
}

/**
 * Routes simples pour exposer le scraper
 * action = scrapInstagram  -> inclut yizzraInstagramV1.php
 */
$routes = [
    'scrapInstagram' => __DIR__ . '/yizzraInstagramV1.php',
    'yizzra'         => __DIR__ . '/yizzraInstagramV1.php',
    // tu peux ajouter d'autres alias ici si nécessaire
];

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing "action" parameter.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if (!isset($routes[$action])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Unknown action: ' . $action], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Récupère keyword depuis GET ou POST
$keyword = $_GET['keyword'] ?? $_POST['keyword'] ?? null;
if (!$keyword || trim($keyword) === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing or empty "keyword" parameter.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    // Appelle le script en lui passant $_GET['keyword']
    $response = runScriptWithParams($routes[$action], ['keyword' => $keyword], []);

    // Si le script a renvoyé null ou vide, on renvoie une erreur
    if ($response === null || $response === []) {
        echo json_encode(['status' => 'error', 'message' => 'No output from scraper or invalid JSON.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // Normalise la réponse pour retourner un objet JSON propre
    echo json_encode(['status' => 'success', 'data' => $response], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Exception: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
