<?php
session_start();

// Include the database connection
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if request ID is provided
if (!isset($_GET['id'])) {
    header("Location: read-all-request-form.php");
    exit();
}

$requestId = $_GET['id'];

try {
    // Reset the submission attempts for the specified order
    $stmt = $pdo->prepare("UPDATE tbl_order_request SET Submission_Attempts = 0 WHERE Request_ID = :request_id");
    $stmt->bindParam(':request_id', $requestId);
    $stmt->execute();

    // Redirect back to the order requests page with success message
    header("Location: read-all-request-form.php?success=attempts_reset");
    exit();
} catch (PDOException $e) {
    // Redirect back with error message if something goes wrong
    header("Location: read-all-request-form.php?error=" . urlencode("Failed to reset attempts: " . $e->getMessage()));
    exit();
}
?> 