<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Vérifier/Créer les tables
$createTables = [
    "CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        description VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS role_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_id INT NOT NULL,
        permission_slug VARCHAR(100) NOT NULL,
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
    )"
];

foreach ($createTables as $query) {
    if (!$mysqli->query($query)) {
        echo json_encode(["success" => false, "message" => "Erreur SQL (Table Creation): " . $mysqli->error]);
        exit;
    }
}

// Ajouter la colonne role_id à users si elle n'existe pas
// Note: On garde la colonne 'role' existante pour compatibilité legacy si besoin, mais on va privilégier role_id.
// Si 'role' est un varchar, on peut peut-être le migrer plus tard. Pour l'instant on ajoute role_id.
$check = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'role_id'");
if ($check && $check->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN role_id INT NULL AFTER role");
    $mysqli->query("ALTER TABLE users ADD CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL");
}

// Récupérer les rôles et leurs permissions
$query = "SELECT r.id, r.name, r.description, GROUP_CONCAT(rp.permission_slug) as permissions
          FROM roles r
          LEFT JOIN role_permissions rp ON r.id = rp.role_id
          GROUP BY r.id";

$result = $mysqli->query($query);
if (!$result) {
    echo json_encode(["success" => false, "message" => "Erreur SQL: " . $mysqli->error]);
    exit;
}

$roles = [];
while ($row = $result->fetch_assoc()) {
    $row['permissions'] = $row['permissions'] ? explode(',', $row['permissions']) : [];
    $roles[] = $row;
}

// Liste des permissions disponibles
$availablePermissions = [
    // Dashboard
    ['slug' => 'view_dashboard', 'name' => 'Voir le tableau de bord'],

    // Commandes
    ['slug' => 'read_orders', 'name' => 'Voir les commandes'],
    ['slug' => 'create_orders', 'name' => 'Créer des commandes'],
    ['slug' => 'edit_orders', 'name' => 'Modifier les commandes'],
    ['slug' => 'delete_orders', 'name' => 'Supprimer les commandes'],

    // Produits
    ['slug' => 'read_products', 'name' => 'Voir les produits'],
    ['slug' => 'create_products', 'name' => 'Créer des produits'],
    ['slug' => 'edit_products', 'name' => 'Modifier les produits'],
    ['slug' => 'delete_products', 'name' => 'Supprimer les produits'],

    // Clients
    ['slug' => 'read_customers', 'name' => 'Voir les clients'],
    ['slug' => 'create_customers', 'name' => 'Créer des clients'],
    ['slug' => 'edit_customers', 'name' => 'Modifier les clients'],
    ['slug' => 'delete_customers', 'name' => 'Supprimer les clients'],

    // Utilisateurs (Team)
    ['slug' => 'read_users', 'name' => 'Voir les utilisateurs'],
    ['slug' => 'create_users', 'name' => 'Créer des utilisateurs'],
    ['slug' => 'edit_users', 'name' => 'Modifier les utilisateurs'],
    ['slug' => 'delete_users', 'name' => 'Supprimer les utilisateurs'],
    ['slug' => 'assign_roles', 'name' => 'Assigner des rôles'],

    // Rôles
    ['slug' => 'read_roles', 'name' => 'Voir les rôles'],
    ['slug' => 'create_roles', 'name' => 'Créer des rôles'],
    ['slug' => 'edit_roles', 'name' => 'Modifier les rôles'],
    ['slug' => 'delete_roles', 'name' => 'Supprimer les rôles'],

    // Livraison
    ['slug' => 'read_delivery', 'name' => 'Voir la livraison'],
    ['slug' => 'create_delivery', 'name' => 'Créer des méthodes de livraison'],
    ['slug' => 'edit_delivery', 'name' => 'Modifier la livraison'],
    ['slug' => 'delete_delivery', 'name' => 'Supprimer la livraison'],

    // Finance/Analytics
    ['slug' => 'view_finance', 'name' => 'Voir la finance'],
    ['slug' => 'view_analytics', 'name' => 'Voir les statistiques'],

    // Paramètres
    ['slug' => 'manage_settings', 'name' => 'Gérer les paramètres'],

    // Legacy (Backward Compatibility)
    ['slug' => 'manage_orders', 'name' => 'Gérer les commandes (Legacy)'],
    ['slug' => 'view_orders', 'name' => 'Voir les commandes (Legacy)'],
    ['slug' => 'manage_products', 'name' => 'Gérer les produits (Legacy)'],
    ['slug' => 'manage_users', 'name' => 'Gérer les utilisateurs (Legacy)'],
    ['slug' => 'manage_roles', 'name' => 'Gérer les rôles (Legacy)'],

    ['slug' => 'all_permissions', 'name' => 'Accées total'],
];

echo json_encode([
    'success' => true,
    'data' => [
        'roles' => $roles,
        'availablePermissions' => $availablePermissions
    ]
]);
?>
