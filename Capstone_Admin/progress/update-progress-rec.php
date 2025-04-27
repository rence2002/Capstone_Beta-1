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
$progressPics = [];
$progressPicsFields = [
    'Progress_Pic_20', 'Progress_Pic_30', 'Progress_Pic_40',
    'Progress_Pic_50', 'Progress_Pic_60', 'Progress_Pic_70', 
    'Progress_Pic_80', 'Progress_Pic_90', 'Progress_Pic_100'
];

// Define the upload directory
$uploadDir = 'C:/xampp/htdocs/Capstone_Beta/uploads/progress/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        error_log("Failed to create upload directory: " . $uploadDir);
        $_SESSION['error'] = "Failed to create upload directory. Please check server permissions.";
        header("Location: update-progress-form.php?id=" . $_POST['Progress_ID']);
        exit();
    }
}

// First, fetch existing progress pictures
$stmt = $pdo->prepare("SELECT * FROM tbl_progress WHERE Progress_ID = ?");
$stmt->execute([$_POST['Progress_ID']]);
$existingProgress = $stmt->fetch(PDO::FETCH_ASSOC);

foreach ($progressPicsFields as $field) {
    if (!empty($_FILES[$field]['name'])) {
        $fileName = basename($_FILES[$field]['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES[$field]['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            error_log("Invalid file type: " . $fileType);
            $_SESSION['error'] = "Invalid file type. Only JPG, PNG, and GIF images are allowed.";
            header("Location: update-progress-form.php?id=" . $_POST['Progress_ID']);
            exit();
        }
        
        if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetPath)) {
            // Store relative path for database
            $relativePath = 'uploads/progress/' . $fileName;
            $progressPics[$field] = $relativePath;

            // Delete old image if it exists
            if (!empty($existingProgress[$field])) {
                $oldImagePath = 'C:/xampp/htdocs/Capstone_Beta/' . $existingProgress[$field];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        } else {
            error_log("Failed to upload file: " . $fileName);
            $_SESSION['error'] = "Failed to upload file: " . $fileName;
            header("Location: update-progress-form.php?id=" . $_POST['Progress_ID']);
            exit();
        }
    } else {
        // Keep existing image if no new upload
        $progressPics[$field] = $existingProgress[$field];
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
            Tracking_Number = ?,
            Progress_Pic_20 = ?,
            Progress_Pic_30 = ?,
            Progress_Pic_40 = ?,
            Progress_Pic_50 = ?,
            Progress_Pic_60 = ?,
            Progress_Pic_70 = ?,
            Progress_Pic_80 = ?,
            Progress_Pic_90 = ?,
            Progress_Pic_100 = ?,
            LastUpdate = NOW()
        WHERE Progress_ID = ?
    ");

    // Ensure all parameters are properly set
    $productStatus = $_POST['Product_Status'] ?? 0;
    $stopReason = $_POST['Stop_Reason'] ?? null;
    $quantity = (int)($_POST['Quantity'] ?? 1);
    $progressId = (int)($_POST['Progress_ID'] ?? 0);
    $trackingNumber = $_POST['Tracking_Number'] ?? null;

    // Use the calculated total price
    $updateParams = [
        $productStatus,
        $stopReason,
        $quantity,
        $totalPrice,
        $trackingNumber,
        $progressPics['Progress_Pic_20'] ?? null,
        $progressPics['Progress_Pic_30'] ?? null,
        $progressPics['Progress_Pic_40'] ?? null,
        $progressPics['Progress_Pic_50'] ?? null,
        $progressPics['Progress_Pic_60'] ?? null,
        $progressPics['Progress_Pic_70'] ?? null,
        $progressPics['Progress_Pic_80'] ?? null,
        $progressPics['Progress_Pic_90'] ?? null,
        $progressPics['Progress_Pic_100'] ?? null,
        $progressId
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
