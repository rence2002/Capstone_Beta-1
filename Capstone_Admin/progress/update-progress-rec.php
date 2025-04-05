<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Ensure POST data is received
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method.";
    exit();
}

// Retrieve form data
$Progress_ID = $_POST['Progress_ID'];
$Order_Type = $_POST['Order_Type'];
$Order_Status = $_POST['Order_Status'];
$Product_Status = $_POST['Product_Status'];
$Stop_Reason = $_POST['Stop_Reason'];
$Product_ID = $_POST['Product_ID'];

// Validate Progress_ID and Order_Type
if (empty($Progress_ID) || empty($Order_Type)) {
    echo "Progress ID and Order Type are required.";
    exit();
}

// Determine the correct table and primary key based on Order_Type
$tableName = "";
$idColumn = "";
$progressIdColumn = "";

switch ($Order_Type) {
    case 'custom':
        $tableName = "tbl_customizations";
        $idColumn = "Customization_ID"; // Primary key for tbl_customizations
        $progressIdColumn = "Progress_ID"; // Always use Progress_ID for tbl_progress
        break;
    case 'pre_order':
        $tableName = "tbl_preorder";
        $idColumn = "Preorder_ID"; // Primary key for tbl_preorder
        $progressIdColumn = "Progress_ID"; // Always use Progress_ID for tbl_progress
        break;
    case 'ready_made':
        $tableName = "tbl_ready_made_orders";
        $idColumn = "ReadyMadeOrder_ID"; // Primary key for tbl_ready_made_orders
        $progressIdColumn = "Progress_ID"; // Always use Progress_ID for tbl_progress
        break;
    default:
        echo "Invalid order type.";
        exit;
}

// Handle file uploads for progress pictures
$uploadDir = "../uploads/progress_pics/";
$allowedTypes = ['image/jpeg', 'image/png'];
$maxFileSize = 5 * 1024 * 1024; // 5MB
$progressPics = [];

foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $percentage) {
    $fileInputName = "Progress_Pic_$percentage";
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $fileType = $_FILES[$fileInputName]['type'];
        $fileSize = $_FILES[$fileInputName]['size'];

        // Validate file type and size
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Invalid file type for $percentage%. Only JPEG and PNG are allowed.");
        }
        if ($fileSize > $maxFileSize) {
            throw new Exception("File size exceeds the maximum limit of 5MB for $percentage%.");
        }

        // Generate unique file name and move the file
        $fileName = uniqid() . "_" . basename($_FILES[$fileInputName]['name']);
        $filePath = $uploadDir . $fileName;
        $relativePath = "../uploads/progress_pics/" . $fileName;

        if (!move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $filePath)) {
            throw new Exception("Failed to upload file for $percentage%.");
        }

        $progressPics["Progress_Pic_$percentage"] = $relativePath;
    }
}

try {
    // Start a transaction
    $pdo->beginTransaction();

    // For custom orders, fetch the Customization_ID from tbl_customizations
    if ($Order_Type === 'custom') {
        $fetchCustomizationQuery = "
            SELECT Customization_ID
            FROM tbl_customizations
            WHERE Product_ID = :Product_ID
        ";
        $fetchCustomizationStmt = $pdo->prepare($fetchCustomizationQuery);
        $fetchCustomizationStmt->bindParam(':Product_ID', $Product_ID, PDO::PARAM_INT);
        $fetchCustomizationStmt->execute();
        $customizationRecord = $fetchCustomizationStmt->fetch(PDO::FETCH_ASSOC);

        if (!$customizationRecord) {
            throw new Exception("Customization record not found for Product_ID $Product_ID.");
        }

        $Customization_ID = $customizationRecord['Customization_ID'];

        // Debugging: Confirm Customization_ID
        error_log("Fetched Customization_ID: $Customization_ID for Product_ID: $Product_ID");
    }

    // Base query to update the specific table
    $query = "
        UPDATE $tableName
        SET 
            Order_Status = :Order_Status,
            Product_Status = :Product_Status,
            Stop_Reason = :Stop_Reason
    ";

    // Add progress picture fields to the query if they exist
    if (!empty($progressPics)) {
        $query .= ", " . implode(", ", array_map(fn($key) => "$key = :$key", array_keys($progressPics)));
    }

    $query .= " WHERE $idColumn = :ID";

    // Prepare the statement
    $stmt = $pdo->prepare($query);

    // Bind parameters
    $stmt->bindParam(':Order_Status', $Order_Status, PDO::PARAM_INT);
    $stmt->bindParam(':Product_Status', $Product_Status, PDO::PARAM_INT);
    $stmt->bindParam(':Stop_Reason', $Stop_Reason, PDO::PARAM_STR);

    if ($Order_Type === 'custom') {
        $stmt->bindParam(':ID', $Customization_ID, PDO::PARAM_INT); // Use Customization_ID for custom orders
    } else {
        $stmt->bindParam(':ID', $Progress_ID, PDO::PARAM_INT); // Use Preorder_ID or ReadyMadeOrder_ID for other orders
    }

    // Bind progress picture parameters if they exist
    foreach ($progressPics as $key => $value) {
        $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
    }

    // Execute the query
    $rowsAffected = $stmt->execute();
    if (!$rowsAffected || $stmt->rowCount() === 0) {
        throw new Exception("Failed to update progress record in $tableName. Rows affected: " . $stmt->rowCount());
    }

    // Debugging: Confirm rows affected
    error_log("Updated $tableName with ID: " . ($Order_Type === 'custom' ? $Customization_ID : $Progress_ID));

    // Update the corresponding record in tbl_progress
    $progressQuery = "
        UPDATE tbl_progress
        SET 
            Order_Status = :Order_Status,
            Product_Status = :Product_Status,
            Stop_Reason = :Stop_Reason
    ";

    // Add progress picture fields to the query if they exist
    if (!empty($progressPics)) {
        $progressQuery .= ", " . implode(", ", array_map(fn($key) => "$key = :$key", array_keys($progressPics)));
    }

    $progressQuery .= " WHERE $progressIdColumn = :Progress_ID";

    // Prepare the statement for tbl_progress
    $progressStmt = $pdo->prepare($progressQuery);

    // Bind parameters
    $progressStmt->bindParam(':Order_Status', $Order_Status, PDO::PARAM_INT);
    $progressStmt->bindParam(':Product_Status', $Product_Status, PDO::PARAM_INT);
    $progressStmt->bindParam(':Stop_Reason', $Stop_Reason, PDO::PARAM_STR);
    $progressStmt->bindParam(':Progress_ID', $Progress_ID, PDO::PARAM_INT);

    // Bind progress picture parameters if they exist
    foreach ($progressPics as $key => $value) {
        $progressStmt->bindValue(":$key", $value, PDO::PARAM_STR);
    }

    // Execute the query for tbl_progress
    $rowsAffectedProgress = $progressStmt->execute();
    if (!$rowsAffectedProgress || $progressStmt->rowCount() === 0) {
        throw new Exception("Failed to update progress record in tbl_progress. Rows affected: " . $progressStmt->rowCount());
    }

    // Debugging: Confirm rows affected
    error_log("Updated tbl_progress with Progress_ID: $Progress_ID");

    // Check if both Order_Status and Product_Status are 100
    if ($Order_Status == 100 && $Product_Status == 100) {
        // Fetch the record from tbl_progress
        $fetchQuery = "
            SELECT * 
            FROM tbl_progress 
            WHERE $progressIdColumn = :Progress_ID
        ";
        $fetchStmt = $pdo->prepare($fetchQuery);
        $fetchStmt->bindParam(':Progress_ID', $Progress_ID, PDO::PARAM_INT);
        $fetchStmt->execute();
        $progressRecord = $fetchStmt->fetch(PDO::FETCH_ASSOC);

        if ($progressRecord) {
            // Insert the record into tbl_purchase_history
            $insertQuery = "
                INSERT INTO tbl_purchase_history (
                    User_ID, Product_ID, Product_Name, Quantity, Total_Price, 
                    Order_Type, Purchase_Date, Order_Status, Product_Status
                ) VALUES (
                    :User_ID, :Product_ID, :Product_Name, :Quantity, :Total_Price, 
                    :Order_Type, NOW(), :Order_Status, :Product_Status
                )
            ";
            $insertStmt = $pdo->prepare($insertQuery);

            // Bind parameters for tbl_purchase_history
            $insertStmt->bindParam(':User_ID', $progressRecord['User_ID'], PDO::PARAM_STR);
            $insertStmt->bindParam(':Product_ID', $progressRecord['Product_ID'], PDO::PARAM_INT);
            $insertStmt->bindParam(':Product_Name', $progressRecord['Product_Name'], PDO::PARAM_STR);
            $insertStmt->bindParam(':Quantity', $progressRecord['Quantity'], PDO::PARAM_INT);
            $insertStmt->bindParam(':Total_Price', $progressRecord['Total_Price'], PDO::PARAM_STR);
            $insertStmt->bindParam(':Order_Type', $progressRecord['Order_Type'], PDO::PARAM_STR);
            $insertStmt->bindParam(':Order_Status', $Order_Status, PDO::PARAM_INT);
            $insertStmt->bindParam(':Product_Status', $Product_Status, PDO::PARAM_INT);

            // Execute the insert query
            if (!$insertStmt->execute()) {
                throw new Exception("Failed to transfer record to tbl_purchase_history.");
            }

            // Delete the record from tbl_progress
            $deleteQuery = "
                DELETE FROM tbl_progress 
                WHERE $progressIdColumn = :Progress_ID
            ";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->bindParam(':Progress_ID', $Progress_ID, PDO::PARAM_INT);

            // Execute the delete query
            if (!$deleteStmt->execute()) {
                throw new Exception("Failed to delete record from tbl_progress.");
            }
        }
    }

    // Commit the transaction
    $pdo->commit();

    // Redirect to the read-all-progress-form.php page with success message
    header("Location: ../progress/read-all-progress-form.php?success=1");
    exit();

} catch (Exception $e) {
    // Rollback the transaction in case of error
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}