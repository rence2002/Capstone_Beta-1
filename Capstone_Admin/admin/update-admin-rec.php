<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin's ID is stored in session after login
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
    // Get user input from the form
    $adminID = $_POST['txtID'];
    if ($adminID != $adminId) {
        echo "You are not allowed to update this Admin.";
        exit();
    }

    // Sanitize and set default values for text inputs
    $lname = isset($_POST['txtLName']) ? htmlspecialchars(trim($_POST['txtLName'])) : '';
    $fname = isset($_POST['txtFName']) ? htmlspecialchars(trim($_POST['txtFName'])) : '';
    $mname = isset($_POST['txtMName']) ? htmlspecialchars(trim($_POST['txtMName'])) : '';
    $email = isset($_POST['txtEmail']) ? htmlspecialchars(trim($_POST['txtEmail'])) : '';
    $mobile = isset($_POST['txtMobile']) ? htmlspecialchars(trim($_POST['txtMobile'])) : '';
    $status = isset($_POST['txtStatus']) ? htmlspecialchars(trim($_POST['txtStatus'])) : '';

    //Handle password
    $pass = isset($_POST['txtPass']) ? trim($_POST['txtPass']) : '';
    $confirmPass = isset($_POST['txtConfirm']) ? trim($_POST['txtConfirm']) : '';

    // Handle password logic
    if (!empty($pass) || !empty($confirmPass)) {
        if ($pass !== $confirmPass) {
            echo "Passwords do not match.";
            exit();
        }
        // Hash the new password
        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
    } else {
        // Fetch the current password from the database
        $stmt = $pdo->prepare("SELECT Password FROM tbl_admin_info WHERE Admin_ID = :admin_id");
        $stmt->bindParam(':admin_id', $adminID);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        $hashedPass = $admin['Password'];
    }

    // Handle file upload for profile picture
    $picPath = $profilePicPath;
    if (isset($_FILES['filePic']) && $_FILES['filePic']['error'] == 0) {
        $targetDir = "../uploads/admin/";
        $fileName = basename($_FILES["filePic"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["filePic"]["tmp_name"], $targetFilePath)) {
            $picPath = $targetFilePath;
        }
    }

    // Update the admin record in the database
    $query = "UPDATE tbl_admin_info SET 
        Last_Name = :lname, 
        First_Name = :fname, 
        Middle_Name = :mname, 
        Email_Address = :email, 
        Mobile_Number = :mobile, 
        Status = :status, 
        Password = :pass, 
        PicPath = :picPath 
        WHERE Admin_ID = :admin_id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':lname', $lname);
    $stmt->bindParam(':fname', $fname);
    $stmt->bindParam(':mname', $mname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':mobile', $mobile);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':pass', $hashedPass);
    $stmt->bindParam(':picPath', $picPath);
    $stmt->bindParam(':admin_id', $adminID);

    if ($stmt->execute()) {
        // Redirect to the dashboard page
        header("Location: ../dashboard/dashboard.php");
        exit();
    } else {
        echo "Error updating admin record: " . implode(", ", $stmt->errorInfo());
    }
}
?>
