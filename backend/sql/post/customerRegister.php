<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../../backend/config/dbConfig.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['name'], $data['phone'], $data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$name = $mysqli->real_escape_string($data['name']);
$phone = $mysqli->real_escape_string($data['phone']);
$email = isset($data['email']) ? $mysqli->real_escape_string($data['email']) : null;
$wilaya = isset($data['wilaya']) ? $mysqli->real_escape_string($data['wilaya']) : '';
$commune = isset($data['commune']) ? $mysqli->real_escape_string($data['commune']) : '';
$address = isset($data['address']) ? $mysqli->real_escape_string($data['address']) : '';
$password = password_hash($data['password'], PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));

// Check if customer exists by phone
$check = $mysqli->query("SELECT id, email, password FROM customers WHERE phone = '$phone'");
if ($check->num_rows > 0) {
    $row = $check->fetch_assoc();
    if (!empty($row['password'])) {
        echo json_encode(['success' => false, 'message' => 'Account already exists for this phone number']);
        exit;
    }
    // Update existing customer (who was created via order)
    $stmt = $mysqli->prepare("UPDATE customers SET name=?, email=?, password=?, token=?, wilaya=?, commune=?, address=? WHERE id=?");
    $stmt->bind_param("sssssssi", $name, $email, $password, $token, $wilaya, $commune, $address, $row['id']);
} else {
    // Check if email exists separately if provided
    if ($email) {
         $checkEmail = $mysqli->query("SELECT id FROM customers WHERE email = '$email'");
         if ($checkEmail->num_rows > 0) {
             echo json_encode(['success' => false, 'message' => 'Email already used']);
             exit;
         }
    }

    // Create new customer
    $stmt = $mysqli->prepare("INSERT INTO customers (name, phone, email, password, token, wilaya, commune, address, power) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("ssssssss", $name, $phone, $email, $password, $token, $wilaya, $commune, $address);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Account created', 'token' => $token]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}
$stmt->close();
$mysqli->close();
?>
