<?php
session_start();
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestID = $_POST['request_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM tbl_order_request WHERE Request_ID = ?");
        $stmt->execute([$requestID]);

        // Redirect to the profile page after successful deletion
        header("Location: profile.php");
        exit();
    } catch (Exception $e) {
        // Redirect to the profile page with an error message
        header("Location: profile.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>