<?php
session_start(); // Start the session

// Include the database connection
include("../config/database.php");

// Check if the admin's ID is stored in the session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

// Ensure POST method is used
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// --- GET USER INPUT FROM WEB FORM (Using names from the updated form) ---
$productID = $_POST['Product_ID'] ?? null;
$userID = $_POST['User_ID'] ?? null;
$quantity = $_POST['Quantity'] ?? null;
$totalPrice = $_POST['Total_Price'] ?? null;
$orderType = $_POST['Order_Type'] ?? null; // Should be 'pre_order' from hidden field

// --- VALIDATE REQUIRED FIELDS ---
$errors = [];
if (empty($productID) || !is_numeric($productID)) {
    $errors[] = "Valid Product ID is required.";
}
if (empty($userID)) {
    $errors[] = "User ID is required.";
}
if (empty($quantity) || !is_numeric($quantity) || $quantity < 1) {
    $errors[] = "Valid Quantity (at least 1) is required.";
}
if ($totalPrice === null || !is_numeric($totalPrice) || $totalPrice < 0) {
    $errors[] = "Valid Total Price is required.";
}
if ($orderType !== 'pre_order') { // Ensure the hidden field value is correct
    $errors[] = "Invalid Order Type specified.";
}

// If validation errors exist, display them (or redirect back with errors)
if (!empty($errors)) {
    // Simple error display for now
    echo "Error creating pre-order request:<br>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    // Link back to the form where the request originated
    echo '<a href="create-preorder-prod-form.php">Go Back</a>';
    exit();
}

// --- CREATE QUERY TO INSERT RECORD INTO tbl_order_request ---
// Removed non-existent Order_Status column
// Customization_ID is explicitly NULL for pre-orders of standard products
// Payment_Status and Processed will use their database defaults ('Pending', 0)
$query = "INSERT INTO tbl_order_request
            (User_ID, Product_ID, Customization_ID, Quantity, Total_Price, Order_Type)
          VALUES
            (:userID, :productID, NULL, :quantity, :totalPrice, :orderType)";

try {
    // PREPARE QUERY AND STORE TO A STATEMENT VARIABLE
    $stmt = $pdo->prepare($query);

    // BIND PARAMETER VALUES
    $stmt->bindParam(":userID", $userID, PDO::PARAM_STR);
    $stmt->bindParam(":productID", $productID, PDO::PARAM_INT);
    $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
    $stmt->bindParam(":totalPrice", $totalPrice, PDO::PARAM_STR); // Bind as string for decimal
    $stmt->bindParam(":orderType", $orderType, PDO::PARAM_STR);

    // EXECUTE STATEMENT
    if ($stmt->execute()) {
        // Redirect to the order requests list page on success
        header("Location: ../order-requests/read-all-request-form.php?success=preorder_request_created");
        exit; // Ensure script stops after the redirect
    } else {
        // Execution failed
        $errorInfo = $stmt->errorInfo();
        throw new PDOException("Failed to execute insert statement: " . ($errorInfo[2] ?? 'Unknown error'));
    }
} catch (PDOException $e) {
    // Catch and display/log any database-related errors
    error_log("Database Error creating pre-order request: " . $e->getMessage());
    echo "Database Error: Could not create the pre-order request. Please check logs or contact support.";
    // echo "Error: " . $e->getMessage(); // Show detailed error during development
    echo '<br><a href="create-preorder-prod-form.php">Go Back</a>';
} catch (Exception $e) {
    // Catch other potential errors
    error_log("General Error creating pre-order request: " . $e->getMessage());
    echo "An unexpected error occurred: " . htmlspecialchars($e->getMessage());
    echo '<br><a href="create-preorder-prod-form.php">Go Back</a>';
}
?>
