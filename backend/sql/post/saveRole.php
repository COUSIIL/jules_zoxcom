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

$id = $input['id'] ?? null;

$requiredPerm = $id ? 'edit_roles' : 'create_roles';

if (!in_array($requiredPerm, $perms) && !in_array('manage_roles', $perms)) {
    echo json_encode(['success' => false, 'message' => 'Permission refusée.']);
    exit;
}
$name = trim($input['name'] ?? '');
$description = trim($input['description'] ?? '');
$permissions = $input['permissions'] ?? [];

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Nom du rôle requis.']);
    exit;
}

if ($id) {
    // Update
    $stmt = $mysqli->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $description, $id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Erreur update rôle: ' . $mysqli->error]);
        exit;
    }
} else {
    // Create
    $check = $mysqli->prepare("SELECT id FROM roles WHERE name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Un rôle avec ce nom existe déjà.']);
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Erreur création rôle: ' . $mysqli->error]);
        exit;
    }
    $id = $mysqli->insert_id;
}

// Permissions
$delStmt = $mysqli->prepare("DELETE FROM role_permissions WHERE role_id = ?");
$delStmt->bind_param("i", $id);
$delStmt->execute();

if (!empty($permissions)) {
    $stmt = $mysqli->prepare("INSERT INTO role_permissions (role_id, permission_slug) VALUES (?, ?)");
    foreach ($permissions as $slug) {
        $stmt->bind_param("is", $id, $slug);
        $stmt->execute();
    }
}

echo json_encode(['success' => true, 'message' => 'Rôle enregistré avec succès.', 'data' => ['id' => $id]]);
?>
