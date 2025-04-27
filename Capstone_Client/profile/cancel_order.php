<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include database connection
include("../config/database.php");

// Check if request_id is provided
if (!isset($_POST['request_id']) || empty($_POST['request_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("location: profile.php");
    exit;
}

$request_id = $_POST['request_id'];
$user_id = $_SESSION["user_id"];

try {
    // First verify that the order belongs to the current user
    $stmt = $pdo->prepare("SELECT * FROM tbl_order_request WHERE Request_ID = ? AND User_ID = ?");
    $stmt->execute([$request_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['error'] = "Order not found or you don't have permission to cancel this order.";
        header("location: profile.php");
        exit;
    }

    // Delete the order request
    $stmt = $pdo->prepare("DELETE FROM tbl_order_request WHERE Request_ID = ? AND User_ID = ?");
    $stmt->execute([$request_id, $user_id]);

    $_SESSION['success'] = "Order has been cancelled successfully.";
    header("location: profile.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = "An error occurred while cancelling the order. Please try again.";
    header("location: profile.php");
    exit;
}
?> 