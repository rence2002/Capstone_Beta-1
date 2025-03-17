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

// CREATE QUERY TO INSERT RECORD INTO tbl_ready_made_orders
$query = "INSERT INTO tbl_ready_made_orders (Product_ID, User_ID, Quantity, Total_Price, Order_Status) 
          VALUES (:productID, :userID, :quantity, :totalPrice, :orderStatus)";

try {
    // PREPARE QUERY AND STORE TO A STATEMENT VARIABLE
    $stmt = $pdo->prepare($query);

    // BIND PARAMETER VALUES
    $stmt->bindParam(":productID", $productID);
    $stmt->bindParam(":userID", $userID);
    $stmt->bindParam(":quantity", $quantity);
    $stmt->bindParam(":totalPrice", $totalPrice);
    $orderStatus = 0; // Pending status
    $stmt->bindParam(":orderStatus", $orderStatus);

    // EXECUTE STATEMENT
    if ($stmt->execute()) {
        // Redirect to the appropriate page after successful insertion
        header("Location: http://localhost../readymade/read-all-readymade-form.php");
        exit; // Ensure script stops after the redirect
    } else {
        echo "Error: Could not execute the query.";
    }
} catch (PDOException $e) {
    // Catch and display any database-related errors
    echo "Error: " . $e->getMessage();
}
?>
