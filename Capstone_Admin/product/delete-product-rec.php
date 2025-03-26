<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Check if the admin's ID is stored in session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

// Check if the product ID is provided in the URL
if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    // Prepare the DELETE statement
    $query = "DELETE FROM tbl_prod_info WHERE Product_ID = ?";
    $stmt = $pdo->prepare($query);

    // Bind the Product_ID to the prepared statement
    $stmt->bindValue(1, $productId);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to the product list page if deletion is successful
        header("Location: read-all-product-form.php");
        exit();
    } else {
        // If there’s an error during deletion, display a message
        echo "Error deleting product. Please try again.";
    }
} else {
    // If no product ID is provided in the URL, display a message
    echo "No product ID specified.";
    exit();
}
?>
