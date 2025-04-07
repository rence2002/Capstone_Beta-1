<?php
session_start();
include("../config/database.php");

// Check if the user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Check if the request ID is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $requestId = $_POST['request_id'];

    try {
        // Delete the order request from tbl_order_request
        $stmt = $pdo->prepare("DELETE FROM tbl_order_request WHERE Request_ID = :requestId");
        $stmt->bindParam(':requestId', $requestId, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect back to the profile page
        header("Location: profile.php");
        exit;
    } catch (PDOException $e) {
        die("Error deleting order request: " . $e->getMessage());
    }
} else {
    // Redirect back to the profile page if no request ID is provided
    header("Location: profile.php");
    exit;
}
?>