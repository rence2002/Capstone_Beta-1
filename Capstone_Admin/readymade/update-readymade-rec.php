<?php
session_start();

// Include database connection
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get form data and sanitize inputs
$readyMadeOrderID = isset($_POST['txtOrderID']) ? (int)$_POST['txtOrderID'] : null;
$quantity = isset($_POST['txtQuantity']) ? (int)$_POST['txtQuantity'] : null;
$totalPrice = isset($_POST['txtTotalPrice']) ? (float)$_POST['txtTotalPrice'] : null;
$orderStatus = isset($_POST['txtOrderStatus']) ? (int)$_POST['txtOrderStatus'] : null;
$productStatus = isset($_POST['txtProductStatus']) ? (int)$_POST['txtProductStatus'] : null;

// Validate required fields
if (!$readyMadeOrderID || !$quantity || !$totalPrice || !isset($orderStatus) || !isset($productStatus)) {
    die("Error: Missing required fields.");
}

// Fetch order details including product name, current order and product status
$query = "
    SELECT 
        r.Order_Date, 
        r.Product_ID, 
        r.User_ID,
        r.Order_Status,
        r.Product_Status,
        p.Product_Name
    FROM tbl_ready_made_orders r
    JOIN tbl_prod_info p ON r.Product_ID = p.Product_ID
    WHERE r.ReadyMadeOrder_ID = :readyMadeOrderID
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':readyMadeOrderID', $readyMadeOrderID, PDO::PARAM_INT);
$stmt->execute();
$orderData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orderData) {
    die("Error: Order not found.");
}
//check if the data was already on 100 if it is then it will not run the transfer
$moveToHistory = true;
if($orderData['Order_Status'] == 100){
    $moveToHistory = false;
}

// Extract order details
$userID = $orderData['User_ID'];
$productID = $orderData['Product_ID'];
$productName = $orderData['Product_Name'];
$oldOrderStatus = $orderData['Order_Status'];
$oldProductStatus = $orderData['Product_Status'];

// Update Order Including Product_Status
$query = "
    UPDATE tbl_ready_made_orders 
    SET 
        Quantity = :quantity,
        Total_Price = :totalPrice,
        Order_Status = :orderStatus,
        Product_Status = :productStatus
    WHERE ReadyMadeOrder_ID = :readyMadeOrderID
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
$stmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);
$stmt->bindParam(':orderStatus', $orderStatus, PDO::PARAM_INT);
$stmt->bindParam(':productStatus', $productStatus, PDO::PARAM_INT);
$stmt->bindParam(':readyMadeOrderID', $readyMadeOrderID, PDO::PARAM_INT);
$stmt->execute();

// After successfully updating the ready-made order
$stmt->execute();

// Update tbl_progress with the new statuses for ready_made
$progressUpdateQuery = "UPDATE tbl_progress 
                        SET Order_Status = :orderStatus, Product_Status = :productStatus 
                        WHERE Product_ID = :productID AND Order_Type = 'ready_made'";
$progressStmt = $pdo->prepare($progressUpdateQuery);
$progressStmt->bindParam(':orderStatus', $orderStatus, PDO::PARAM_INT);
$progressStmt->bindParam(':productStatus', $productStatus, PDO::PARAM_INT);
$progressStmt->bindParam(':productID', $productID, PDO::PARAM_INT);
$progressStmt->execute();

// If order is at least 100% complete, update or insert into purchase history
if ($orderStatus == 100 && $moveToHistory == true) {
    $orderType = 'ready_made';

    // Check if a record already exists in tbl_purchase_history
    $query = "SELECT Purchase_ID FROM tbl_purchase_history WHERE Product_ID = :productID AND User_ID = :userID";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':productID', $productID, PDO::PARAM_INT);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_STR);
    $stmt->execute();
    $purchaseHistory = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($purchaseHistory) {
        // Update existing purchase record
        $query = "
            UPDATE tbl_purchase_history 
            SET 
                Product_Name = :productName,
                Quantity = :quantity,
                Total_Price = :totalPrice,
                Order_Type = :orderType,
                Order_Status = :orderStatus,
                Product_Status = :productStatus
            WHERE 
                Purchase_ID = :purchaseID
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':productName', $productName, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);
        $stmt->bindParam(':orderType', $orderType, PDO::PARAM_STR);
        $stmt->bindParam(':orderStatus', $orderStatus, PDO::PARAM_INT);
        $stmt->bindParam(':productStatus', $productStatus, PDO::PARAM_INT);
        $stmt->bindParam(':purchaseID', $purchaseHistory['Purchase_ID'], PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Insert a new purchase record
        $query = "
            INSERT INTO tbl_purchase_history (
                User_ID, 
                Product_ID, 
                Product_Name, 
                Quantity, 
                Total_Price, 
                Order_Type, 
                Order_Status,
                Product_Status
            ) VALUES (
                :userID, 
                :productID, 
                :productName, 
                :quantity, 
                :totalPrice, 
                :orderType, 
                :orderStatus,
                :productStatus
            )
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_STR);
        $stmt->bindParam(':productID', $productID, PDO::PARAM_INT);
        $stmt->bindParam(':productName', $productName, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);
        $stmt->bindParam(':orderType', $orderType, PDO::PARAM_STR);
        $stmt->bindParam(':orderStatus', $orderStatus, PDO::PARAM_INT);
        $stmt->bindParam(':productStatus', $productStatus, PDO::PARAM_INT);
        $stmt->execute();
    }
}

// Delete the record if the order status or product status changes from 100
if (($oldOrderStatus == 100 && $orderStatus != 100) ) {
    $query = "DELETE FROM tbl_ready_made_orders WHERE ReadyMadeOrder_ID = :readyMadeOrderID";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':readyMadeOrderID', $readyMadeOrderID, PDO::PARAM_INT);
    $stmt->execute();
}

// **Check if both statuses in tbl_progress are 100 and delete if so**
$checkProgressQuery = "SELECT Order_Status, Product_Status FROM tbl_progress WHERE Product_ID = :productID AND Order_Type = 'ready_made'";
$checkProgressStmt = $pdo->prepare($checkProgressQuery);
$checkProgressStmt->bindParam(':productID', $productID, PDO::PARAM_INT);
$checkProgressStmt->execute();
$progressData = $checkProgressStmt->fetch(PDO::FETCH_ASSOC);

if ($progressData && $progressData['Order_Status'] == 100 && $progressData['Product_Status'] == 100) {
    $deleteProgressQuery = "DELETE FROM tbl_progress WHERE Product_ID = :productID AND Order_Type = 'ready_made'";
    $deleteProgressStmt = $pdo->prepare($deleteProgressQuery);
    $deleteProgressStmt->bindParam(':productID', $productID, PDO::PARAM_INT);
    $deleteProgressStmt->execute();
}

// Redirect back to the ready-made orders list
header("Location: read-all-readymade-form.php");
exit();

?>
