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

// --- CORRECTION: Get Purchase_ID directly ---
$purchaseID = $_POST['record_id'] ?? null;
$orderType = $_POST['order_type'] ?? null; // Keep for logging/messaging

// Validate ID
if (!$purchaseID || !filter_var($purchaseID, FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "Error: Invalid or missing ID for deletion.";
    header("Location: read-all-history-form.php");
    exit();
}

$errorMessage = null; // Initialize error message

try {
    // --- CORRECTION: Delete ONLY from tbl_purchase_history ---
    $pdo->beginTransaction();

    $deleteQuery = "DELETE FROM tbl_purchase_history WHERE Purchase_ID = :purchase_id";
    $stmt = $pdo->prepare($deleteQuery);
    $stmt->bindParam(':purchase_id', $purchaseID, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Check if a row was actually deleted
        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            $_SESSION['success_message'] = "Purchase history record (ID: {$purchaseID}) deleted successfully.";
            header("Location: read-all-history-form.php");
            exit();
        } else {
            // If no rows were affected, the record might have already been deleted
            $pdo->rollBack();
            $_SESSION['error_message'] = "Record (ID: {$purchaseID}) not found in history or already deleted.";
            header("Location: read-all-history-form.php");
            exit();
        }
    } else {
        // Rollback and set error if deletion fails
        $pdo->rollBack();
        $errorMessage = "Error deleting history record.";
        error_log("Failed to delete Purchase_ID {$purchaseID}: " . print_r($stmt->errorInfo(), true));
        throw new Exception($errorMessage);
    }

} catch (PDOException $e) {
    // Catch database errors
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $errorMessage = "Database Error: " . $e->getMessage();
    error_log("Database Error during history delete (Purchase_ID: {$purchaseID}): " . $e->getMessage());

} catch (Exception $e) {
    // Catch errors thrown manually
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $errorMessage = $e->getMessage();
}

// If we reached here, an error occurred
$_SESSION['error_message'] = $errorMessage ?: "An unexpected error occurred during deletion.";
header("Location: read-all-history-form.php");
exit();

?>
