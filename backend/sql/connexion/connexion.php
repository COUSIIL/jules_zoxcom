<?php
header("Content-Type: application/json; charset=UTF-8");
$input = json_decode(file_get_contents("php://input"), true);

$secret = "6LeAL7QrAAAAABBJHVja_OyzbcOYq2ZDJVdcMAZl";
$recaptcha = $input['recaptcha'] ?? null;

if (!$recaptcha) {
    echo json_encode(["success" => false, "message" => "reCAPTCHA manquant"]);
    exit;
}

$ch = curl_init("https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'secret' => $secret,
    'response' => $recaptcha
]);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur cURL",
        "error" => curl_error($ch)
    ]);
    exit;
}
curl_close($ch);

$captchaSuccess = json_decode($response, true);

if ($captchaSuccess && !$captchaSuccess['success']) {
    echo json_encode([
        "success" => false,
        "message" => $captchaSuccess ?? "error reCAPTCHA",
    ]);
    exit;
}

// reCAPTCHA v3 donne aussi un score (0.0 = bot, 1.0 = humain)
if ($captchaSuccess['score'] < 0.5) {
    echo json_encode(["success" => false, "message" => "Score reCAPTCHA trop bas"]);
    exit;
}

// 📌 Inclure la config DB
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// 📌 Créer la table si elle n'existe pas
$createTable = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    ip_adresse VARCHAR(45),
    name VARCHAR(255),
    family_name VARCHAR(255),
    role VARCHAR(50),
    profile_image VARCHAR(255) DEFAULT NULL,
    token VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    description VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$mysqli->query($createTable)) {
    echo json_encode(['success' => false, 'message' => 'Error creating table: ' . $mysqli->error]);
    exit;
}

// 📌 SETUP ROLES & PERMISSIONS
$mysqli->query("CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$mysqli->query("CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_slug VARCHAR(100) NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
)");

$check = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'role_id'");
if ($check && $check->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN role_id INT NULL AFTER role");
    $mysqli->query("ALTER TABLE users ADD CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL");
}

// Auto-create Admin role if empty
$checkRoles = $mysqli->query("SELECT COUNT(*) as total FROM roles");
$totalRoles = $checkRoles->fetch_assoc()['total'];
$adminRoleId = null;

if ($totalRoles == 0) {
    $stmt = $mysqli->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
    $name = 'Admin';
    $desc = 'Administrateur avec tous les droits';
    $stmt->bind_param("ss", $name, $desc);
    $stmt->execute();
    $adminRoleId = $mysqli->insert_id;

    $allPerms = [
        'view_dashboard', 'manage_orders', 'view_orders', 'manage_products',
        'manage_users', 'manage_roles', 'view_finance', 'manage_settings'
    ];
    $stmtPerm = $mysqli->prepare("INSERT INTO role_permissions (role_id, permission_slug) VALUES (?, ?)");
    foreach ($allPerms as $slug) {
        $stmtPerm->bind_param("is", $adminRoleId, $slug);
        $stmtPerm->execute();
    }
} else {
    $res = $mysqli->query("SELECT id FROM roles WHERE name = 'Admin'");
    if ($res && $row = $res->fetch_assoc()) {
        $adminRoleId = $row['id'];
    }
}

// 📌 Lire les données envoyées

if (!isset($input['username']) || !isset($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Username/email and password are required.']);
    exit;
}

$usernameOrEmail = trim($input['username']);
$password = trim($input['password']);
if (empty($usernameOrEmail) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Empty username/email or password.']);
    exit;
}

// 📌 Vérifier si la table contient déjà un utilisateur
$checkUsers = $mysqli->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $checkUsers->fetch_assoc()['total'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$hashedPassword2 = hash('sha256', $password);
$token = hash('sha256', time() . $usernameOrEmail);

if ($totalUsers == 0) {
    // 📌 Pas d'utilisateur → création automatique
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password, token) VALUES (?, ?, ?, ?)");
    // Si l'entrée contient un @ → on considère que c'est l'email
    if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
        $stmt->bind_param("ssss", $usernameOrEmail, $usernameOrEmail, $hashedPassword, $token);
    } else {
        // Pas d'email → email vide
        $emptyEmail = '';
        $stmt->bind_param("ssss", $usernameOrEmail, $emptyEmail, $hashedPassword, $token);
    }
    if ($stmt->execute()) {
        // Assign Admin Role to this first user
        $newUserId = $mysqli->insert_id;
        if ($adminRoleId) {
            $mysqli->query("UPDATE users SET role_id = $adminRoleId WHERE id = $newUserId");
        }

        // Fetch perms (all admin perms)
        $permissions = [];
        if ($adminRoleId) {
            $pRes = $mysqli->query("SELECT permission_slug FROM role_permissions WHERE role_id = $adminRoleId");
            while ($pRow = $pRes->fetch_assoc()) {
                $permissions[] = $pRow['permission_slug'];
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'First user created successfully and logged in.',
            'data' => [
                'token' => $token,
                'user' => $usernameOrEmail,
                'permissions' => $permissions
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating first user: ' . $mysqli->error]);
    }
    exit;
}

// 📌 Connexion avec username OU email
$stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    $validPassword = false;
    if (password_verify($password, $user['password'])) {
        $validPassword = true;
    } elseif ($user['password'] === $hashedPassword) {
        $validPassword = true;
    } elseif ($user['password'] === $hashedPassword2) {
        $validPassword = true;
    }

    if ($validPassword) {
        // Auto-assign Admin if this is the first user with a role (or system has no role assigned users yet)
        if (empty($user['role_id']) && $adminRoleId) {
             $checkUsersWithRole = $mysqli->query("SELECT COUNT(*) as total FROM users WHERE role_id IS NOT NULL");
             if ($checkUsersWithRole->fetch_assoc()['total'] == 0) {
                 $mysqli->query("UPDATE users SET role_id = $adminRoleId WHERE id = " . $user['id']);
                 $user['role_id'] = $adminRoleId;
             }
        }

        // Fetch permissions
        $permissions = [];
        if (!empty($user['role_id'])) {
            $pRes = $mysqli->query("SELECT permission_slug FROM role_permissions WHERE role_id = " . $user['role_id']);
            while ($pRow = $pRes->fetch_assoc()) {
                $permissions[] = $pRow['permission_slug'];
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Connexion successful',
            'data' => [
                'token' => $user['token'],
                'user' => $user['username'], 
                'profile_image' => $user['profile_image'] ?? null,
                'permissions' => $permissions
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Wrong password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
?>
