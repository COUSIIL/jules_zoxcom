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

if (!in_array('delete_users', $perms) && !in_array('manage_users', $perms)) {
    echo json_encode(['success' => false, 'message' => 'Permission refusée.']);
    exit;
}

$id = $input['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID requis.']);
    exit;
}

// Check if user exists and is not an admin (optional, prevent deleting oneself or main admin)
$stmt = $mysqli->prepare("SELECT id, username FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
    exit;
}
$targetUser = $res->fetch_assoc();

// Optional: Prevent deleting self
$stmtSelf = $mysqli->prepare("SELECT id FROM users WHERE token = ?");
$stmtSelf->bind_param("s", $token);
$stmtSelf->execute();
$resSelf = $stmtSelf->get_result();
$currentUser = $resSelf->fetch_assoc();

if ($currentUser && $currentUser['id'] == $id) {
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.']);
    exit;
}

$stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur suppression: ' . $mysqli->error]);
}
?>
