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

    // Prepare the DELETE query to remove the order request
    $stmt = $pdo->prepare("DELETE FROM tbl_order_request WHERE Request_ID = :requestID");
    $stmt->bindParam(':requestID', $requestID);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Request has been deleted successfully.";
        // Redirect to the order request list page or another page
        header("Location: read-all-request-form.php?message=Request deleted successfully.");
        exit();
    } else {
        echo "Error deleting request.";
    }
} else {
    echo "Invalid Request ID.";
}
?>
