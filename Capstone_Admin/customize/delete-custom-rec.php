<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Use a more consistent relative path
    header("Location: ../login.php");
    exit();
}

// --- Configuration ---
// Define the base directory for uploads relative to *this* script's location
// This script is in Capstone_Admin/customize/, uploads are in Capstone_Admin/uploads/
// We need to go up one level from __DIR__ to reach Capstone_Admin/
$uploadBaseDirServer = realpath(__DIR__ . '/../uploads/customizations/'); // Base absolute path for file deletion

// Check if 'id' parameter is set in the URL
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: read-all-custom-form.php?error=" . urlencode("Invalid or missing Customization ID."));
    exit();
}

$customizationID = (int) $_GET['id'];
$errorMessage = null; // To store potential error messages

try {
    // 1. Fetch the customization record to get Product_ID and image paths
    $stmtFetch = $pdo->prepare("SELECT * FROM tbl_customizations WHERE Customization_ID = :id");
    $stmtFetch->bindParam(':id', $customizationID, PDO::PARAM_INT);
    $stmtFetch->execute();
    $customizationData = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    if (!$customizationData) {
        header("Location: read-all-custom-form.php?error=" . urlencode("Customization record not found."));
        exit();
    }

    $productID = $customizationData['Product_ID']; // May be NULL

    // 2. Delete Associated Files (Do this *before* the transaction)
    $imageFields = [
        'Color_Image_URL', 'Texture_Image_URL', 'Wood_Image_URL', 'Foam_Image_URL',
        'Cover_Image_URL', 'Design_Image_URL', 'Tile_Image_URL', 'Metal_Image_URL',
        'Progress_Pic_10', 'Progress_Pic_20', 'Progress_Pic_30', 'Progress_Pic_40',
        'Progress_Pic_50', 'Progress_Pic_60', 'Progress_Pic_70', 'Progress_Pic_80',
        'Progress_Pic_90', 'Progress_Pic_100'
    ];

    foreach ($imageFields as $field) {
        if (!empty($customizationData[$field])) {
            $relativePath = $customizationData[$field]; // e.g., ../uploads/customizations/image.jpg

            // Construct absolute server path relative to Capstone_Admin directory
            // Assumes paths stored like '../uploads/customizations/...'
            $absolutePath = realpath(__DIR__ . '/../' . substr($relativePath, 3)); // Go up one level from __DIR__

            if ($absolutePath && file_exists($absolutePath)) {
                if (!unlink($absolutePath)) {
                    // Log error but continue, as DB deletion is more critical
                    error_log("Failed to delete image file: " . $absolutePath . " for Customization ID: " . $customizationID);
                }
            } else {
                 error_log("Image file not found or path invalid: " . $absolutePath . " (from DB: " . $relativePath . ") for Customization ID: " . $customizationID);
            }
        }
    }

    // 3. Database Deletions (Inside Transaction)
    $pdo->beginTransaction();

    // 3a. Delete associated progress record (if Product_ID exists)
    if ($productID !== null) {
        $deleteProgressQuery = "DELETE FROM tbl_progress WHERE Product_ID = :productID AND Order_Type = 'custom'";
        $progressStmt = $pdo->prepare($deleteProgressQuery);
        $progressStmt->bindParam(':productID', $productID, PDO::PARAM_INT);
        if (!$progressStmt->execute()) {
            // Rollback and set error if progress deletion fails
            $pdo->rollBack();
            $errorMessage = "Error deleting associated progress record.";
            // Log error: error_log("Failed to delete progress for Product_ID $productID: " . print_r($progressStmt->errorInfo(), true));
            throw new Exception($errorMessage); // Throw to trigger catch block
        }
    }

    // 3b. Delete the customization record itself
    $deleteCustomQuery = "DELETE FROM tbl_customizations WHERE Customization_ID = :customizationID";
    $customStmt = $pdo->prepare($deleteCustomQuery);
    $customStmt->bindParam(':customizationID', $customizationID, PDO::PARAM_INT);

    if ($customStmt->execute()) {
        // If customization deletion is successful, commit the transaction
        $pdo->commit();
        header("Location: read-all-custom-form.php?message=" . urlencode("Customization record and associated data deleted successfully."));
        exit();
    } else {
        // Rollback and set error if customization deletion fails
        $pdo->rollBack();
        $errorMessage = "Error deleting customization record.";
         // Log error: error_log("Failed to delete customization ID $customizationID: " . print_r($customStmt->errorInfo(), true));
        throw new Exception($errorMessage); // Throw to trigger catch block
    }

} catch (PDOException $e) {
    // Catch database errors (potentially during fetch or transaction)
    if ($pdo->inTransaction()) {
        $pdo->rollBack(); // Ensure rollback on PDO exception
    }
    $errorMessage = "Database Error: " . $e->getMessage();
    // Log error: error_log("Database Error during customization delete (ID: $customizationID): " . $e->getMessage());

} catch (Exception $e) {
    // Catch errors thrown manually (like failed deletes within transaction)
    // Error message is already set in $errorMessage
    $errorMessage = $e->getMessage(); // Use the message from the thrown exception
}

// If we reached here, an error occurred
header("Location: read-all-custom-form.php?error=" . urlencode($errorMessage ?: "An unexpected error occurred during deletion."));
exit();

?>
