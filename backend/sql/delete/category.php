<?php
header("Content-Type: application/json; charset=UTF-8");

// Inclure la configuration de la base de données
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found'
    ]);
    exit;
}

require_once $configPath; // Doit initialiser $mysqli

// Vérifie la connexion
if (!$mysqli || $mysqli->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'connection to database failed: ' . $mysqli->connect_error
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;


if (!$id) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Field "id" is required (JSON body or ?id=).']));
}

// Vérifier l’existence
$stmt = $mysqli->prepare("SELECT id FROM category WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || !$res->num_rows) {
    http_response_code(404);
    exit(json_encode(['success' => false, 'message' => 'Category not found.']));
}
$stmt->close();


// Suppression récursive
function deleteCategoryRecursively(mysqli $mysqli, int $id): void {



    

    $stmt = $mysqli->prepare("SELECT id FROM category WHERE parent_id = ?");
    // Récupérer tous les enfants

    if (!$stmt) {
        throw new Exception("SQL prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Appel récursif

            deleteCategoryRecursively($mysqli, (int)$row['id']);
        }
        $result->free(); // ✅ libérer avant de fermer

    }
    $stmt->close();


    // Supprimer la catégorie courante
    $del = $mysqli->prepare("DELETE FROM category WHERE id = ?");
    if (!$del) {
        throw new Exception("SQL prepare failed: " . $mysqli->error);
    }
    $del->bind_param('i', $id);
    if (!$del->execute()) {
        $err = $del->error ?: 'unknown error';
        $del->close();
        throw new Exception("SQL delete failed: $err");
    }
    $del->close();
}


$mysqli->begin_transaction();
try {
    deleteCategoryRecursively($mysqli, $id);
    $mysqli->commit();
    echo json_encode(['success' => true, 'message' => 'Category and subcategories deleted.']);
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Deletion error: ' . $e->getMessage()]);
} finally {
    $mysqli->close();
}
