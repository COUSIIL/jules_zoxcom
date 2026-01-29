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

$productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$deleteAll = isset($data['delete_all']) && $data['delete_all'] === true;

if (empty($ids) && (!$deleteAll || $productId <= 0)) {
    echo json_encode(['success' => false, 'message' => 'Missing ID or IDs, or invalid Product ID for bulk delete']);
    exit;
}

$mysqli->begin_transaction();

try {
    $groups = [];

    if ($deleteAll && $productId > 0) {
        // Bulk Delete Logic: Reset quantities to 0 and Delete Rows
        $stmtResetModels = $mysqli->prepare("UPDATE product_models SET quantity = 0 WHERE product_id = ?");
        $stmtResetModels->bind_param("i", $productId);
        $stmtResetModels->execute();
        $stmtResetModels->close();

        $stmtResetDetails = $mysqli->prepare("UPDATE model_details SET quantity = 0 WHERE model_id IN (SELECT id FROM product_models WHERE product_id = ?)");
        $stmtResetDetails->bind_param("i", $productId);
        $stmtResetDetails->execute();
        $stmtResetDetails->close();

        $stmtDelete = $mysqli->prepare("DELETE FROM product_stock WHERE product_id = ? AND status = 'available'");
        $stmtDelete->bind_param("i", $productId);
        if (!$stmtDelete->execute()) throw new Exception($stmtDelete->error);
        $stmtDelete->close();

        $groups = []; // Skip the foreach loop

    } else {
        // Specific IDs Logic
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
    }

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
    if (!($deleteAll && $productId > 0)) {
         $idList = implode(',', $ids);
         $sqlDelete = "DELETE FROM product_stock WHERE id IN ($idList)";
         if (!$mysqli->query($sqlDelete)) throw new Exception($mysqli->error);
    }

    $mysqli->commit();
    echo json_encode(['success' => true, 'message' => 'Stock removed successfully']);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$mysqli->close();
?>
