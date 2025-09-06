<?php
// api.php

/*$allowed_origins = [
    'http://swingi.pro',
    'http://192.168.0.135:8080'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
    header("Access-Control-Allow-Origin: http://swingi.pro"); // Origine par défaut
}*/


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");


function addDeliveryMethod() {
    ob_start();
    include '../backend/sql/post/deliveryMethod.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function banIP() {
    ob_start();
    include '../backend/sql/post/banIp.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function selectStoreDelivery() {
    ob_start();
    include '../backend/sql/post/storeDelivery.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getStoreDelivery() {
    ob_start();
    include '../backend/sql/get/storeDelivery.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function ipList() {
    ob_start();
    include '../backend/sql/get/ipList.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getBanks() {
    ob_start();
    include '../backend/sql/get/banks.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function checkIP() {
    ob_start();
    include '../backend/sql/get/checkIp.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function deleteIP() {
    ob_start();
    include '../backend/sql/delete/ip.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function deleteCategory() {
    ob_start();
    include '../backend/sql/delete/category.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function viewPage() {
    ob_start();
    include '../backend/sql/post/viewPage.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}
function productClick() {
    ob_start();
    include '../backend/sql/post/productClick.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getViewPage() {
    ob_start();
    include '../backend/sql/get/viewPage.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getProductClick() {
    ob_start();
    include '../backend/sql/get/productClick.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getDeliveryMethod() {
    ob_start();
    include '../backend/sql/get/deliveryMethod.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function uploadImageCat() {
    ob_start();
    include '../backend/upload/image.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function uploadImages() {
    ob_start();
    include '../backend/upload/images.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function saveImages() {
    ob_start();
    include '../backend/upload/imageSave.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function postCategory() {
    ob_start();
    include '../backend/sql/post/category.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function createFolder() {
    ob_start();
    include '../backend/sql/post/addFolder.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function createEmail() {
    ob_start();
    include '../backend/sql/post/email.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getFolders() {
    ob_start();
    include '../backend/sql/get/folders.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getEmail() {
    ob_start();
    include '../backend/sql/get/email.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getImages() {
    ob_start();
    include '../backend/sql/get/images.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function updateActivator() {
    ob_start();
    include '../backend/sql/update/modules/activator.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function updateFolderName() {
    ob_start();
    include '../backend/sql/update/folderName.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function moveFolder() {
    ob_start();
    include '../backend/sql/update/moveFolder.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function moveImage() {
    ob_start();
    include '../backend/sql/update/moveImage.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function updateActiveDelivery() {
    ob_start();
    include '../backend/sql/update/deliveryActive.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function updateImageName() {
    ob_start();
    include '../backend/sql/update/imageName.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function postProducts() {
    ob_start();
    include '../backend/sql/post/products.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function postDiscount() {
    ob_start();
    include '../backend/sql/post/discount.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function addUser() {
    ob_start();
    include '../backend/sql/post/addUser.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function postDelivery() {
    ob_start();
    include '../backend/sql/post/delivery.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function postOrder() {
    ob_start();
    include '../backend/sql/post/order.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getDiscount() {
    ob_start();
    include '../backend/sql/get/discount.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getDelivery() {
    ob_start();
    include '../backend/sql/get/delivery.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function testDiscount() {
    ob_start();
    include '../backend/sql/get/testDiscount.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getCategory() {
    ob_start();
    include '../backend/sql/get/category.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function connexion() {
    ob_start();
    include '../backend/sql/connexion/connexion.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function checkConnexion() {
    ob_start();
    include '../backend/sql/connexion/checkConnexion.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getProducts() {
    ob_start();
    include '../backend/sql/get/products.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function descriptionImages() {
    ob_start();
    include '../backend/sql/get/descriptionImages.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getOrders() {
    ob_start();
    include '../backend/sql/get/order.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function updateOrderValue() {
    ob_start();
    include '../backend/sql/update/order.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function updateUserProfile() {
    ob_start();
    include '../backend/sql/update/profileImage.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function updateUserInfo() {
    ob_start();
    include '../backend/sql/update/userInfo.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}


function chatDeepSeek() {
    ob_start();
    include '../backend/IA/deepSeek/chat.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function chatGPT() {
    ob_start();
    include '../backend/IA/chatGpt/chat.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function chatMistral() {
    ob_start();
    include '../backend/IA/mistral/chat.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function chatGemini() {
    ob_start();
    include '../backend/IA/gemini/chat.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function deleteOrder() {
    ob_start();
    include '../backend/sql/delete/order.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function deleteDiscount() {
    ob_start();
    include '../backend/sql/delete/discount.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function deleteCustomer() {
    ob_start();
    include '../backend/sql/delete/customer.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function deleteDeliveryMethod() {
    ob_start();
    include '../backend/sql/delete/deliveryMethod.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function deleteDeliveryOptions() {
    ob_start();
    include '../backend/sql/delete/deliveryOptions.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function deleteProduct() {
    ob_start();
    include '../backend/sql/delete/product.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function deleteImages() {
    ob_start();
    include '../backend/sql/delete/images.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function deleteFolders() {
    ob_start();
    include '../backend/sql/delete/folders.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getCustomer() {
    ob_start();
    include '../backend/sql/get/customers.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getUsers() {
    ob_start();
    include '../backend/sql/get/users.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getAllCustomers() {
    ob_start();
    include '../backend/sql/get/allCustomers.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function upsModule() {
    ob_start();
    include '../backend/sql/post/upsModule.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getUpsWilaya() {
    ob_start();
    include '../backend/ups/getWilaya.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getUpsFees() {
    ob_start();
    include '../backend/ups/getFees.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function addUpsOrder() {
    ob_start();
    include '../backend/ups/addOrder.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getCommune() {
    ob_start();
    include '../backend/ups/getCommune.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function testUps() {
    ob_start();
    include '../backend/ups/test.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function andersonModule() {
    ob_start();
    include '../backend/sql/post/andersonModule.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getAndersonWilaya() {
    ob_start();
    include '../backend/anderson/getWilaya.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getAndersonFees() {
    ob_start();
    include '../backend/anderson/getFees.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function addAndersonOrder() {
    ob_start();
    include '../backend/anderson/addOrder.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getAndersonCommune() {
    ob_start();
    include '../backend/anderson/getCommune.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function testAnderson() {
    ob_start();
    include '../backend/anderson/test.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}
function yalidineModule() {
    ob_start();
    include '../backend/sql/post/yalModule.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getYalidineWilaya() {
    ob_start();
    include '../backend/yalidine/getWilaya.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getYalidineFees() {
    ob_start();
    include '../backend/yalidine/getFees.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function addYalidineOrder() {
    ob_start();
    include '../backend/yalidine/addOrder.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getYalidineCommune() {
    ob_start();
    include '../backend/yalidine/getCommune.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getYalidineCenter() {
    ob_start();
    include '../backend/yalidine/getCenter.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function testYalidine() {
    ob_start();
    include '../backend/yalidine/test.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}


function guepexModule() {
    ob_start();
    include '../backend/sql/post/gpxModule.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getGuepexWilaya() {
    ob_start();
    include '../backend/guepex/getWilaya.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getGuepexFees() {
    ob_start();
    include '../backend/guepex/getFees.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function addGuepexOrder() {
    ob_start();
    include '../backend/guepex/addOrder.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getGuepexCommune() {
    ob_start();
    include '../backend/guepex/getCommune.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function getGuepexCenter() {
    ob_start();
    include '../backend/guepex/getCenter.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function testGuepex() {
    ob_start();
    include '../backend/guepex/test.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function imageBlob() {
    ob_start();
    include '../backend/download/imageBlob.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

function sendEmailOrder() {
    ob_start();
    include '../backend/email/sendOrder.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}



/*function proxyImage() {
    include 'backend/functions/pimage.php';
    exit(); // Stopper l'exécution après l'inclusion pour éviter tout output supplémentaire
}


function uploadImage() {
    include 'backend/functions/uploadImage.php';
    exit();
}

*/
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}


// Vérifiez si l'action est définie dans les paramètres de la requête
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch($action) {
        case 'getBanks':
            $response = getBanks();
            break;
        case 'deleteCategory':
            $response = deleteCategory();
            break;
        case 'updateProfileImage':
            $response = updateUserProfile();
            break;
        case 'updateUserInfo':
            $response = updateUserInfo();
            break;
        case 'checkConnexion':
            $response = checkConnexion();
            break;
        case 'getUsers':
            $response = getUsers();
            break;
        case 'addUser':
            $response = addUser();
            break;
        case 'getStoreDelivery':
            $response = getStoreDelivery();
            break;
        case 'selectStoreDelivery':
            $response = selectStoreDelivery();
            break;
        case 'ipList':
            $response = ipList();
            break;
        case 'checkIp':
            $response = checkIP();
            break;
        case 'deleteIp':
            $response = deleteIP();
            break;
        case 'banIp':
            $response = banIP();
            break;
        case 'viewPage':
            $response = viewPage();
            break;
        case 'getViewPage':
            $response = getViewPage();
            break;
        case 'productClick':
            $response = productClick();
            break;
        case 'getProductClick':
            $response = getProductClick();
            break;
        case 'deleteDeliveryMethod':
            $response = deleteDeliveryMethod();
            break;
        case 'updateActiveDelivery':
            $response = updateActiveDelivery();
            break;
        case 'getDeliveryMethod':
            $response = getDeliveryMethod();
            break;
        case 'addDeliveryMethod':
            $response = addDeliveryMethod();
            break;
        case 'moveFolder':
            $response = moveFolder();
            break;
        case 'moveImage':
            $response = moveImage();
            break;
        case 'getEmail':
            $response = getEmail();
            break;
        case 'createEmail':
            $response = createEmail();
            break;
        case 'getImages':
            $response = getImages();
            break;
        case 'saveImages':
            $response = saveImages();
            break;
        case 'updateFolderName':
            $response = updateFolderName();
            break;
        case 'updateImageName':
            $response = updateImageName();
            break;
        case 'getFolders':
            $response = getFolders();
            break;
        case 'createFolder':
            $response = createFolder();
            break;
        case 'updateActivator':
            $response = updateActivator();
            break;
        case 'sendEmailOrder':
            $response = sendEmailOrder();
            break;
        case 'connexion':
            $response = connexion();
            break;
        case 'deleteImages':
            $response = deleteImages();
            break;
        case 'deleteFolders':
            $response = deleteFolders();
            break;
        case 'descriptionImages':
            $response = descriptionImages();
            break;
        case 'deleteDeliveryOptions':
            $response = deleteDeliveryOptions();
            break;     
        case 'imageBlob':
            $response = imageBlob();
            break;
        case 'deleteProduct':
            $response = deleteProduct();
            break;
        case 'getCommune':
            $response = getCommune();
            break;
        case 'getUpsFees':
            $response = getUpsFees();
            break;
        case 'testUps':
            $response = testUps();
            break;
        case 'upsModule':
            $response = upsModule();
            break;
        case 'addUpsOrder':
            $response = addUpsOrder();
            break;
        case 'getUpsWilaya':
            $response = getUpsWilaya();
            break;
        
        case 'getAndersonCommune':
            $response = getAndersonCommune();
            break;
        case 'getAndersonFees':
            $response = getAndersonFees();
            break;
        case 'testAnderson':
            $response = testAnderson();
            break;
        case 'andersonModule':
            $response = andersonModule();
            break;
        case 'addAndersonOrder':
            $response = addAndersonOrder();
            break;
        case 'getAndersonWilaya':
            $response = getAndersonWilaya();
            break;

        case 'getYalidineCommune':
            $response = getYalidineCommune();
            break;
        case 'getYalidineFees':
            $response = getYalidineFees();
            break;
        case 'testYalidine':
            $response = testYalidine();
            break;
        case 'yalidineModule':
            $response = yalidineModule();
            break;
        case 'addYalidineOrder':
            $response = addYalidineOrder();
            break;
        case 'getYalidineWilaya':
            $response = getYalidineWilaya();
            break;
        case 'getYalidineCenter':
            $response = getYalidineCenter();
            break;
        
        case 'getGuepexCommune':
            $response = getGuepexCommune();
            break;
        case 'getGuepexFees':
            $response = getGuepexFees();
            break;
        case 'testGuepex':
            $response = testGuepex();
            break;
        case 'guepexModule':
            $response = guepexModule();
            break;
        case 'addGuepexOrder':
            $response = addGuepexOrder();
            break;
        case 'getGuepexWilaya':
            $response = getGuepexWilaya();
            break;
        case 'getGuepexCenter':
            $response = getGuepexCenter();
            break;

        case 'getCustomers':
            $response = getCustomer();
            break;
        case 'getAllCustomers':
            $response = getAllCustomers();
            break;
        case 'deleteDiscount':
            $response = deleteDiscount();
            break;
        case 'deleteCustomer':
            $response = deleteCustomer();
            break;
        case 'deleteOrder':
            $response = deleteOrder();
            break;
        case 'updateOrderValue':
            $response = updateOrderValue();
            break;
        case 'chatMistral':
            $response = chatMistral();
            break;
        case 'chatGemini':
            $response = chatGemini();
            break;
        case 'chatGPT':
            $response = chatGPT();
            break;
        case 'chatDeepSeek':
            $response = chatDeepSeek();
            break;
        case 'postOrder':
            $response = postOrder();
            break;
        case 'getDelivery':
            $response = getDelivery();
            break;
        case 'testDiscount':
            $response = testDiscount();
            break;
        case 'getDiscount':
            $response = getDiscount();
            break;
        case 'postDelivery':
            $response = postDelivery();
            break;
        case 'postDiscount':
            $response = postDiscount();
            break;
        case 'getProducts':
            $response = getProducts();
            break;
        case 'postProducts':
            $response = postProducts();
            break;
        case 'uploadImages':
            $response = uploadImages();
            break;

        case 'getOrders':
            $response = getOrders();
            break;
        case 'getCategory':
            $response = getCategory();
            break;
        case 'postCategory':
            $response = postCategory();
            break;
        
        case 'uploadImageCat':
            $response = uploadImageCat();
            break;
        
        default:
            $response = ['error' => 'Action not correct'];
            break;
    }
    
    echo json_encode($response);
} else {
    echo json_encode(['error' => 'No action selected']);
}


?>