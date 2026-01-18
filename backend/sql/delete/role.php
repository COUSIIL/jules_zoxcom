<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
require_once $configPath;

$input = json_decode(file_get_contents('php://input'), true);

$token = $input['token'] ?? '';
if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Token requis.']);
    exit;
}

// Verify permission
$stmt = $mysqli->prepare("
    SELECT rp.permission_slug
    FROM users u
    JOIN role_permissions rp ON u.role_id = rp.role_id
    WHERE u.token = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
$perms = [];
while ($row = $res->fetch_assoc()) {
    $perms[] = $row['permission_slug'];
}

if (!in_array('manage_roles', $perms)) {
    echo json_encode(['success' => false, 'message' => 'Permission refusée.']);
    exit;
}

$id = $input['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID requis.']);
    exit;
}

// Check if trying to delete Admin role
$res = $mysqli->query("SELECT name FROM roles WHERE id = $id");
if ($res && $row = $res->fetch_assoc()) {
    if ($row['name'] === 'Admin') {
        echo json_encode(['success' => false, 'message' => 'Impossible de supprimer le rôle Admin.']);
        exit;
    }
}

$stmt = $mysqli->prepare("DELETE FROM roles WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Rôle supprimé.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur suppression: ' . $mysqli->error]);
}
?>
