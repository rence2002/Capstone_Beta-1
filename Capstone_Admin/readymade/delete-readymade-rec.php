<?php
// Disable error reporting for production (enable for development if needed)
// error_reporting(E_ALL); // For debugging
error_reporting(0); // For production

// Include the database connection
include("../config/database.php");

// Check if 'id' is set and is a valid integer
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $readyMadeOrderID = (int) $_GET['id'];

    // Delete associated progress record
    $deleteProgressQuery = "DELETE FROM tbl_progress 
                            WHERE Product_ID = (SELECT Product_ID FROM tbl_ready_made_orders WHERE ReadyMadeOrder_ID = ?) 
                            AND Order_Type = 'ready_made'";
    $progressStmt = $pdo->prepare($deleteProgressQuery);
    $progressStmt->bindValue(1, $readyMadeOrderID, PDO::PARAM_INT);
    $progressStmt->execute();

    // Prepare the delete statement for ready-made order
    $deleteQuery = "DELETE FROM tbl_ready_made_orders WHERE ReadyMadeOrder_ID = ?";
    $stmt = $pdo->prepare($deleteQuery);
    $stmt->bindValue(1, $readyMadeOrderID, PDO::PARAM_INT);

    // Execute the delete statement
    if ($stmt->execute()) {
        header("Location: read-all-readymade-form.php"); // Redirect to the list page
        exit();
    } else {
        echo "Error deleting the order. Please try again.";
    }
} else {
    echo "Invalid order ID.";
    header("Location: read-all-readymade-form.php"); // Redirect to the list page if the ID is invalid
    exit();
}
?>
