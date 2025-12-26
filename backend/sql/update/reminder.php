<?php
header("Content-Type: application/json; charset=UTF-8");

// Inclure le fichier de configuration
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

if (!$mysqli) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

// Vérifier si la table existe
$table_check_query = "SHOW TABLES LIKE 'reminder'";
$table_check_result = $mysqli->query($table_check_query);
if ($table_check_result === false) {
    echo json_encode(['success' => false, 'message' => 'Error checking table: ' . $mysqli->error]);
    exit;
}
if ($table_check_result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Table reminder does not exist.']);
    exit;
}

// Lire les données JSON
$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? null;
$note = $data['note'] ?? '';
$reminder_date = $data['reminder_date'] ?? '';
$work = isset($data['work']) ? (int)$data['work'] : null;

// Validation minimale
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing reminder ID.']);
    exit;
}

// Mettre à jour les champs fournis
$fields = [];
$params = [];
$types = "";

if (!empty($reminder_date)) {
    $fields[] = "reminder_date = ?";
    $params[] = $reminder_date;
    $types .= "s";
}
if (!empty($note)) {
    $fields[] = "note = ?";
    $params[] = $note;
    $types .= "s";
}
if ($work !== null) {
    $fields[] = "work = ?";
    $params[] = $work;
    $types .= "i";
}

if (empty($fields)) {
    echo json_encode(['success' => false, 'message' => 'No fields to update.']);
    exit;
}

$params[] = $id;
$types .= "i";

$query = "UPDATE `reminder` SET " . implode(", ", $fields) . " WHERE id = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Reminder updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No reminder found or no changes made.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating reminder: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
