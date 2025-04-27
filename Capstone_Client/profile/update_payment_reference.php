<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include database connection
include("../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["request_id"]) && isset($_POST["reference_number"])) {
    $requestId = $_POST["request_id"];
    $referenceNumber = $_POST["reference_number"];
    $userId = $_SESSION["user_id"];

    try {
        // First check the current submission attempts
        $checkStmt = $pdo->prepare("
            SELECT Payment_Reference_Number, Submission_Attempts 
            FROM tbl_order_request 
            WHERE Request_ID = :request_id AND User_ID = :user_id AND Payment_Status = 'Pending'
        ");
        $checkStmt->execute([
            'request_id' => $requestId,
            'user_id' => $userId
        ]);
        $orderData = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($orderData) {
            // Check if this is a resubmission
            if ($orderData['Payment_Reference_Number'] !== null) {
                // Check if max attempts reached
                if ($orderData['Submission_Attempts'] >= 3) {
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
            } else {
                // First submission
                $updateStmt = $pdo->prepare("
                    UPDATE tbl_order_request 
                    SET Payment_Reference_Number = :reference_number,
                        Submission_Attempts = 1
                    WHERE Request_ID = :request_id
                ");
            }
            
            $updateStmt->execute([
                'reference_number' => $referenceNumber,
                'request_id' => $requestId
            ]);
            
            // Redirect back to profile with success message
            header("Location: profile.php?success=reference_updated&request_id=" . $requestId);
            exit;
        } else {
            // Order not found or doesn't belong to user
            header("Location: profile.php?error=invalid_request&request_id=" . $requestId);
            exit;
        }
    } catch (Exception $e) {
        // Handle any errors
        header("Location: profile.php?error=" . urlencode($e->getMessage()) . "&request_id=" . $requestId);
        exit;
    }
} else {
    // Invalid request
    header("Location: profile.php?error=invalid_request");
    exit;
}
?> 