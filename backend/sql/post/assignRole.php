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

if (!in_array('manage_users', $perms)) {
    echo json_encode(['success' => false, 'message' => 'Permission refusée.']);
    exit;
}

$userId = $input['user_id'] ?? null;
$roleId = $input['role_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID requis.']);
    exit;
}

$stmt = $mysqli->prepare("UPDATE users SET role_id = ? WHERE id = ?");
$stmt->bind_param("ii", $roleId, $userId);

if ($stmt->execute()) {
    // Legacy update
    if ($roleId) {
        $roleNameRes = $mysqli->query("SELECT name FROM roles WHERE id = $roleId");
        if ($roleNameRes && $row = $roleNameRes->fetch_assoc()) {
            $roleName = $row['name'];
            $stmt2 = $mysqli->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt2->bind_param("si", $roleName, $userId);
            $stmt2->execute();
        }
    } else {
        $stmtDel = $mysqli->prepare("UPDATE users SET role = NULL WHERE id = ?");
        $stmtDel->bind_param("i", $userId);
        $stmtDel->execute();
    }

    echo json_encode(['success' => true, 'message' => 'Rôle assigné avec succès.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur assignation: ' . $mysqli->error]);
}
?>
