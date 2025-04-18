<?php
session_start();
include '../config/database.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

// Validate the request ID and payment status
$requestID = isset($_GET['id']) ? (int)$_GET['id'] : null;
$paymentStatus = isset($_GET['payment_status']) ? $_GET['payment_status'] : null;

if (!$requestID || !$paymentStatus) {
    die("Invalid request ID or payment status.");
}

// Validate payment status value
$validPaymentStatuses = ['downpayment_paid', 'fully_paid']; // Update these values to match the dropdown
if (!in_array($paymentStatus, $validPaymentStatuses)) {
    die("Invalid payment status value.");
}

try {
    $pdo->beginTransaction();

    // Update the Payment_Status in tbl_order_request
    $stmt = $pdo->prepare("UPDATE tbl_order_request SET Payment_Status = :paymentStatus WHERE Request_ID = :requestID");
    $stmt->bindParam(':paymentStatus', $paymentStatus, PDO::PARAM_STR);
    $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the order request
    $orderRequest = fetchOrderRequest($pdo, $requestID);

    if (!$orderRequest) {
        throw new Exception("Order request not found.");
    }

    // Process the order based on its type
    switch ($orderRequest['Order_Type']) {
        case 'ready_made':
            processReadyMade($pdo, $orderRequest, $paymentStatus);
            break;

        case 'pre_order':
            processPreOrder($pdo, $orderRequest, $paymentStatus);
            break;

        case 'custom':
            processCustomOrder($pdo, $orderRequest, $requestID, $paymentStatus);
            break;

        default:
            throw new Exception("Unsupported order type.");
    }

    // Update the order request to mark it as processed
    $stmt = $pdo->prepare("UPDATE tbl_order_request SET Order_Status = 1, Processed = 1 WHERE Request_ID = ?");
    $stmt->execute([$requestID]);

    $pdo->commit();
    header("Location: read-all-request-form.php");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error processing request: " . $e->getMessage());
}

/**
 * Fetch the order request from tbl_order_request.
 */
function fetchOrderRequest($pdo, $requestID) {
    $stmt = $pdo->prepare("SELECT * FROM tbl_order_request WHERE Request_ID = ?");
    $stmt->execute([$requestID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Process a ready-made order.
 */
function processReadyMade($pdo, $orderRequest, $paymentStatus) {
    // Fetch the product name from tbl_prod_info
    $productName = fetchProductName($pdo, $orderRequest['Product_ID']);

    // Insert into tbl_ready_made_orders
    $stmt = $pdo->prepare("
        INSERT INTO tbl_ready_made_orders 
        (Product_ID, User_ID, Quantity, Total_Price, Order_Status, Product_Status, Payment_Status)
        VALUES (?, ?, ?, ?, 10, 90, ?)
    ");
    $stmt->execute([
        $orderRequest['Product_ID'],
        $orderRequest['User_ID'],
        $orderRequest['Quantity'],
        $orderRequest['Total_Price'],
        $paymentStatus
    ]);

    // Insert into tbl_progress
    insertIntoProgress($pdo, $orderRequest, 'ready_made', 10, 90, $productName);
}

/**
 * Process a pre-order.
 */
function processPreOrder($pdo, $orderRequest, $paymentStatus) {
    // Insert into tbl_preorder
    $stmt = $pdo->prepare("
        INSERT INTO tbl_preorder 
        (Product_ID, User_ID, Quantity, Total_Price, Preorder_Status, Product_Status, Payment_Status)
        VALUES (?, ?, ?, ?, 10, 0, ?)
    ");
    $stmt->execute([
        $orderRequest['Product_ID'],
        $orderRequest['User_ID'],
        $orderRequest['Quantity'],
        $orderRequest['Total_Price'],
        $paymentStatus
    ]);

    // Insert into tbl_progress
    insertIntoProgress($pdo, $orderRequest, 'pre_order', 10, 0);
}

/**
 * Process a custom order.
 */
function processCustomOrder($pdo, $orderRequest, $requestID, $paymentStatus) {
    // Fetch temporary customization
    $customization = fetchTemporaryCustomization($pdo, $orderRequest['Customization_ID']);

    if (!$customization) {
        throw new Exception("Customization request not found.");
    }

    // Create a custom product
    $newProductID = createCustomProduct($pdo, $customization, $requestID);

    // Fetch the product name from tbl_prod_info
    $productName = fetchProductName($pdo, $newProductID);

    // Move to permanent customizations
    moveToPermanentCustomizations($pdo, $customization, $newProductID, $paymentStatus);

    // Insert into tbl_progress
    insertIntoProgress($pdo, [
        'User_ID' => $customization['User_ID'],
        'Product_ID' => $newProductID,
        'Product_Name' => $productName,
        'Quantity' => 1,
        'Total_Price' => 0.00
    ], 'custom', 10, 0);

    // Cleanup temporary data
    cleanupTemporaryData($pdo, $orderRequest['Customization_ID']);
}

/**
 * Fetch temporary customization data.
 */
function fetchTemporaryCustomization($pdo, $tempCustomizationID) {
    $stmt = $pdo->prepare("SELECT * FROM tbl_customizations_temp WHERE Temp_Customization_ID = ?");
    $stmt->execute([$tempCustomizationID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Create a custom product in tbl_prod_info.
 */
function createCustomProduct($pdo, $customization, $requestID) {
    $stmt = $pdo->prepare("
        INSERT INTO tbl_prod_info 
        (Product_Name, Description, Category, product_type)
        VALUES (?, ?, 'Custom Furniture', 'custom')
    ");
    $productName = 'Custom ' . $customization['Furniture_Type'];
    $description = 'Custom order from request #' . $requestID;
    $stmt->execute([$productName, $description]);
    return $pdo->lastInsertId();
}

/**
 * Fetch the product name from tbl_prod_info.
 */
function fetchProductName($pdo, $productID) {
    $stmt = $pdo->prepare("SELECT Product_Name FROM tbl_prod_info WHERE Product_ID = ?");
    $stmt->execute([$productID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['Product_Name'] ?? 'N/A';
}

/**
 * Move temporary customization to permanent customizations.
 */
function moveToPermanentCustomizations($pdo, $customization, $newProductID, $paymentStatus) {
    $stmt = $pdo->prepare("
        INSERT INTO tbl_customizations 
        (User_ID, Furniture_Type, Product_ID, Order_Status, Product_Status, Payment_Status)
        VALUES (?, ?, ?, 10, 0, ?)
    ");
    $stmt->execute([
        $customization['User_ID'],
        $customization['Furniture_Type'],
        $newProductID,
        $paymentStatus
    ]);
}

/**
 * Insert progress data into tbl_progress.
 */
function insertIntoProgress($pdo, $orderRequest, $orderType, $orderStatus, $productStatus) {
    $stmt = $pdo->prepare("
        INSERT INTO tbl_progress 
        (User_ID, Product_ID, Product_Name, Order_Type, Order_Status, Product_Status, Quantity, Total_Price, Date_Added, LastUpdate)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $orderRequest['User_ID'],
        $orderRequest['Product_ID'],
        fetchProductName($pdo, $orderRequest['Product_ID']),
        $orderType,
        $orderStatus,
        $productStatus,
        $orderRequest['Quantity'],
        $orderRequest['Total_Price']
    ]);
}

/**
 * Cleanup temporary customization data.
 */
function cleanupTemporaryData($pdo, $tempCustomizationID) {
    $stmt = $pdo->prepare("DELETE FROM tbl_customizations_temp WHERE Temp_Customization_ID = ?");
    $stmt->execute([$tempCustomizationID]);
}
?>
