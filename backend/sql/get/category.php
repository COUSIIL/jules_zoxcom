<?php
header('Content-Type: application/json; charset=UTF-8');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Configuration file not found.']));
}
require_once $configPath;

/** Création de la table si nécessaire (uniquement une fois) **/
$tableQuery = "CREATE TABLE IF NOT EXISTS category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    name VARCHAR(255)        NOT NULL,
    slug VARCHAR(255)        NOT NULL UNIQUE,
    level ENUM('meta', 'branch', 'leaf') NOT NULL DEFAULT 'leaf',
    description TEXT         NULL,
    parent_id INT            NULL,
    image TEXT               NULL,
    sustainable TINYINT(1)    NOT NULL DEFAULT 0,
    facets JSON              NULL,
    meta_title VARCHAR(255)  NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
if ($mysqli->query($tableQuery) === false) {
    http_response_code(500);
    exit(json_encode(['success'=>false,'message'=>'Error creating table: '.$mysqli->error]));
}

// Lecture de l'action et des paramètres
$id     = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;
$input  = json_decode(file_get_contents('php://input'), true);

// ---------- GET CATEGORY ----------
if ($id !== null) {
    // Récupérer une seule catégorie
    $stmt = $mysqli->prepare("SELECT * FROM category WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($cat = $result->fetch_assoc()) {
        // Décoder le JSON des facets
        $cat['facets'] = json_decode($cat['facets'], true);
        echo json_encode(['success'=>true, 'category'=>$cat]);
    } else {
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Category not found.']);
    }
    $stmt->close();
} else {
    // Récupérer toutes les catégories
    $res = $mysqli->query("SELECT * FROM category ORDER BY parent_id ASC, name ASC");
    $cats = [];
    while ($row = $res->fetch_assoc()) {
        $row['facets'] = json_decode($row['facets'], true);
        $cats[] = $row;
    }
    echo json_encode(['success'=>true, 'categories'=>$cats]);
}
$mysqli->close();
exit;