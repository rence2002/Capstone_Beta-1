<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Assuming the admin's ID is stored in session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

// Check if the user ID is set in the URL
if (isset($_GET['id'])) {
    $userID = $_GET['id']; // Get user ID from the URL

    // Create the query to delete the user record
    $query = "DELETE FROM tbl_user_info WHERE User_ID = :userID";

    // Prepare the query and store it in a statement variable
    $stmt = $pdo->prepare($query);

    // Bind the parameter value
    $stmt->bindParam(':userID', $userID);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to the user list page after successful deletion
        header("Location: ../user/read-all-user-form.php");
        exit(); // Ensure that no further code is executed
    } else {
        // Handle any errors that may occur during deletion
        echo "Error deleting record: " . implode(":", $stmt->errorInfo());
    }
} else {
    // If no ID is passed in the URL, redirect back to the user list
    header("Location: ../user/read-all-user-form.php");
    exit();
}
?>
