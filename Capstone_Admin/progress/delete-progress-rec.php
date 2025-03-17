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

// Check if form is submitted (through POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the progress ID and order type to delete
    if (isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['order_type']) && !empty($_POST['order_type'])) {
        $progressID = $_POST['id'];
        $orderType = $_POST['order_type'];
        $tableName = "";
        $idColumnName = "";

        // Determine the table name and ID column based on order type
        switch ($orderType) {
            case 'custom':
                // Get the Product_ID to be able to delete the product
                $getProductIdQuery = "SELECT Product_ID FROM tbl_customizations WHERE Customization_ID = :progressID";
                $getProductIdStmt = $pdo->prepare($getProductIdQuery);
                $getProductIdStmt->bindParam(":progressID", $progressID, PDO::PARAM_INT);
                $getProductIdStmt->execute();
                $productIdResult = $getProductIdStmt->fetch(PDO::FETCH_ASSOC);

                if ($productIdResult && isset($productIdResult['Product_ID'])) {
                    $productID = $productIdResult['Product_ID'];

                    // Delete associated product from tbl_prod_info
                    $deleteProductQuery = "DELETE FROM tbl_prod_info WHERE Product_ID = :productID";
                    $deleteProductStmt = $pdo->prepare($deleteProductQuery);
                    $deleteProductStmt->bindParam(":productID", $productID, PDO::PARAM_INT);
                    $deleteProductStmt->execute();
                }

                $tableName = "tbl_customizations";
                $idColumnName = "Customization_ID";
                break;
            case 'pre_order':
                $tableName = "tbl_preorder";
                $idColumnName = "Preorder_ID";
                break;
            case 'ready_made':
                $tableName = "tbl_ready_made_orders";
                $idColumnName = "ReadyMadeOrder_ID";
                break;
            default:
                echo "Invalid order type.";
                exit;
        }

        // Create query to delete record from selected table
        $query = "DELETE FROM $tableName WHERE $idColumnName = :progressID";

        // Prepare query and store to a statement variable
        $stmt = $pdo->prepare($query);

        // Bind the Progress ID parameter
        $stmt->bindParam(":progressID", $progressID, PDO::PARAM_INT);

        // Execute statement
        if ($stmt->execute()) {
            // Redirect to the list of progress records after deletion
            header("Location: read-all-progress-form.php");
            exit();
        } else {
            // Use implode to get the error message for older versions of PHP
            echo "Error deleting record: " . implode(":", $stmt->errorInfo());
        }
    } else {
        echo "<p>Progress ID and Order Type is required.</p>";
    }
}
?>
