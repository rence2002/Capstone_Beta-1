<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: /Capstone/login.php");
    exit();
}

// Fetch admin data from the database
$adminId = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT First_Name, PicPath FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmt->bindParam(':admin_id', $adminId);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if admin data is fetched
if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get admin ID from the form
    $adminID = $_POST['admin_id'];

    if ($adminID != $adminId){
        echo "You are not allowed to delete this Admin.";
        exit();
    }
    // Delete the admin record from the database
    $query = "DELETE FROM tbl_admin_info WHERE Admin_ID = :admin_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':admin_id', $adminID);

    if ($stmt->execute()) {
        // Redirect to the admin list page
        header("Location: ../admin/logout.php");
        exit();
    } else {
        echo "Error deleting admin record: " . implode(", ", $stmt->errorInfo());
    }
}
?>
