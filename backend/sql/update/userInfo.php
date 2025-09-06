<?php
header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Récupération de la requête (POST classique ou JSON)

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$name = trim($input['firstname'] ?? '');
$family_name = trim($input['lastname'] ?? '');
$role = trim($input['role'] ?? '');
$description = trim($input['description'] ?? '');


// Vérification ID
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

// Vérifier si l'utilisateur existe
$stmt = $mysqli->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

// Construction dynamique de la requête UPDATE
$fields = [];
$params = [];
$types = "";

// Ajout des champs seulement s'ils sont fournis
if (!empty($username)) {
    $fields[] = "username = ?";
    $params[] = $username;
    $types .= "s";
}
if (!empty($email)) {
    $fields[] = "email = ?";
    $params[] = $email;
    $types .= "s";
}
if (!empty($name)) {
    $fields[] = "name = ?";
    $params[] = $name;
    $types .= "s";
}
if (!empty($family_name)) {
    $fields[] = "family_name = ?";
    $params[] = $family_name;
    $types .= "s";
}
if (!empty($role)) {
    $fields[] = "role = ?";
    $params[] = $role;
    $types .= "s";
}
if (!empty($description)) {
    $fields[] = "description = ?";
    $params[] = $description;
    $types .= "s";
}

// Si aucun champ à mettre à jour
if (empty($fields)) {
    echo json_encode(['success' => false, 'message' => 'No fields provided for update.']);
    exit;
}

$params[] = $id;
$types .= "i";

// Préparation de la requête
$query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param($types, ...$params);

// Exécution
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}
