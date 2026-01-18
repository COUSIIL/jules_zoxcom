<?php
header("Content-Type: application/json; charset=UTF-8");
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
require_once $configPath;

// Full list of permissions
$permissions = [
    'view_dashboard',
    'read_orders', 'create_orders', 'edit_orders', 'delete_orders',
    'read_products', 'create_products', 'edit_products', 'delete_products',
    'read_customers', 'create_customers', 'edit_customers', 'delete_customers',
    'read_users', 'create_users', 'edit_users', 'delete_users', 'assign_roles',
    'read_roles', 'create_roles', 'edit_roles', 'delete_roles',
    'read_delivery', 'create_delivery', 'edit_delivery', 'delete_delivery',
    'view_finance', 'view_analytics',
    'manage_settings',
    // Legacy
    'manage_orders', 'view_orders', 'manage_products', 'manage_users', 'manage_roles'
];

// Get Admin Role ID
$res = $mysqli->query("SELECT id FROM roles WHERE name = 'Admin'");
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Admin role not found']);
    exit;
}
$adminId = $res->fetch_assoc()['id'];

$stmt = $mysqli->prepare("INSERT INTO role_permissions (role_id, permission_slug) VALUES (?, ?)");

$count = 0;
foreach ($permissions as $slug) {
    $check = $mysqli->query("SELECT id FROM role_permissions WHERE role_id = $adminId AND permission_slug = '$slug'");
    if ($check->num_rows == 0) {
        $stmt->bind_param("is", $adminId, $slug);
        $stmt->execute();
        $count++;
    }
}

echo json_encode(['success' => true, 'message' => "Updated Admin with $count new permissions."]);
?>
