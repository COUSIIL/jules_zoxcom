<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../../backend/config/dbConfig.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['login'], $data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing login or password']);
    exit;
}

$login = $mysqli->real_escape_string($data['login']); // Can be phone or email
$password = $data['password'];

$query = "SELECT * FROM customers WHERE phone = '$login' OR email = '$login'";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        // Generate new token or keep old? Let's refresh it for security or just return existing.
        // Usually better to refresh.
        $newToken = bin2hex(random_bytes(32));
        $update = $mysqli->query("UPDATE customers SET token = '$newToken' WHERE id = " . $user['id']);

        // Remove password from response
        unset($user['password']);
        $user['token'] = $newToken;

        echo json_encode(['success' => true, 'message' => 'Login successful', 'data' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}

$mysqli->close();
?>
