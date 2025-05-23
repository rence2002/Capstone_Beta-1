<?php
// CALL DATABASE CONNECTION SCRIPT
include("../config/database.php");

// GET USER INPUT FROM WEB FORM
$productID = $_POST['txtProductID'];
$userID = $_POST['txtUserID'];
$quantity = $_POST['txtQuantity'];
$totalPrice = $_POST['txtTotalPrice'];

// VALIDATE REQUIRED FIELDS (check if fields are not empty)
if (empty($productID) || empty($userID) || empty($quantity) || empty($totalPrice)) {
    die("Error: Please fill in all required fields.");
}

// Define Order Type and Payment Status
$orderType = 'ready_made'; // Default order type for ready-made products
$customizationID = null; // No customization for ready-made products
$paymentStatus = 'Pending'; // Default payment status

// CREATE QUERY TO INSERT RECORD INTO tbl_order_request
$query = "INSERT INTO tbl_order_request (
              User_ID, 
              Product_ID, 
              Customization_ID, 
              Quantity, 
              Order_Type, 
              Total_Price, 
              Payment_Status, 
              Processed
          ) VALUES (
              :userID, 
              :productID, 
              :customizationID, 
              :quantity, 
              :orderType, 
              :totalPrice, 
              :paymentStatus, 
              :processed
          )";

try {
    // PREPARE QUERY AND STORE TO A STATEMENT VARIABLE
    $stmt = $pdo->prepare($query);

    // BIND PARAMETER VALUES
    $stmt->bindParam(":userID", $userID);
    $stmt->bindParam(":productID", $productID);
    $stmt->bindParam(":customizationID", $customizationID);
    $stmt->bindParam(":quantity", $quantity);
    $stmt->bindParam(":orderType", $orderType);
    $stmt->bindParam(":totalPrice", $totalPrice);
    $stmt->bindParam(":paymentStatus", $paymentStatus);
    $processed = 0; // Default value for 'Processed' (0 = Not Processed)
    $stmt->bindParam(":processed", $processed);

    // EXECUTE STATEMENT
    if ($stmt->execute()) {
        // Redirect to the appropriate page for managing orders
        header("Location: ../order-requests/read-all-request-form.php");
        exit; // Ensure script stops after the redirect
    } else {
        echo "Error: Could not execute the query.";
    }
} catch (PDOException $e) {
    // Catch and display any database-related errors
    echo "Error: " . $e->getMessage();
}
?>