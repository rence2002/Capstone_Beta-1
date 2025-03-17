<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /Capstone/login.php");
    exit();
}

// DELETE CART LOGIC
if (isset($_POST['txtCartID']) && is_numeric($_POST['txtCartID'])) {
    // Sanitize the Cart ID from the POST data
    $cartID = intval($_POST['txtCartID']);

    // Create the query to delete the cart record
    $query = "DELETE FROM tbl_cart WHERE Cart_ID = :cart_id";

    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':cart_id', $cartID, PDO::PARAM_INT);

    // Check if the query executed successfully
    if ($stmt->execute()) {
        // Redirect to the cart list page after deletion
        header("location: read-all-cart-form.php");
        exit();
    } else {
        // Error handling if deletion fails
        echo "Error deleting cart record.";
        exit();
    }
} else {
    // Error handling if Cart ID is invalid or missing
    echo "Invalid or missing cart ID.";
    exit();
}
?>
