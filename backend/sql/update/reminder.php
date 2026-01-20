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
$note_raw = $data['note'] ?? null;
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

if ($note_raw !== null) {
    if (is_array($note_raw) || is_object($note_raw)) {
        $note = json_encode($note_raw, JSON_UNESCAPED_UNICODE);
    } else {
        $note = (string)$note_raw;
    }

    // On ne met à jour que si la note n'est pas vide (ou on autorise vide ?)
    // Supposons qu'on veut mettre à jour même si c'est vide si c'est passé explicitement
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
        // Fetch updated data to return
        $fetchStmt = $mysqli->prepare("
            SELECT r.*, u.username, u.profile_image as user_image, o.id as order_id
            FROM `reminder` r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN orders o ON o.reminder_id = r.id
            WHERE r.id = ?
        ");
        $fetchStmt->bind_param("i", $id);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();
        $updatedData = $result->fetch_assoc();

        echo json_encode(['success' => true, 'message' => 'Reminder updated successfully.', 'data' => $updatedData]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No reminder found or no changes made.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating reminder: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
