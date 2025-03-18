<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /Capstone/login.php");
    exit();
}

// Fetch admin data from the database
$adminId = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT First_Name, PicPath FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmt->bindParam(':admin_id', $adminId);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if admin data is available
if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = $admin['PicPath'] ? htmlspecialchars($admin['PicPath']) : '/Capstone/static/images/default-profile.png'; // Default image

// Check if 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $customizationID = (int) $_GET['id'];

    // Delete associated progress record
    $deleteProgressQuery = "DELETE FROM tbl_progress WHERE Product_ID = (SELECT Product_ID FROM tbl_customizations WHERE Customization_ID = ?)";
    $progressStmt = $pdo->prepare($deleteProgressQuery);
    $progressStmt->bindValue(1, $customizationID, PDO::PARAM_INT);
    $progressStmt->execute();

    // Prepare the delete statement for customization
    $query = "DELETE FROM tbl_customizations WHERE Customization_ID = ?";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(1, $customizationID, PDO::PARAM_INT);
    
    // Execute the statement with confirmation
    if ($stmt->execute()) {
        header("Location: read-all-custom-form.php?message=Customization record deleted successfully");
        exit();
    } else {
        echo "Error deleting customization record.";
    }
} else {
    echo "No customization ID specified.";
}
?>
