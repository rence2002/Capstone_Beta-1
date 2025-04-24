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

// --- Retrieve form data ---
// Get required fields
$Progress_ID = $_POST['Progress_ID'] ?? null; // Use null coalescing for safety
$Product_Status = $_POST['Product_Status'] ?? null;
$Stop_Reason = $_POST['Stop_Reason'] ?? ''; // Default to empty string if not set

// Get optional fields
$Tracking_Number = !empty($_POST['Tracking_Number']) ? trim($_POST['Tracking_Number']) : null; // Trim and set to null if empty

// Get fields needed for context/potential future use, but not directly for the simplified update
$Order_Type = $_POST['Order_Type'] ?? null;
$Product_ID = $_POST['Product_ID'] ?? null;

// --- Basic Validation ---
if (empty($Progress_ID) || !is_numeric($Progress_ID)) {
    echo "Invalid or missing Progress ID.";
    exit();
}
if ($Product_Status === null || !is_numeric($Product_Status)) { // Check for null explicitly
    echo "Invalid or missing Product Status.";
    exit();
}
// Removed Order_Status validation as it's no longer received

// --- Handle file uploads for progress pictures ---
$uploadDir = "../uploads/progress_pics/"; // Relative path from this script's location
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif']; // Added GIF just in case
$maxFileSize = 5 * 1024 * 1024; // 5MB
$progressPicsUpdates = []; // Array to hold SQL update parts for pictures
$progressPicsValues = []; // Array to hold values for binding picture paths

// Create upload directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $percentage) {
    $fileInputName = "Progress_Pic_$percentage";
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
        $fileName = $_FILES[$fileInputName]['name'];
        $fileSize = $_FILES[$fileInputName]['size'];
        $fileType = mime_content_type($fileTmpPath); // More reliable type check
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Validate file type and size
        if (!in_array($fileType, $allowedTypes)) {
            // Consider logging this error instead of dying immediately in production
            die("Error: Invalid file type for $percentage% ($fileType). Only JPEG, PNG, GIF are allowed.");
        }
        if ($fileSize > $maxFileSize) {
             die("Error: File size exceeds the maximum limit of 5MB for $percentage%.");
        }

        // Generate unique file name and define paths
        $newFileName = uniqid("progress_{$percentage}_", true) . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;
        // Store the path relative to the web root's perspective for DB (adjust if needed)
        // Assuming 'uploads' is directly under Capstone_Beta
        $dbPath = "../uploads/progress_pics/" . $newFileName; // Path to store in DB

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $columnName = "Progress_Pic_$percentage";
            $paramName = ":Progress_Pic_$percentage";
            $progressPicsUpdates[] = "$columnName = $paramName"; // e.g., "Progress_Pic_10 = :Progress_Pic_10"
            $progressPicsValues[$paramName] = $dbPath; // e.g., [":Progress_Pic_10" => "../uploads/progress_pics/unique_name.jpg"]
        } else {
            // Consider logging this error
            die("Error: Failed to move uploaded file for $percentage%. Check permissions for $uploadDir");
        }
    } elseif (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors
        die("Error uploading file for $percentage%: Error code " . $_FILES[$fileInputName]['error']);
    }
}

// --- Database Update Logic ---
try {
    // Start a transaction
    $pdo->beginTransaction();

    // *** SIMPLIFIED: Update ONLY tbl_progress ***

    // Build the base update query for tbl_progress
    $updateFields = [
        "Product_Status = :Product_Status",
        "Stop_Reason = :Stop_Reason"
        // LastUpdate is handled by DB trigger/default
    ];

    // Add Tracking_Number to the update if it's provided
    if ($Tracking_Number !== null) {
        $updateFields[] = "Tracking_Number = :Tracking_Number";
    }

    // Add progress picture fields to the update query if any were uploaded
    if (!empty($progressPicsUpdates)) {
        $updateFields = array_merge($updateFields, $progressPicsUpdates);
    }

    // Construct the final SQL query
    $sql = "UPDATE tbl_progress SET " . implode(", ", $updateFields) . " WHERE Progress_ID = :Progress_ID";

    // Prepare the statement
    $stmt = $pdo->prepare($sql);

    // Bind core parameters
    $stmt->bindParam(':Product_Status', $Product_Status, PDO::PARAM_INT);
    $stmt->bindParam(':Stop_Reason', $Stop_Reason, PDO::PARAM_STR);
    $stmt->bindParam(':Progress_ID', $Progress_ID, PDO::PARAM_INT);

    // Bind Tracking_Number if it exists
    if ($Tracking_Number !== null) {
        $stmt->bindParam(':Tracking_Number', $Tracking_Number, PDO::PARAM_STR);
    }

    // Bind progress picture parameters if they exist
    foreach ($progressPicsValues as $paramName => $value) {
        $stmt->bindValue($paramName, $value, PDO::PARAM_STR);
    }

    // Execute the update query for tbl_progress
    $stmt->execute();

    // Optional: Check if rows were actually affected (useful for debugging)
    $rowCount = $stmt->rowCount();
    // error_log("Updated tbl_progress for Progress_ID: $Progress_ID. Rows affected: $rowCount");
    // if ($rowCount === 0) {
    //     // This might happen if the submitted data is identical to the existing data.
    //     // Decide if this is an error or just informational.
    //     // error_log("Warning: No rows updated for Progress_ID: $Progress_ID. Data might be unchanged.");
    // }


    // --- History Transfer Logic (Based ONLY on Product_Status) ---
    if ($Product_Status == 100) {
        // 1. Fetch the completed record from tbl_progress
        $fetchQuery = "SELECT * FROM tbl_progress WHERE Progress_ID = :Progress_ID";
        $fetchStmt = $pdo->prepare($fetchQuery);
        $fetchStmt->bindParam(':Progress_ID', $Progress_ID, PDO::PARAM_INT);
        $fetchStmt->execute();
        $progressRecord = $fetchStmt->fetch(PDO::FETCH_ASSOC);

        if ($progressRecord) {
            // 2. Insert the record into tbl_purchase_history (Removed Order_Status)
            $insertQuery = "
                INSERT INTO tbl_purchase_history (
                    User_ID, Product_ID, Product_Name, Quantity, Total_Price,
                    Order_Type, Purchase_Date, Product_Status -- Removed Order_Status
                ) VALUES (
                    :User_ID, :Product_ID, :Product_Name, :Quantity, :Total_Price,
                    :Order_Type, NOW(), :Product_Status -- Removed :Order_Status
                )
            ";
            $insertStmt = $pdo->prepare($insertQuery);

            // Bind parameters for tbl_purchase_history
            $insertStmt->bindParam(':User_ID', $progressRecord['User_ID']);
            $insertStmt->bindParam(':Product_ID', $progressRecord['Product_ID']);
            $insertStmt->bindParam(':Product_Name', $progressRecord['Product_Name']);
            $insertStmt->bindParam(':Quantity', $progressRecord['Quantity']);
            $insertStmt->bindParam(':Total_Price', $progressRecord['Total_Price']);
            $insertStmt->bindParam(':Order_Type', $progressRecord['Order_Type']);
            // Bind the Product_Status that triggered the completion
            $insertStmt->bindParam(':Product_Status', $Product_Status, PDO::PARAM_INT); // Use the variable from POST

            if (!$insertStmt->execute()) {
                throw new Exception("Failed to transfer record to tbl_purchase_history. Error: " . implode(", ", $insertStmt->errorInfo()));
            }

            // 3. Delete the record from tbl_progress
            $deleteQuery = "DELETE FROM tbl_progress WHERE Progress_ID = :Progress_ID";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->bindParam(':Progress_ID', $Progress_ID, PDO::PARAM_INT);

            if (!$deleteStmt->execute()) {
                throw new Exception("Failed to delete record from tbl_progress after history transfer. Error: " . implode(", ", $deleteStmt->errorInfo()));
            }
             // error_log("Transferred and deleted Progress_ID: $Progress_ID");

        } else {
             // This shouldn't happen if the update succeeded, but good to log
             error_log("Warning: Could not find Progress_ID $Progress_ID to transfer to history after update.");
        }
    }

    // Commit the transaction
    $pdo->commit();

    // Redirect on success
    header("Location: ../progress/read-all-progress-form.php?success=1&id=" . $Progress_ID); // Optionally pass ID back
    exit();

} catch (PDOException $e) {
    // Rollback the transaction in case of database error
    $pdo->rollBack();
    // Log the detailed error
    error_log("Database Error in update-progress-rec.php: " . $e->getMessage());
    echo "Database Error: Failed to update progress. Please check logs or contact support."; // User-friendly message
    // echo "Error: " . $e->getMessage(); // Show detailed error during development

} catch (Exception $e) {
    // Rollback the transaction for other errors (like file upload issues handled earlier)
    if ($pdo->inTransaction()) { // Check if transaction started before rolling back
       $pdo->rollBack();
    }
    // Log the error
    error_log("General Error in update-progress-rec.php: " . $e->getMessage());
    echo "Error: " . $e->getMessage(); // Show specific error message
}
?>
