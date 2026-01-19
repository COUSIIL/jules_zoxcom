<?php
// api.php



header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Fonction générique pour inclure un fichier et retourner le JSON décodé
 */
function runScript($path) {
    ob_start();
    include $path;
    $output = ob_get_clean();
    return json_decode($output, true);
}

/**
 * Map des actions disponibles
 */
$routes = [
    // --- Chat & IA ---
    "chatStream"                   => "../backend/diniChat/chat_stream.php",
    "chatDeepSeek"                 => "../backend/IA/deepSeek/chat.php",
    "chatGPT"                      => "../backend/IA/chatGpt/chat.php",
    "chatMistral"                  => "../backend/IA/mistral/chat.php",
    "chatGemini"                   => "../backend/IA/gemini/chat.php",
    "giminiGeneratePlan"           => "../backend/IA/gemini/generate_plan.php",
    "giminiImageToVideo"           => "../backend/IA/gemini/image_to_video.php",
    "giminiPoll"                   => "../backend/IA/gemini/polling.php",

    // --- Image & Upload ---
    "convertImageToHtml"           => "../backend/imageToHtml/convert.php",
    "uploadImage"                  => "../backend/imageToHtml/upload.php",
    "uploadImageCat"               => "../backend/upload/image.php",
    "uploadImages"                 => "../backend/upload/images.php",
    "saveImages"                   => "../backend/upload/imageSave.php",
    "getImages"                    => "../backend/sql/get/images.php",
    "updateImageName"              => "../backend/sql/update/imageName.php",
    "descriptionImages"            => "../backend/sql/get/descriptionImages.php",
    "deleteImages"                 => "../backend/sql/delete/images.php",
    "moveImage"                    => "../backend/sql/update/moveImage.php",
    "imageBlob"                    => "../backend/download/imageBlob.php",

    // --- Categories & Folders ---
    "postCategory"                 => "../backend/sql/post/category.php",
    "deleteCategory"               => "../backend/sql/delete/category.php",
    "getCategory"                  => "../backend/sql/get/category.php",
    "createFolder"                 => "../backend/sql/post/addFolder.php",
    "getFolders"                   => "../backend/sql/get/folders.php",
    "updateFolderName"             => "../backend/sql/update/folderName.php",
    "moveFolder"                   => "../backend/sql/update/moveFolder.php",
    "deleteFolders"                => "../backend/sql/delete/folders.php",

    // --- Delivery ---
    "addDeliveryMethod"            => "../backend/sql/post/deliveryMethod.php",
    "getDeliveryMethod"            => "../backend/sql/get/deliveryMethod.php",
    "deleteDeliveryMethod"         => "../backend/sql/delete/deliveryMethod.php",
    "updateActiveDelivery"         => "../backend/sql/update/deliveryActive.php",
    "getDelivery"                  => "../backend/sql/get/delivery.php",
    "postDelivery"                 => "../backend/sql/post/delivery.php",
    "selectStoreDelivery"          => "../backend/sql/post/storeDelivery.php",
    "getStoreDelivery"             => "../backend/sql/get/storeDelivery.php",
    "deleteDeliveryOptions"        => "../backend/sql/delete/deliveryOptions.php",
    

    // --- Orders ---
    "postOrder"                    => "../backend/sql/post/order.php",
    "getOrders"                    => "../backend/sql/get/order.php",
    "getOrder"                     => "../backend/sql/get/singleOrder.php",
    "updateOrderValue"             => "../backend/sql/update/order.php",
    "editOrder"                    => "../backend/sql/update/editOrder.php",
    "deleteOrder"                  => "../backend/sql/delete/order.php",
    "sendEmailOrder"               => "../backend/email/sendOrder.php",
    "getOrderHistory"              => "../backend/sql/get/orderHistory.php",
    "sseOrders"                    => "../backend/sse_orders.php",

    // --- Pinned Orders ---
    "pinOrder"                     => "../backend/sql/insert/pinOrder.php",
    "unpinOrder"                   => "../backend/sql/delete/unpinOrder.php",
    "getPinnedOrders"              => "../backend/sql/get/pinnedOrders.php",

    // --- OrderDZ conf
    "postOrderDz"                  => "../backend/orderdz/postOrder.php",
    "getOrderDz"                   => "../backend/orderdz/getOrder.php",
    "testOrderDz"                  => "../backend/orderdz/test.php",

    "orderDzModule"                => "../backend/sql/post/orderDzModule.php",

    // --- Discounts ---
    "postDiscount"                 => "../backend/sql/post/discount.php",
    "getDiscount"                  => "../backend/sql/get/discount.php",
    "testDiscount"                 => "../backend/sql/get/testDiscount.php",
    "deleteDiscount"               => "../backend/sql/delete/discount.php",

    // --- Products ---
    "postProducts"                 => "../backend/sql/post/products.php",
    "getProducts"                  => "../backend/sql/get/products.php",
    "deleteProduct"                => "../backend/sql/delete/product.php",
    "productClick"                 => "../backend/sql/post/productClick.php",
    "getProductClick"              => "../backend/sql/get/productClick.php",

    // --- Customers & Users ---
    "getCustomers"                 => "../backend/sql/get/customers.php",
    "getAllCustomers"              => "../backend/sql/get/allCustomers.php",
    "exportCustomers"              => "../backend/sql/get/exportCustomers.php",
    "deleteCustomer"               => "../backend/sql/delete/customer.php",
    "customerRegister"             => "../backend/sql/post/customerRegister.php",
    "customerLogin"                => "../backend/sql/connexion/customerLogin.php",
    "customerProfile"              => "../backend/sql/update/customerProfile.php",
    "customerDeleteAccount"        => "../backend/sql/delete/customerAccount.php",
    "getUsers"                     => "../backend/sql/get/users.php",
    "deleteUser"                   => "../backend/sql/delete/user.php",
    "addUser"                      => "../backend/sql/post/addUser.php",
    "updateUserProfile"            => "../backend/sql/update/profileImage.php",
    "updateUserInfo"               => "../backend/sql/update/userInfo.php",
    "connexion"                    => "../backend/sql/connexion/connexion.php",
    "checkConnexion"               => "../backend/sql/connexion/checkConnexion.php",

    // --- User Files ---
    "getUserFiles"                 => "../backend/sql/get/userFiles.php",
    "uploadUserFile"               => "../backend/sql/post/uploadUserFile.php",
    "deleteUserFile"               => "../backend/sql/delete/deleteUserFile.php",

    // --- Roles & Permissions ---
    "getRoles"                     => "../backend/sql/get/roles.php",
    "saveRole"                     => "../backend/sql/post/saveRole.php",
    "deleteRole"                   => "../backend/sql/delete/role.php",
    "assignRole"                   => "../backend/sql/post/assignRole.php",
    "seedAdmin"                    => "../backend/sql/update/seedAdmin.php",

    // --- Email ---
    "createEmail"                  => "../backend/sql/post/email.php",
    "getEmail"                     => "../backend/sql/get/email.php",

    // --- IP Management ---
    "banIp"                        => "../backend/sql/post/banIp.php",
    "ipList"                       => "../backend/sql/get/ipList.php",
    "checkIp"                      => "../backend/sql/get/checkIp.php",
    "deleteIp"                     => "../backend/sql/delete/ip.php",

    // --- Analytics ---
    "viewPage"                     => "../backend/sql/post/viewPage.php",
    "getViewPage"                  => "../backend/sql/get/viewPage.php",

    // --- Reminder ---
    "createReminder"                     => "../backend/sql/post/reminder.php",
    "editReminder"                     => "../backend/sql/update/reminder.php",
    "remouveReminder"                     => "../backend/sql/delete/reminder.php",
    "getReminder"                     => "../backend/sql/get/reminder.php",

    // --- Modules UPS, Anderson, Yalidine, Guepex ---
    "upsModule"                    => "../backend/sql/post/upsModule.php",
    "getUpsWilaya"                 => "../backend/ups/getWilaya.php",
    "getUpsFees"                   => "../backend/ups/getFees.php",
    "addUpsOrder"                  => "../backend/ups/addOrder.php",
    "getCommune"                   => "../backend/ups/getCommune.php",
    "testUps"                      => "../backend/ups/test.php",

    "andersonModule"               => "../backend/sql/post/andersonModule.php",
    "getAndersonWilaya"            => "../backend/anderson/getWilaya.php",
    "getAndersonFees"              => "../backend/anderson/getFees.php",
    "addAndersonOrder"             => "../backend/anderson/addOrder.php",
    "getAndersonCommune"           => "../backend/anderson/getCommune.php",
    "testAnderson"                 => "../backend/anderson/test.php",
    "getTrackingOp"                => "../backend/anderson/getTrackingOp.php",

    "yalidineModule"               => "../backend/sql/post/yalModule.php",
    "getYalidineWilaya"            => "../backend/yalidine/getWilaya.php",
    "getYalidineFees"              => "../backend/yalidine/getFees.php",
    "addYalidineOrder"             => "../backend/yalidine/addOrder.php",
    "getYalidineCommune"           => "../backend/yalidine/getCommune.php",
    "getYalidineCenter"            => "../backend/yalidine/getCenter.php",
    "yalidineCenterNext"           => "../backend/yalidine/getCenterNext.php",
    "testYalidine"                 => "../backend/yalidine/test.php",

    "guepexModule"                 => "../backend/sql/post/gpxModule.php",
    "getGuepexWilaya"              => "../backend/guepex/getWilaya.php",
    "getGuepexFees"                => "../backend/guepex/getFees.php",
    "addGuepexOrder"               => "../backend/guepex/addOrder.php",
    "getGuepexCommune"             => "../backend/guepex/getCommune.php",
    "getGuepexCenter"              => "../backend/guepex/getCenter.php",
    "guepexCenterNext"             => "../backend/guepex/getCenterNext.php",
    "testGuepex"                   => "../backend/guepex/test.php",
];

// Vérifiez si l'action demandée existe
if (isset($_GET['action']) && isset($routes[$_GET['action']])) {
    if (in_array($_GET['action'], ['exportCustomers', 'sseOrders', 'chatStream'])) {
        include $routes[$_GET['action']];
        exit();
    }
    $response = runScript($routes[$_GET['action']]);
} else {
    http_response_code(400);
    $response = ["error" => "Action non reconnue"];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
