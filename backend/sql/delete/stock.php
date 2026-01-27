<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

$data = json_decode(file_get_contents('php://input'), true);

$ids = [];
if (isset($data['ids']) && is_array($data['ids'])) {
    $ids = array_map('intval', $data['ids']);
} elseif (isset($data['id'])) {
    $ids = [(int)$data['id']];
}

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Missing ID or IDs']);
    exit;
}

$mysqli->begin_transaction();

try {
    // 1. Calculate quantities to decrement (only for 'available' items)
    // We group by model and detail to minimize update queries
    $idList = implode(',', $ids);
    $sql = "SELECT model_id, detail_id, COUNT(*) as cnt
            FROM product_stock
            WHERE id IN ($idList) AND status = 'available'
            GROUP BY model_id, detail_id";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) throw new Exception($mysqli->error);

    $stmt->execute();
    $result = $stmt->get_result();
    $groups = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // 2. Update Quantities
    foreach ($groups as $group) {
        $qty = (int)$group['cnt'];
        $model_id = (int)$group['model_id'];
        $detail_id = $group['detail_id'] ? (int)$group['detail_id'] : null;

        // Decrement product_models
        $stmtUpdModel = $mysqli->prepare("UPDATE product_models SET quantity = quantity - ? WHERE id = ?");
        $stmtUpdModel->bind_param("ii", $qty, $model_id);
        $stmtUpdModel->execute();
        $stmtUpdModel->close();

        // Decrement model_details if exists
        if ($detail_id) {
            $stmtUpdDetail = $mysqli->prepare("UPDATE model_details SET quantity = quantity - ? WHERE id = ?");
            $stmtUpdDetail->bind_param("ii", $qty, $detail_id);
            $stmtUpdDetail->execute();
            $stmtUpdDetail->close();
        }
    }

    // 3. Delete Rows
    $sqlDelete = "DELETE FROM product_stock WHERE id IN ($idList)";
    if (!$mysqli->query($sqlDelete)) {
        throw new Exception($mysqli->error);
    }

    $mysqli->commit();
    echo json_encode(['success' => true, 'message' => 'Stock removed successfully']);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$mysqli->close();
?>
