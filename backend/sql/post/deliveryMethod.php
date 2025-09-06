<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once __DIR__ . '/../../../backend/config/dbConfig.php';

// Lecture des données JSON POST
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    response(false, 'Invalid JSON data');
}

// Extraction sécurisée
$method         = $mysqli->real_escape_string($data['method']         ?? '');
$deliveryName   = $mysqli->real_escape_string($data['delivery_name']  ?? '');
$dropAreaName   = $mysqli->real_escape_string($data['drop_area_name'] ?? '');
$dropAreaId     = isset($data['drop_area_id'])   ? (int)$data['drop_area_id'] : null;
$returnFree     = !empty($data['return_free'])   ? 1 : 0;
$includeFees    = !empty($data['include_fees'])  ? 1 : 0;
$deliveryInfo = $data['delivery_info'];      // déjà un array PHP
$deliveryContent = $data['delivery_content'];
$createdAt      = date('Y-m-d H:i:s');

// Validation minimale
if (!$method || !$deliveryName || !$dropAreaName || $dropAreaId === null) {
    response(false, 'Missing required fields');
}

// Création de la table si elle n’existe pas, avec un champ JSON pour delivery_info
$createTable = "
CREATE TABLE IF NOT EXISTS deliver_method (
    id INT AUTO_INCREMENT PRIMARY KEY,
    method VARCHAR(100) NOT NULL UNIQUE,
    delivery_name VARCHAR(255) NOT NULL,
    drop_area_name VARCHAR(255) NOT NULL,
    drop_area_id INT NOT NULL,
    return_free TINYINT(1) DEFAULT 0,
    include_fees TINYINT(1) DEFAULT 0,
    delivery_info JSON NULL,
    delivery_content JSON NULL,
    active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (!$mysqli->query($createTable)) {
    response(false, 'Failed to create table: ' . $mysqli->error);
}

$alters = [
  "ALTER TABLE deliver_method ADD COLUMN IF NOT EXISTS delivery_info JSON NULL AFTER include_fees",
  "ALTER TABLE deliver_method ADD COLUMN IF NOT EXISTS delivery_content JSON NULL AFTER delivery_info"
];

foreach ($alters as $sql) {
    if (! $mysqli->query($sql)) {
        // Si ta version de MySQL ne supporte pas IF NOT EXISTS, ou si 
        // la colonne existe déjà, tu peux vérifier le code d’erreur 1060
        if ($mysqli->errno === 1060) {
            continue;
        }
        response(false, "Migration failed: " . $mysqli->error, 500);
    }
}

// Vérifie si la méthode existe déjà
$check = $mysqli->prepare("SELECT id FROM deliver_method WHERE method = ?");
$check->bind_param("s", $method);
$check->execute();
$result = $check->get_result();
$exists = $result->num_rows > 0;
$check->close();

if ($exists) {
    // Mettre à jour l'enregistrement existant
    $sql = "
      UPDATE deliver_method
      SET delivery_name   = ?,
          drop_area_name  = ?,
          drop_area_id    = ?,
          return_free     = ?,
          include_fees    = ?,
          delivery_info   = ?,
          delivery_content= ?,
          created_at      = ?
      WHERE method = ?
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param(
        "ssiiissss",
        $deliveryName,
        $dropAreaName,
        $dropAreaId,
        $returnFree,
        $includeFees,
        $deliveryInfo,
        $deliveryContent,
        $createdAt,
        $method
    );
} else {
    // Insérer un nouvel enregistrement
    $sql = "
      INSERT INTO deliver_method (
        method,
        delivery_name,
        drop_area_name,
        drop_area_id,
        return_free,
        include_fees,
        delivery_info,
        delivery_content,
        created_at
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param(
        "sssiiisss",
        $method,
        $deliveryName,
        $dropAreaName,
        $dropAreaId,
        $returnFree,
        $includeFees,
        $deliveryInfo,
        $deliveryContent,
        $createdAt
    );
}

if ($stmt->execute()) {
    $message = $exists
      ? 'Delivery method updated successfully'
      : 'Delivery method inserted successfully';
    response(true, $message);
} else {
    response(false, 'Database error: ' . $stmt->error);
}

/**
 * Renvoie une réponse JSON et quitte.
 */
function response($success, $message)
{
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}
