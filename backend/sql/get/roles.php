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

// Liste des permissions disponibles (hardcoded for UI reference)
$availablePermissions = [
    ['slug' => 'view_dashboard', 'name' => 'Voir le tableau de bord'],
    ['slug' => 'manage_orders', 'name' => 'Gérer les commandes (Ajout/Modif/Suppr)'],
    ['slug' => 'view_orders', 'name' => 'Voir les commandes'],
    ['slug' => 'manage_products', 'name' => 'Gérer les produits'],
    ['slug' => 'manage_users', 'name' => 'Gérer les utilisateurs'],
    ['slug' => 'manage_roles', 'name' => 'Gérer les rôles et permissions'],
    ['slug' => 'view_finance', 'name' => 'Voir la finance'],
    ['slug' => 'manage_settings', 'name' => 'Gérer les paramètres'],
];

echo json_encode([
    'success' => true,
    'data' => [
        'roles' => $roles,
        'availablePermissions' => $availablePermissions
    ]
]);
?>
