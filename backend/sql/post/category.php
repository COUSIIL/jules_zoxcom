<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Chargement config MySQL
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'DB config not found.']));
}
require_once $configPath; // $mysqli

// 1. Création de la table
$tableSQL = <<<SQL
CREATE TABLE IF NOT EXISTS category (
  id INT AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  name VARCHAR(255)         NOT NULL,
  slug VARCHAR(255)         NOT NULL UNIQUE,
  level ENUM('meta', 'branch', 'leaf') NOT NULL DEFAULT 'leaf',
  description TEXT          NULL,
  parent_id INT             NULL,
  image TEXT                NOT NULL,
  sustainable TINYINT(1)    NOT NULL DEFAULT 0,
  facets JSON               NULL,
  meta_title VARCHAR(255)   NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if ($mysqli->query($tableSQL) === false) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Create table error: ' . $mysqli->error]));
}

// 2. Lecture JSON
$input = json_decode(file_get_contents('php://input'), true);
foreach (['name', 'slug', 'image'] as $f) {
    if (empty($input[$f])) {
        http_response_code(400);
        exit(json_encode(['success' => false, 'message' => "Field \"$f\" is required."]));
    }
}

// 3. Préparation des valeurs
$name        = trim($input['name']);
$slug        = trim($input['slug']);
$description = $input['description'] ?? null;
$parent_id   = is_numeric($input['parent_id'] ?? null) ? intval($input['parent_id']) : null;
$image       = trim($input['image']);
$sustainable = !empty($input['sustainable']) ? 1 : 0;
$facets      = isset($input['facets']) ? json_encode($input['facets'], JSON_UNESCAPED_UNICODE) : null;
$meta_title  = $input['meta_title'] ?? null;
$level       = in_array($input['level'] ?? '', ['meta', 'branch', 'leaf']) ? $input['level'] : 'leaf';

// 4. Vérification d'existence
$stmt = $mysqli->prepare("SELECT id FROM category WHERE slug = ?");
$stmt->bind_param('s', $slug);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    // 5a. UPDATE
    $catId = intval($row['id']);
    $stmt->close();
    $upd = $mysqli->prepare(<<<SQL
      UPDATE category SET
        name        = ?,
        level       = ?,
        description = ?,
        parent_id   = ?,
        image       = ?,
        sustainable = ?,
        facets      = ?,
        meta_title  = ?
      WHERE id = ?
    SQL
    );
    $upd->bind_param(
        'sssissisi',
        $name, $level, $description, $parent_id,
        $image, $sustainable, $facets,
        $meta_title, $catId
    );
    if ($upd->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Category updated.',
            'id' => $catId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Update error: ' . $upd->error]);
    }
    $upd->close();
} else {
    // 5b. INSERT
    $stmt->close();
    $ins = $mysqli->prepare(<<<SQL
      INSERT INTO category
        (name, slug, level, description, parent_id, image, sustainable, facets, meta_title)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    SQL
    );
    $ins->bind_param(
        'sssississ',
        $name, $slug, $level, $description, $parent_id,
        $image, $sustainable, $facets, $meta_title
    );
    if ($ins->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'New category created.',
            'id' => $ins->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Insert error: ' . $ins->error]);
    }
    $ins->close();
}

$mysqli->close();
