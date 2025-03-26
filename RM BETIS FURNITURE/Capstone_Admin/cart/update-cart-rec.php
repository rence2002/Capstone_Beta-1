<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /Capstone/login.php");
    exit();
}

// Check if it's a checkout request
if (isset($_POST['checkout']) && $_POST['checkout'] === 'true') {
    // Validate required POST parameters for checkout
    if (!isset($_POST['txtCartID'], $_POST['txtQuantity'], $_POST['txtTotalPrice'], $_POST['txtOrderType'], $_POST['txtProductID'], $_POST['txtUserID']) ||
        !is_numeric($_POST['txtCartID']) || !is_numeric($_POST['txtQuantity']) || !is_numeric($_POST['txtTotalPrice']) ||
        !in_array($_POST['txtOrderType'], ['pre_order', 'ready_made'])) {
        echo "Invalid checkout request.";
        exit();
    }

    // Get and sanitize form inputs
    $cartID = intval($_POST['txtCartID']);
    $quantity = intval($_POST['txtQuantity']);
    $totalPrice = floatval($_POST['txtTotalPrice']);
    $orderType = $_POST['txtOrderType'];
    $productID = $_POST['txtProductID'];
    $userID = $_POST['txtUserID'];

    // Validate quantity
    if ($quantity <= 0) {
        echo "Please provide a valid quantity.";
        exit();
    }

    // Fetch product type from the product table.
    $query = "SELECT product_type FROM tbl_prod_info WHERE Product_ID = :productID";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':productID', $productID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    //Validate product
    if(!$result){
        echo "Error: Product not found.";
        exit();
    }
    $productType = $result['product_type'];

    // Determine if it's a custom order based on product type
    $customizationID = null;
    if ($productType === 'custom') {
        // If it's a custom order, you need to find the corresponding Customization_ID.
        // Assuming you store a Temp_Customization_ID in the cart when a custom order is created.
        $customizationQuery = "SELECT Temp_Customization_ID FROM tbl_customizations_temp WHERE User_ID = :userID ORDER BY Request_Date DESC LIMIT 1";
        $customizationStmt = $pdo->prepare($customizationQuery);
        $customizationStmt->bindParam(':userID', $userID, PDO::PARAM_STR);
        $customizationStmt->execute();
        $customizationResult = $customizationStmt->fetch(PDO::FETCH_ASSOC);
        if ($customizationResult) {
            $customizationID = $customizationResult['Temp_Customization_ID'];
        } else {
             echo "Error: No customization request found for this user.";
             echo "<script>console.log('No Customization Request Found')</script>"; //debugging
            exit();
        }
    }


    // Insert into tbl_order_request
    $pdo->beginTransaction(); // Start transaction

    try {
        $insertQuery = "
            INSERT INTO tbl_order_request (User_ID, Product_ID, Customization_ID, Quantity, Order_Type, Total_Price)
            VALUES (:userID, :productID, :customizationID, :quantity, :orderType, :totalPrice)
        ";

        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->bindParam(":userID", $userID, PDO::PARAM_STR);
        $insertStmt->bindParam(":productID", $productID, PDO::PARAM_INT);
        $insertStmt->bindParam(":customizationID", $customizationID, PDO::PARAM_INT);
        $insertStmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $insertStmt->bindParam(":orderType", $orderType, PDO::PARAM_STR);
        $insertStmt->bindParam(":totalPrice", $totalPrice, PDO::PARAM_STR);
        $insertStmt->execute();

        // Delete from tbl_cart
        $deleteQuery = "DELETE FROM tbl_cart WHERE Cart_ID = :cartID";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->bindParam(":cartID", $cartID, PDO::PARAM_INT);
        $deleteStmt->execute();

        $pdo->commit(); // Commit transaction
        echo "Checkout successful! Order has been placed.";

    } catch (PDOException $e) {
        $pdo->rollBack(); // Roll back transaction on error
        echo "Error: " . $e->getMessage();
        echo "<script>console.log('Error on PDO')</script>"; //debugging
    }

} else {
    echo "Invalid request.";
}
?>
