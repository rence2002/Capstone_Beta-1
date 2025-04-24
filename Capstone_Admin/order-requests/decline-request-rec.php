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

// Check if the Request_ID is provided via GET and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $requestID = (int)$_GET['id']; // Cast to integer for safety

    try {
        // Prepare the UPDATE query to mark the request as processed (effectively declined)
        // Removed Order_Status = -1 as the column does not exist in tbl_order_request
        $stmt = $pdo->prepare("UPDATE tbl_order_request SET Processed = 1 WHERE Request_ID = :requestID");

        // Bind the parameter
        $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        // Check if any row was actually updated
        if ($stmt->rowCount() > 0) {
            // Redirect on success with a specific success message
            header("Location: read-all-request-form.php?success=declined&id=" . $requestID);
            exit();
        } else {
            // No rows updated - maybe already processed or ID doesn't exist
            // Redirect back with a warning message
            header("Location: read-all-request-form.php?warning=notfound_or_processed&id=" . $requestID);
            exit();
        }
    } catch (PDOException $e) {
        // Log the database error (important for debugging)
        error_log("Database Error declining request ID $requestID: " . $e->getMessage());
        // Show a generic error message to the user or redirect with an error flag
        // die("Database Error: Could not decline the request. Please check server logs.");
        header("Location: read-all-request-form.php?error=db_error&id=" . $requestID);
        exit();
    }
} else {
    // Invalid or missing Request ID provided in the URL
    // die("Invalid Request ID provided.");
    header("Location: read-all-request-form.php?error=invalid_id");
    exit();
}
?>
