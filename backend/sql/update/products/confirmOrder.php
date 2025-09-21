<?php
function updateStock($mysqli, $modal_id, $product_id, $qty) {
    // Récupération du model detail
    $stmtModel = $mysqli->prepare("SELECT * FROM model_details WHERE id = ?");
    $stmtModel->bind_param("i", $modal_id);
    $stmtModel->execute();
    $resultModel = $stmtModel->get_result();
    $fetchedResult1 = $resultModel->fetch_assoc();
    $stmtModel->close();

    // Récupération du product model (⚠️ attention id vs product_id)
    $stmtProduct = $mysqli->prepare("SELECT * FROM product_models WHERE product_id = ?");
    $stmtProduct->bind_param("i", $product_id);
    $stmtProduct->execute();
    $resultProduct = $stmtProduct->get_result();
    $fetchedResult2 = $resultProduct->fetch_assoc();
    $stmtProduct->close();

    $isDetails = (bool)$fetchedResult1;

    if (!$fetchedResult2) {
        return [
            'success' => false,
            'message' => 'Product model not found.',
            'data'    => $product_id
        ];
    }

    // Valeurs par défaut
    $newModelQty   = $fetchedResult1['quantity'] ?? null;
    $newProductQty = $fetchedResult2['quantity'] ?? null;

    if ($fetchedResult2['infinit_stock'] == "0") {
        // Calcul des nouvelles quantités
        if ($isDetails) {
            $oldQtyModel = (int)$fetchedResult1['quantity'];
            $newModelQty = $oldQtyModel - $qty;
        }

        $oldQtyProduct = (int)$fetchedResult2['quantity'];
        $newProductQty = $oldQtyProduct - $qty;

        // Vérif stock négatif
        if (($isDetails && $newModelQty < 0) || $newProductQty < 0) {
            return [
                'success' => false,
                'message' => 'Not enough stock available.'
            ];
        }

        // Update model_details si existe
        if ($isDetails) {
            $update_modal_query = $mysqli->prepare(
                "UPDATE model_details SET quantity = ? WHERE id = ?"
            );
            $update_modal_query->bind_param("ii", $newModelQty, $modal_id);
            $update_modal_query->execute();
            $update_modal_query->close();
        }

        // Update product_models
        $update_product_query = $mysqli->prepare(
            "UPDATE product_models SET quantity = ? WHERE product_id = ?"
        );
        $update_product_query->bind_param("ii", $newProductQty, $product_id);
        $update_product_query->execute();
        $update_product_query->close();
    }

    return [
        'success' => true,
        'message' => 'Stock updated successfully',
        'data'    => [
            'newModelQty'   => $newModelQty,
            'newProductQty' => $newProductQty
        ]
    ];
}
