<?php
session_start();

// Include the database connection
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: read-all-history-form.php");
    exit();
}

// Get the ID and Order Type from the POST data
$recordId = $_POST['record_id'] ?? null;
$orderType = $_POST['order_type'] ?? null;

// Validate ID and Order Type
if (!$recordId || !filter_var($recordId, FILTER_VALIDATE_INT) || !$orderType) {
    $_SESSION['error_message'] = "Error: Invalid, missing ID, or missing Order Type for deletion.";
    header("Location: read-all-history-form.php");
    exit();
}

// --- Configuration & Setup ---
$tableName = '';
$idColumn = '';
$productIdColumn = 'Product_ID'; // Consistent column name across tables
$imageFields = []; // Specific image fields (like Color_Image_URL for custom)
$progressImageFields = [ // Common progress image fields
    'Progress_Pic_10', 'Progress_Pic_20', 'Progress_Pic_30', 'Progress_Pic_40',
    'Progress_Pic_50', 'Progress_Pic_60', 'Progress_Pic_70', 'Progress_Pic_80',
    'Progress_Pic_90', 'Progress_Pic_100'
];

switch ($orderType) {
    case 'custom':
        $tableName = 'tbl_customizations';
        $idColumn = 'Customization_ID';
        // Add specific customization image fields
        $imageFields = [
            'Color_Image_URL', 'Texture_Image_URL', 'Wood_Image_URL', 'Foam_Image_URL',
            'Cover_Image_URL', 'Design_Image_URL', 'Tile_Image_URL', 'Metal_Image_URL'
        ];
        break;
    case 'pre_order':
        $tableName = 'tbl_preorder';
        $idColumn = 'Preorder_ID';
        // No specific fields other than progress pics
        break;
    case 'ready_made':
        $tableName = 'tbl_ready_made_orders';
        $idColumn = 'ReadyMadeOrder_ID';
        // No specific fields other than progress pics
        break;
    default:
        $_SESSION['error_message'] = "Invalid order type specified for deletion.";
        header("Location: read-all-history-form.php");
        exit();
}

$allImageFields = array_merge($imageFields, $progressImageFields); // Combine all potential image fields
$errorMessage = null; // Initialize error message

try {
    // 1. Fetch the record to get Product_ID and image paths
    // Ensure Product_Status = 100 as we are deleting from history
    $stmtFetch = $pdo->prepare("SELECT * FROM `{$tableName}` WHERE `{$idColumn}` = :id AND `Product_Status` = 100");
    $stmtFetch->bindParam(':id', $recordId, PDO::PARAM_INT);
    $stmtFetch->execute();
    $recordData = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    if (!$recordData) {
        $_SESSION['error_message'] = "Completed record not found or already deleted.";
        header("Location: read-all-history-form.php");
        exit();
    }

    $productID = $recordData[$productIdColumn] ?? null; // Get the Product_ID

    // 2. Delete Associated Files (Do this *before* the transaction)
    foreach ($allImageFields as $field) {
        if (!empty($recordData[$field])) {
            $relativePath = $recordData[$field]; // e.g., ../uploads/customizations/image.jpg or ../uploads/progress/...

            // Construct absolute server path relative to Capstone_Admin directory
            // Script is in purchase-history/, paths start with ../uploads/
            $absolutePath = realpath(__DIR__ . '/../' . substr($relativePath, 3)); // Go up one level from __DIR__

            if ($absolutePath && file_exists($absolutePath)) {
                if (!unlink($absolutePath)) {
                    // Log error but continue
                    error_log("Failed to delete image file: " . $absolutePath . " for {$orderType} ID: " . $recordId);
                }
            } else {
                 error_log("Image file not found or path invalid: " . ($absolutePath ?: 'Invalid Path') . " (from DB: " . $relativePath . ") for {$orderType} ID: " . $recordId);
            }
        }
    }

    // 3. Database Deletions (Inside Transaction)
    $pdo->beginTransaction();

    // 3a. Attempt to delete associated progress record (if Product_ID exists) - Cleanup step
    if ($productID !== null) {
        // Note: We delete based on Product_ID and Order_Type, regardless of its status,
        // as the main record is confirmed completed (status 100).
        $deleteProgressQuery = "DELETE FROM tbl_progress WHERE Product_ID = :productID AND Order_Type = :orderType";
        $progressStmt = $pdo->prepare($deleteProgressQuery);
        $progressStmt->bindParam(':productID', $productID, PDO::PARAM_INT);
        $progressStmt->bindParam(':orderType', $orderType, PDO::PARAM_STR); // Bind order type

        // Execute, but don't necessarily fail the whole transaction if it doesn't delete
        // (it might have been correctly deleted earlier). Log if it fails unexpectedly.
        if (!$progressStmt->execute() && $progressStmt->errorCode() !== '00000') {
             // Log unexpected error during progress cleanup
             error_log("Unexpected error deleting potential progress record for Product_ID {$productID}, Order Type {$orderType}: " . print_r($progressStmt->errorInfo(), true));
             // Decide if this should cause a rollback - maybe not, as it's just cleanup.
             // $pdo->rollBack();
             // throw new Exception("Error cleaning up associated progress record.");
        }
    }

    // 3b. Delete the main history record itself
    $deleteMainQuery = "DELETE FROM `{$tableName}` WHERE `{$idColumn}` = :record_id";
    $mainStmt = $pdo->prepare($deleteMainQuery);
    $mainStmt->bindParam(':record_id', $recordId, PDO::PARAM_INT);

    if ($mainStmt->execute()) {
        // Check if the main record was actually deleted
        if ($mainStmt->rowCount() > 0) {
            // If main deletion is successful, commit the transaction
            $pdo->commit();
            $_SESSION['success_message'] = "Purchase history record (ID: {$recordId}, Type: {$orderType}) deleted successfully.";
            header("Location: read-all-history-form.php");
            exit();
        } else {
             // Should not happen if fetch succeeded, but handle defensively
             $pdo->rollBack();
             $errorMessage = "Record vanished before final deletion step.";
             throw new Exception($errorMessage);
        }
    } else {
        // Rollback and set error if main deletion fails
        $pdo->rollBack();
        $errorMessage = "Error deleting main history record.";
        error_log("Failed to delete {$orderType} ID {$recordId}: " . print_r($mainStmt->errorInfo(), true));
        throw new Exception($errorMessage);
    }

} catch (PDOException $e) {
    // Catch database errors
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $errorMessage = "Database Error: " . $e->getMessage();
    error_log("Database Error during history delete ({$orderType} ID: {$recordId}): " . $e->getMessage());

} catch (Exception $e) {
    // Catch errors thrown manually or other exceptions
    if ($pdo->inTransaction()) { // Ensure rollback if error happened mid-transaction
        $pdo->rollBack();
    }
    $errorMessage = $e->getMessage(); // Use the message from the thrown exception
}

// If we reached here, an error occurred
$_SESSION['error_message'] = $errorMessage ?: "An unexpected error occurred during deletion.";
header("Location: read-all-history-form.php");
exit();

?>
