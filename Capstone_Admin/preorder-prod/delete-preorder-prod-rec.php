<?php
// Disable error reporting for production (enable for development if needed)
// error_reporting(E_ALL); // For debugging
error_reporting(0); // For production

// Include the database connection
include("../config/database.php");

// Check if 'id' is set and is a valid integer
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    // Get the Preorder ID to delete
    $preorderID = (int) $_GET['id'];

    // First, retrieve the data about the preorder (product name, user name, etc.)
    $selectQuery = "
        SELECT 
            p.Product_ID, 
            pi.Product_Name, 
            p.User_ID, 
            ui.First_Name AS User_First_Name, 
            ui.Last_Name AS User_Last_Name, 
            p.Quantity, 
            p.Total_Price, 
            p.Preorder_Status 
        FROM tbl_preorder p
        JOIN tbl_prod_info pi ON p.Product_ID = pi.Product_ID
        JOIN tbl_user_info ui ON p.User_ID = ui.User_ID
        WHERE p.Preorder_ID = ?";

    // Prepare the SELECT query
    if ($stmt = $pdo->prepare($selectQuery)) {
        $stmt->bindValue(1, $preorderID, PDO::PARAM_INT);

        // Execute and fetch the preorder data
        if ($stmt->execute()) {
            $preorderData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($preorderData) {
                // Store the fetched data to variables
                $productName = $preorderData['Product_Name'];
                $userName = $preorderData['User_First_Name'] . ' ' . $preorderData['User_Last_Name']; // Full user name
                $quantity = $preorderData['Quantity'];
                $totalPrice = $preorderData['Total_Price'];
                $preorderStatus = $preorderData['Preorder_Status'];

                // Now, delete the preorder record from the tbl_preorder table
                $deleteQuery = "DELETE FROM tbl_preorder WHERE Preorder_ID = ?";
                
                // Prepare query for deletion
                if ($deleteStmt = $pdo->prepare($deleteQuery)) {
                    // Bind the Preorder ID to the parameter for deletion
                    $deleteStmt->bindValue(1, $preorderID, PDO::PARAM_INT);

                    // Execute the delete statement
                    if ($deleteStmt->execute()) {
                        // Optionally log or confirm the deletion with details
                        // Redirect to the list page after successful deletion
                        header("Location: read-all-preorder-prod-form.php"); // Redirect to the list page
                        exit();
                    } else {
                        echo "Error deleting preorder. Please try again.";
                    }
                } else {
                    echo "Error preparing the delete statement.";
                }
            } else {
                echo "Preorder not found.";
            }
        } else {
            echo "Error fetching preorder details.";
        }
    } else {
        echo "Error preparing the select query.";
    }
} else {
    echo "Invalid preorder ID.";
    // Optionally, redirect back to the list page or show an error page
    header("Location: read-all-preorder-prod-form.php"); // Redirect to the list page if the ID is invalid
    exit();
}
?>
