<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log raw POST data
error_log("Raw POST data: " . print_r($_POST, true));
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content type: " . $_SERVER['CONTENT_TYPE']);

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include database connection
include("../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["request_id"]) && isset($_POST["reference_number"])) {
    $requestId = $_POST["request_id"];
    $referenceNumber = trim($_POST["reference_number"]);
    $userId = $_SESSION["user_id"];

    // Log input data with more detail
    error_log("Input Data - Request ID: " . var_export($requestId, true));
    error_log("Input Data - Reference Number: " . var_export($referenceNumber, true));
    error_log("Input Data - User ID: " . var_export($userId, true));
    error_log("Input Data - Reference Number length: " . strlen($referenceNumber));
    error_log("Input Data - Reference Number is empty: " . (empty($referenceNumber) ? 'true' : 'false'));

    // Validate reference number
    if (empty($referenceNumber)) {
        error_log("Empty reference number provided - Details: " . print_r([
            'request_id' => $requestId,
            'reference_number' => $referenceNumber,
            'user_id' => $userId,
            'post_data' => $_POST
        ], true));
        header("Location: profile.php?error=empty_reference&request_id=" . $requestId);
        exit;
    }

    // Log input data
    error_log("Input Data - Request ID: $requestId, Reference Number: $referenceNumber, User ID: $userId");

    try {
        // Start transaction
        $pdo->beginTransaction();
        error_log("Transaction started");

        // First check the current submission attempts
        $checkStmt = $pdo->prepare("
            SELECT Payment_Reference_Number, Submission_Attempts, Payment_Status
            FROM tbl_order_request 
            WHERE Request_ID = :request_id AND User_ID = :user_id AND Payment_Status = 'Pending'
        ");
        $checkStmt->execute([
            'request_id' => $requestId,
            'user_id' => $userId
        ]);
        $orderData = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        // Log the fetched data
        error_log("Fetched Order Data: " . print_r($orderData, true));
        
        if ($orderData) {
            // Check if this is a resubmission
            if ($orderData['Payment_Reference_Number'] !== null) {
                error_log("Resubmission detected. Current attempts: " . $orderData['Submission_Attempts']);
                
                // Check if max attempts reached
                if ($orderData['Submission_Attempts'] >= 3) {
                    error_log("Max attempts reached. Rolling back transaction.");
                    $pdo->rollBack();
                    header("Location: profile.php?error=max_attempts_reached&request_id=" . $requestId);
                    exit;
                }
                
                // Increment submission attempts
                $updateStmt = $pdo->prepare("
                    UPDATE tbl_order_request 
                    SET Payment_Reference_Number = :reference_number,
                        Submission_Attempts = Submission_Attempts + 1
                    WHERE Request_ID = :request_id
                ");
                error_log("Preparing update for resubmission");
            } else {
                // First submission
                $updateStmt = $pdo->prepare("
                    UPDATE tbl_order_request 
                    SET Payment_Reference_Number = :reference_number,
                        Submission_Attempts = 1
                    WHERE Request_ID = :request_id
                ");
                error_log("Preparing update for first submission");
            }
            
            // Bind parameters with explicit types
            $updateStmt->bindParam(':reference_number', $referenceNumber, PDO::PARAM_STR);
            $updateStmt->bindParam(':request_id', $requestId, PDO::PARAM_INT);
            
            $updateStmt->execute();
            error_log("Update executed with reference number: " . $referenceNumber);

            // Verify the update was successful
            if ($updateStmt->rowCount() === 0) {
                error_log("Update failed - no rows affected");
                throw new Exception("Failed to update payment reference and submission attempts");
            }
            
            // Double check the update
            $verifyStmt = $pdo->prepare("
                SELECT Payment_Reference_Number, Submission_Attempts 
                FROM tbl_order_request 
                WHERE Request_ID = :request_id
            ");
            $verifyStmt->execute(['request_id' => $requestId]);
            $updatedData = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            error_log("Verification data after update: " . print_r($updatedData, true));
            
            // Verify the reference number was actually updated
            if (empty($updatedData['Payment_Reference_Number'])) {
                error_log("Reference number update verification failed");
                throw new Exception("Payment reference number was not updated successfully");
            }
            
            // Commit the transaction
            $pdo->commit();
            error_log("Transaction committed successfully");
            
            // Redirect back to profile with success message
            header("Location: profile.php?success=reference_updated&request_id=" . $requestId);
            exit;
        } else {
            // Order not found or doesn't belong to user
            error_log("Order not found or invalid user. Rolling back transaction.");
            $pdo->rollBack();
            header("Location: profile.php?error=invalid_request&request_id=" . $requestId);
            exit;
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            error_log("Error occurred, rolling back transaction");
            $pdo->rollBack();
        }
        // Log the error with stack trace
        error_log("Error in update_payment_reference.php: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        // Handle any errors
        header("Location: profile.php?error=" . urlencode($e->getMessage()) . "&request_id=" . $requestId);
        exit;
    }
} else {
    // Invalid request
    error_log("Invalid request method or missing parameters");
    header("Location: profile.php?error=invalid_request");
    exit;
}
?> 