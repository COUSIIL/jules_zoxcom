<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

// Create table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS product_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    model_id INT NOT NULL,
    detail_id INT NULL,
    unique_code VARCHAR(255) NOT NULL UNIQUE,
    order_id INT NULL,
    status ENUM('available', 'sold', 'returned', 'removed') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

if (!$mysqli->query($createTable)) {
    echo json_encode(['success' => false, 'message' => 'Table creation failed: ' . $mysqli->error]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['product_id'], $data['qty'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$product_id = (int)$data['product_id'];
$model_id = isset($data['model_id']) ? (int)$data['model_id'] : 0; // product_models.id
$detail_id = isset($data['detail_id']) ? (int)$data['detail_id'] : null; // model_details.id
$qty = (int)$data['qty'];

if ($qty <= 0) {
    echo json_encode(['success' => false, 'message' => 'Quantity must be positive']);
    exit;
}

// Fetch Names for Code Generation
$modelName = 'Model';
if ($model_id > 0) {
    $stmt = $mysqli->prepare("SELECT name, ref FROM product_models WHERE id = ?");
    $stmt->bind_param("i", $model_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $modelName = !empty($res['name']) ? $res['name'] : (!empty($res['ref']) ? $res['ref'] : 'Model');
    }
    $stmt->close();
}

$variantSuffix = '';
if ($detail_id) {
    $stmt = $mysqli->prepare("SELECT color, size FROM model_details WHERE id = ?");
    $stmt->bind_param("i", $detail_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $parts = [];
        if (!empty($res['colorName'])) $parts[] = $res['colorName'];
        if (!empty($res['size'])) $parts[] = $res['size'];
        if (!empty($parts)) {
            $variantSuffix = '-' . implode('-', $parts);
        }
    }
    $stmt->close();
}

// Generate Codes
$generatedCodes = [];
$mysqli->begin_transaction();

try {
    // Get current count for index
    $countSql = "SELECT COUNT(*) as cnt FROM product_stock WHERE product_id = ? AND model_id = ?";
    if ($detail_id) {
        $countSql .= " AND detail_id = " . $detail_id;
    } else {
        $countSql .= " AND detail_id IS NULL";
    }

    $stmt = $mysqli->prepare($countSql);
    $stmt->bind_param("ii", $product_id, $model_id);
    $stmt->execute();
    $currentCount = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();

    $stmtInsert = $mysqli->prepare("INSERT INTO product_stock (product_id, model_id, detail_id, unique_code, status) VALUES (?, ?, ?, ?, 'available')");

    for ($i = 1; $i <= $qty; $i++) {
        $index = $currentCount + $i;
        $random = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 5);

        // Clean names for code (remove spaces, special chars)
        $cleanModel = preg_replace('/[^A-Za-z0-9]/', '', $modelName);
        $cleanVariant = preg_replace('/[^A-Za-z0-9\-]/', '', $variantSuffix);

        $code = "{$cleanModel}{$cleanVariant}-{$index}-{$random}";

        $bindDetail = $detail_id; // can be null
        $stmtInsert->bind_param("iiis", $product_id, $model_id, $bindDetail, $code);
        $stmtInsert->execute();
        $generatedCodes[] = $code;
    }
    $stmtInsert->close();

    // Update Quantities
    // 1. Update product_models
    $stmtUpdModel = $mysqli->prepare("UPDATE product_models SET quantity = quantity + ? WHERE id = ?");
    $stmtUpdModel->bind_param("ii", $qty, $model_id);
    $stmtUpdModel->execute();
    $stmtUpdModel->close();

    // 2. Update model_details if applicable
    if ($detail_id) {
        $stmtUpdDetail = $mysqli->prepare("UPDATE model_details SET quantity = quantity + ? WHERE id = ?");
        $stmtUpdDetail->bind_param("ii", $qty, $detail_id);
        $stmtUpdDetail->execute();
        $stmtUpdDetail->close();
    }

    $mysqli->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Stock added successfully',
        'codes' => $generatedCodes
    ]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$mysqli->close();
?>
