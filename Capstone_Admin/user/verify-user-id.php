<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if User_ID is provided via GET or POST
if (isset($_GET['user_id']) || isset($_POST['user_id'])) {
    $userID = isset($_GET['user_id']) ? $_GET['user_id'] : $_POST['user_id'];

    // Query to check if the User_ID exists
    $query = "SELECT * FROM tbl_user_info WHERE User_ID = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userID);
    $stmt->execute();

    // Fetch the user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "<h3>User ID Verified</h3>";
        echo "<p>User ID: " . htmlspecialchars($user['User_ID']) . "</p>";
        echo "<p>Name: " . htmlspecialchars($user['First_Name']) . " " . htmlspecialchars($user['Last_Name']) . "</p>";
        echo "<p>Email: " . htmlspecialchars($user['Email_Address']) . "</p>";
    } else {
        echo "<h3>User ID Not Found</h3>";
    }
} else {
    echo "<h3>No User ID Provided</h3>";
}
?>