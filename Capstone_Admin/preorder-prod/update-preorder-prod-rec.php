<?php
// CALL DATABASE CONNECTION SCRIPT
include("../config/database.php");

try {
    // GET USER INPUT FROM WEB FORM
    $preorderID = isset($_POST['txtPreorderID']) ? $_POST['txtPreorderID'] : null;
    $productName = isset($_POST['txtProductName']) ? $_POST['txtProductName'] : null;
    $userID = isset($_POST['txtUserID']) ? $_POST['txtUserID'] : null;
    $quantity = isset($_POST['txtQuantity']) ? $_POST['txtQuantity'] : null;
    $totalPrice = isset($_POST['txtTotalPrice']) ? $_POST['txtTotalPrice'] : null;
    $preorderStatus = isset($_POST['status']) ? (int)$_POST['status'] : null; // Ensure it's an integer
    $productStatus = isset($_POST['product_status']) ? (int)$_POST['product_status'] : null;

    // Fetch the ProductID based on ProductName
    $productID = null;
    if ($productName) {
        $stmt = $pdo->prepare("SELECT Product_ID FROM tbl_prod_info WHERE Product_Name = :productName LIMIT 1");
        $stmt->bindParam(':productName', $productName, PDO::PARAM_STR);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $productID = $product ? $product['Product_ID'] : null;
    }

    // Check if all required fields are present
    if ($preorderID && $productID && $userID && $quantity && $totalPrice !== null && $preorderStatus !== null && $productStatus !== null) {
        // UPDATE PREORDER RECORD
        $query = "UPDATE tbl_preorder SET 
            Product_ID = :productID, 
            User_ID = :userID, 
            Quantity = :quantity, 
            Total_Price = :totalPrice, 
            Preorder_Status = :preorderStatus, 
            Product_Status = :productStatus
            WHERE Preorder_ID = :preorderID";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":preorderID", $preorderID, PDO::PARAM_INT);
        $stmt->bindParam(":productID", $productID, PDO::PARAM_INT);
        $stmt->bindParam(":userID", $userID, PDO::PARAM_STR);
        $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindParam(":totalPrice", $totalPrice, PDO::PARAM_STR);
        $stmt->bindParam(":preorderStatus", $preorderStatus, PDO::PARAM_INT);
        $stmt->bindParam(":productStatus", $productStatus, PDO::PARAM_INT);

        // After successfully updating the preorder record
        if ($stmt->execute()) {
            // INSERT or UPDATE PURCHASE HISTORY
            $purchaseHistoryQuery = "INSERT INTO tbl_purchase_history 
                (User_ID, Product_ID, Product_Name, Quantity, Total_Price, Order_Type, Order_Status) 
                VALUES (:userID, :productID, :productName, :quantity, :totalPrice, 'pre_order', :status)
                ON DUPLICATE KEY UPDATE 
                Quantity = VALUES(Quantity), 
                Total_Price = VALUES(Total_Price), 
                Order_Status = VALUES(Order_Status), 
                Purchase_Date = CURRENT_TIMESTAMP";

            $purchaseHistoryStmt = $pdo->prepare($purchaseHistoryQuery);
            $purchaseHistoryStmt->bindParam(':userID', $userID, PDO::PARAM_STR);
            $purchaseHistoryStmt->bindParam(':productID', $productID, PDO::PARAM_INT);
            $purchaseHistoryStmt->bindParam(':productName', $productName, PDO::PARAM_STR);
            $purchaseHistoryStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $purchaseHistoryStmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);
            $purchaseHistoryStmt->bindParam(':status', $preorderStatus, PDO::PARAM_INT);
            $purchaseHistoryStmt->execute();

            // Update tbl_progress with the new statuses for pre_order
            $progressUpdateQuery = "UPDATE tbl_progress 
                                    SET Order_Status = :orderStatus, Product_Status = :productStatus 
                                    WHERE Product_ID = :productID AND Order_Type = 'pre_order'";
            $progressStmt = $pdo->prepare($progressUpdateQuery);
            $progressStmt->bindParam(':orderStatus', $preorderStatus, PDO::PARAM_INT);
            $progressStmt->bindParam(':productStatus', $productStatus, PDO::PARAM_INT);
            $progressStmt->bindParam(':productID', $productID, PDO::PARAM_INT);
            $progressStmt->execute();

            // **Check if both statuses in tbl_progress are 100 and delete if so**
            $checkProgressQuery = "SELECT Order_Status, Product_Status FROM tbl_progress WHERE Product_ID = :productID AND Order_Type = 'pre_order'";
            $checkProgressStmt = $pdo->prepare($checkProgressQuery);
            $checkProgressStmt->bindParam(':productID', $productID, PDO::PARAM_INT);
            $checkProgressStmt->execute();
            $progressData = $checkProgressStmt->fetch(PDO::FETCH_ASSOC);

            if ($progressData && $progressData['Order_Status'] == 100 && $progressData['Product_Status'] == 100) {
                $deleteProgressQuery = "DELETE FROM tbl_progress WHERE Product_ID = :productID AND Order_Type = 'pre_order'";
                $deleteProgressStmt = $pdo->prepare($deleteProgressQuery);
                $deleteProgressStmt->bindParam(':productID', $productID, PDO::PARAM_INT);
                $deleteProgressStmt->execute();
            }

            // Redirect to the list page after successful update
            header("Location: read-all-preorder-prod-form.php");
            exit();
        } else {
            throw new Exception("Error updating record: " . implode(", ", $stmt->errorInfo()));
        }
    } else {
        // Show missing fields message
        throw new Exception("Error: Missing required fields: " . implode(', ', array_filter([
            empty($preorderID) ? 'PreorderID' : '',
            empty($productID) ? 'ProductID' : '',
            empty($userID) ? 'UserID' : '',
            empty($quantity) ? 'Quantity' : '',
            $totalPrice === null ? 'TotalPrice' : '',
            $preorderStatus === null ? 'PreorderStatus' : '',
            $productStatus === null ? 'ProductStatus' : ''
        ])));
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>
