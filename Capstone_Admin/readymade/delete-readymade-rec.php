<?php
// Disable error reporting for production (enable for development if needed)
// error_reporting(E_ALL); // For debugging
error_reporting(0); // For production

// Include the database connection
include("../config/database.php");

// Check if 'id' is set and is a valid integer
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    // Get the Ready-Made Order ID to delete
    $readyMadeOrderID = (int) $_GET['id'];

    // First, retrieve the data about the order (product name, user name, etc.)
    $selectQuery = "
        SELECT 
            r.Product_ID, 
            pi.Product_Name, 
            r.User_ID, 
            ui.First_Name AS User_First_Name, 
            ui.Last_Name AS User_Last_Name, 
            r.Quantity, 
            r.Total_Price, 
            r.Order_Status, 
            r.Order_Date
        FROM tbl_ready_made_orders r
        JOIN tbl_prod_info pi ON r.Product_ID = pi.Product_ID
        JOIN tbl_user_info ui ON r.User_ID = ui.User_ID
        WHERE r.ReadyMadeOrder_ID = ?";

    // Prepare the SELECT query
    if ($stmt = $pdo->prepare($selectQuery)) {
        $stmt->bindValue(1, $readyMadeOrderID, PDO::PARAM_INT);

        // Execute and fetch the order data
        if ($stmt->execute()) {
            $orderData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($orderData) {
                // Store the fetched data to variables
                $productName = $orderData['Product_Name'];
                $userName = $orderData['User_First_Name'] . ' ' . $orderData['User_Last_Name']; // Full user name
                $quantity = $orderData['Quantity'];
                $totalPrice = $orderData['Total_Price'];
                $orderStatus = $orderData['Order_Status'];
                $orderDate = $orderData['Order_Date'];

                // Now, delete the order record from the tbl_ready_made_orders table
                $deleteQuery = "DELETE FROM tbl_ready_made_orders WHERE ReadyMadeOrder_ID = ?";
                
                // Prepare query for deletion
                if ($deleteStmt = $pdo->prepare($deleteQuery)) {
                    // Bind the Ready-Made Order ID to the parameter for deletion
                    $deleteStmt->bindValue(1, $readyMadeOrderID, PDO::PARAM_INT);

                    // Execute the delete statement
                    if ($deleteStmt->execute()) {
                        // Optionally log or confirm the deletion with details
                        // Redirect to the list page after successful deletion
                        header("Location: read-all-readymade-form.php"); // Redirect to the list page
                        exit();
                    } else {
                        echo "Error deleting the order. Please try again.";
                    }
                } else {
                    echo "Error preparing the delete statement.";
                }
            } else {
                echo "Order not found.";
            }
        } else {
            echo "Error fetching order details.";
        }
    } else {
        echo "Error preparing the select query.";
    }
} else {
    echo "Invalid order ID.";
    // Optionally, redirect back to the list page or show an error page
    header("Location: read-all-readymade-form.php"); // Redirect to the list page if the ID is invalid
    exit();
}
?>
