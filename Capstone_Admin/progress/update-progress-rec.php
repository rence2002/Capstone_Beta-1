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

// Debug: Log POST data
error_log("POST Data: " . print_r($_POST, true));

// Validate required fields
if (empty($_POST['Progress_ID']) || empty($_POST['Product_Status']) || empty($_POST['Quantity'])) {
    $_SESSION['error'] = "Progress ID, Product Status, and Quantity are required fields.";
    header("Location: update-progress-form.php?id=" . $_POST['Progress_ID']);
    exit();
}

// Validate quantity
$quantity = (int)$_POST['Quantity'];
if ($quantity < 1) {
    $_SESSION['error'] = "Quantity must be at least 1.";
    header("Location: update-progress-form.php?id=" . $_POST['Progress_ID']);
    exit();
}

// Get total price
$totalPrice = null;
if (!empty($_POST['Total_Price'])) {
    $totalPrice = (float)$_POST['Total_Price'];
    if ($totalPrice < 0) {
        $_SESSION['error'] = "Total price cannot be negative.";
        header("Location: update-progress-form.php?id=" . $_POST['Progress_ID']);
        exit();
    }
} else {
    // For non-custom products, calculate price based on product info
    if ($_POST['Order_Type'] !== 'custom') {
        $stmt = $pdo->prepare("
            SELECT pi.Price 
            FROM tbl_prod_info pi 
            JOIN tbl_progress p ON pi.Product_ID = p.Product_ID 
            WHERE p.Progress_ID = ?
        ");
        $stmt->execute([$_POST['Progress_ID']]);
        $basePrice = $stmt->fetchColumn();
        $totalPrice = $quantity * $basePrice;
    } else {
        $_SESSION['error'] = "Total price is required for custom products.";
        header("Location: update-progress-form.php?id=" . $_POST['Progress_ID']);
        exit();
    }
}

// Debug: Log calculated values
error_log("Calculated Values - Quantity: $quantity, Total Price: $totalPrice");

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
    // Start a transaction to ensure both tables are updated
    $pdo->beginTransaction();

    // First, verify the progress record exists and get its details
    $stmt = $pdo->prepare("
        SELECT p.*, c.Customization_ID 
        FROM tbl_progress p
        LEFT JOIN tbl_customizations c ON p.Product_ID = c.Product_ID
        WHERE p.Progress_ID = ?
    ");
    $stmt->execute([$_POST['Progress_ID']]);
    $progressRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$progressRecord) {
        throw new Exception("Progress record not found");
    }
    
    error_log("Existing Progress Record: " . print_r($progressRecord, true));

    // Update tbl_progress
    $stmt = $pdo->prepare("
        UPDATE tbl_progress 
        SET 
            Product_Status = ?,
            Stop_Reason = ?,
            Quantity = ?,
            Total_Price = ?,
            LastUpdate = NOW()
        WHERE Progress_ID = ?
    ");

    $updateParams = [
        $_POST['Product_Status'],
        $_POST['Stop_Reason'] ?? null,
        $quantity,
        $totalPrice,
        $_POST['Progress_ID']
    ];

    error_log("Updating tbl_progress with params: " . print_r($updateParams, true));
    $stmt->execute($updateParams);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("No rows were updated in tbl_progress");
    }

    // If it's a custom product, also update tbl_customizations
    if ($_POST['Order_Type'] === 'custom') {
        // First try to find the customization record
        $stmt = $pdo->prepare("
            SELECT Customization_ID 
            FROM tbl_customizations 
            WHERE Product_ID = ?
        ");
        $stmt->execute([$_POST['Product_ID']]);
        $customizationId = $stmt->fetchColumn();
        
        error_log("Found Customization_ID: " . ($customizationId ?: 'not found'));

        if ($customizationId) {
            // Update tbl_customizations using Customization_ID
            $stmt = $pdo->prepare("
                UPDATE tbl_customizations 
                SET Total_Price = ?,
                    Last_Update = NOW()
                WHERE Customization_ID = ?
            ");
            $stmt->execute([$totalPrice, $customizationId]);
            
            if ($stmt->rowCount() === 0) {
                error_log("Warning: No rows were updated in tbl_customizations for Customization_ID: " . $customizationId);
            } else {
                error_log("Successfully updated tbl_customizations");
            }
        } else {
            error_log("Warning: No customization record found for Product_ID: " . $_POST['Product_ID']);
        }
    }

    // Commit the transaction
    $pdo->commit();
    error_log("Transaction committed successfully");

    $_SESSION['success'] = "Progress updated successfully.";
    header("Location: read-all-progress-form.php");
    exit();

} catch (Exception $e) {
    // Rollback the transaction on error
    $pdo->rollBack();
    error_log("Error updating progress: " . $e->getMessage());
    $_SESSION['error'] = "Error updating progress: " . $e->getMessage();
    header("Location: update-progress-form.php?id=" . $_POST['Progress_ID']);
    exit();
}
?>
