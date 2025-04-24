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

// Check if the request method is POST and Progress_ID is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Progress_ID']) && is_numeric($_POST['Progress_ID'])) {
    $progressID = (int)$_POST['Progress_ID'];

    try {
        // Prepare the DELETE query targeting tbl_progress
        // Optional: Add WHERE Order_Type = 'pre_order' for extra safety, though deleting by primary key is usually sufficient.
        $stmt = $pdo->prepare("DELETE FROM tbl_progress WHERE Progress_ID = :progressID");

        // Bind the parameter
        $stmt->bindParam(':progressID', $progressID, PDO::PARAM_INT);

        // Execute the query
        if ($stmt->execute()) {
            // Check if any row was actually deleted
            if ($stmt->rowCount() > 0) {
                // Redirect on successful deletion
                header("Location: read-all-preorder-prod-form.php?success=deleted&id=" . $progressID);
                exit();
            } else {
                // No rows deleted - maybe already deleted or ID doesn't exist
                header("Location: read-all-preorder-prod-form.php?warning=notfound&id=" . $progressID);
                exit();
            }
        } else {
             // Execution failed
             $errorInfo = $stmt->errorInfo();
             throw new PDOException("Failed to execute delete statement: " . ($errorInfo[2] ?? 'Unknown error'));
        }
    } catch (PDOException $e) {
        // Log the database error
        error_log("Database Error deleting progress record ID $progressID: " . $e->getMessage());
        // Redirect with a generic error message
        header("Location: read-all-preorder-prod-form.php?error=db_error&id=" . $progressID);
        exit();
    }
} else {
    // Invalid request method or missing/invalid Progress ID
    header("Location: read-all-preorder-prod-form.php?error=invalid_request");
    exit();
}
?>
