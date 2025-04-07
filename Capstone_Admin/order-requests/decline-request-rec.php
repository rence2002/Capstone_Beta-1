<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Check if the admin's ID is stored in the session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

// Check if the Request_ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $requestID = $_GET['id'];

    // Prepare the UPDATE query to update the order request
    $stmt = $pdo->prepare("UPDATE tbl_order_request SET Order_Status = -1, Processed = 1 WHERE Request_ID = ?");
    $stmt->execute([$requestID]);

    // Check if the statement executed successfully
    if ($stmt->rowCount() > 0) {
        echo "Request has been updated successfully.";
        // Redirect to the order request list page or another page
        header("Location: read-all-request-form.php?message=Request updated successfully.");
        exit();
    } else {
        echo "Error updating request.";
    }
} else {
    echo "Invalid Request ID.";
}
?>
