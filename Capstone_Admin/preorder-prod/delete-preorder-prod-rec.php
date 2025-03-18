<?php
// Disable error reporting for production (enable for development if needed)
// error_reporting(E_ALL); // For debugging
error_reporting(0); // For production

// Include the database connection
include("../config/database.php");

// Check if 'id' is set and is a valid integer
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $preorderID = (int) $_GET['id'];

    // Delete associated progress record
    $deleteProgressQuery = "DELETE FROM tbl_progress 
                            WHERE Product_ID = (SELECT Product_ID FROM tbl_preorder WHERE Preorder_ID = ?) 
                            AND Order_Type = 'pre_order'";
    $progressStmt = $pdo->prepare($deleteProgressQuery);
    $progressStmt->bindValue(1, $preorderID, PDO::PARAM_INT);
    $progressStmt->execute();

    // Prepare the delete statement for preorder
    $deleteQuery = "DELETE FROM tbl_preorder WHERE Preorder_ID = ?";
    $stmt = $pdo->prepare($deleteQuery);
    $stmt->bindValue(1, $preorderID, PDO::PARAM_INT);

    // Execute the delete statement
    if ($stmt->execute()) {
        header("Location: read-all-preorder-prod-form.php"); // Redirect to the list page
        exit();
    } else {
        echo "Error deleting preorder. Please try again.";
    }
} else {
    echo "Invalid preorder ID.";
    header("Location: read-all-preorder-prod-form.php"); // Redirect to the list page if the ID is invalid
    exit();
}
?>
