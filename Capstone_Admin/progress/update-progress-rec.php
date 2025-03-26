<?php
session_start();

// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $progressID = $_POST['Progress_ID'];
    $orderType = $_POST['Order_Type'];
    $orderStatus = $_POST['Order_Status'];
    $productStatus = $_POST['Product_Status'];
    $totalPrice = $_POST['Total_Price'];
    $stopReason = $_POST['Stop_Reason'];
    $productName = $_POST['Product_Name'];
    $productID = $_POST['Product_ID'];
    $userID = $_POST['User_ID']; // Assuming you have User_ID in the form

    // Validate inputs
    if (empty($progressID) || empty($orderType) || empty($orderStatus) || empty($productStatus)) {
        echo "All fields are required.";
        exit();
    }

    $uploadDir = '../uploads/progress_pics/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $progressPics = [];

    foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $percentage) {
        $fileKey = "Progress_Pic_$percentage";
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES[$fileKey]['tmp_name'];
            $fileName = basename($_FILES[$fileKey]['name']);
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueIdentifier = time() . '_' . bin2hex(random_bytes(5)); // Unique identifier
            $newFileName = strtolower($orderType) . '_' . str_replace(' ', '_', $productName) . '_' . $percentage . '_' . $uniqueIdentifier . '.' . $fileExtension;
            $filePath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $filePath)) {
                $progressPics[$fileKey] = $filePath;
            }
        }
    }

    $query = "";
    $params = [
        ':order_status' => $orderStatus,
        ':product_status' => $productStatus,
        ':stop_reason' => $stopReason,
        ':progress_id' => $progressID,
    ];
     
    switch ($orderType) {
        case 'custom':
             //Check if product_id is set
            if (empty($productID)) {
                // Create a new product in tbl_prod_info
                $newProductQuery = "INSERT INTO tbl_prod_info (Product_Name, Price, Product_Type) VALUES (:product_name, :price, :product_type)";
                $newProductStmt = $pdo->prepare($newProductQuery);
                $newProductStmt->execute([
                    ':product_name' => $productName,
                    ':price' => $totalPrice, // Assuming you have total price when creating the product
                    ':product_type' => 'custom'
                ]);
                 //Get the id of the inserted row.
                $productID = $pdo->lastInsertId();

                //Update customization table with new product id
                $updateCustomization = "UPDATE tbl_customizations SET Product_ID = :product_id WHERE Customization_ID = :progress_id";
                $updateCustomizationStmt = $pdo->prepare($updateCustomization);
                $updateCustomizationStmt->execute([
                    ':product_id' => $productID,
                    ':progress_id' => $progressID
                ]);
            }
            else {
                //Update product in tbl_prod_info
                $updateProductQuery = "UPDATE tbl_prod_info SET Price = :price WHERE Product_ID = :product_id";
                $updateProductStmt = $pdo->prepare($updateProductQuery);
                $updateProductStmt->execute([
                    ':price' => $totalPrice,
                    ':product_id' => $productID
                ]);

            }
           
            $query = "UPDATE tbl_customizations SET
                Order_Status = :order_status,
                Product_Status = :product_status,
                Last_Update = NOW(),
                Stop_Reason = :stop_reason";
            $whereClause = " WHERE Customization_ID = :progress_id";
            break;
        case 'pre_order':
            $query = "UPDATE tbl_preorder SET
                Preorder_Status = :order_status,
                Product_Status = :product_status,
                Total_Price = :total_price,
                Order_Date = NOW(),
                Stop_Reason = :stop_reason";
            $whereClause = " WHERE Preorder_ID = :progress_id";
            $params[':total_price'] = $totalPrice;
            break;
        case 'ready_made':
            $query = "UPDATE tbl_ready_made_orders SET
                Order_Status = :order_status,
                Product_Status = :product_status,
                Total_Price = :total_price,
                Order_Date = NOW(),
                Stop_Reason = :stop_reason";
            $whereClause = " WHERE ReadyMadeOrder_ID = :progress_id";
            $params[':total_price'] = $totalPrice;
            break;
        default:
            echo "Invalid order type.";
            exit;
    }

    foreach ($progressPics as $key => $path) {
        $query .= ", $key = :$key";
    }

    //Append where clause to the query
    $query .= $whereClause;

    $stmt = $pdo->prepare($query);

    // Add the progress pics parameters only when they are used in the query
    foreach ($progressPics as $key => $path) {
        if (strpos($query, ":$key") !== false) { // Check if the placeholder exists in the query
            $params[":$key"] = $path;
        }
    }

    //Debugging
    //echo "Query : " . $query . "<br>";
    //echo "<pre>";
    //print_r($params);
    //echo "</pre>";


    if ($stmt->execute($params)) {
        // Insert into tbl_purchase_history if order status and product status are both 100
        if ($orderStatus == 100 && $productStatus == 100) {
            $insertHistoryQuery = "INSERT INTO tbl_purchase_history (User_ID, Product_ID, Product_Name, Quantity, Total_Price, Order_Type, Order_Status, Product_Status)
                                   VALUES (:user_id, :product_id, :product_name, 1, :total_price, :order_type, 100, 100)";
            $insertHistoryStmt = $pdo->prepare($insertHistoryQuery);
            $insertHistoryStmt->execute([
                ':user_id' => $userID,
                ':product_id' => $productID,
                ':product_name' => $productName,
                ':total_price' => $totalPrice,
                ':order_type' => $orderType
            ]);
        }

        header("Location: read-all-progress-form.php?message=update_success");
        exit();
    } else {
        echo "Failed to update the record.";
        echo "<pre>";
        print_r($stmt->errorInfo());
        echo "</pre>";
    }
} else {
    echo "Invalid request.";
}
?>
