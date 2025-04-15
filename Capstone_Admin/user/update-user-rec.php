<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Redirect to login page if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
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

// Get the form data if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure required fields are set and escape them
    $userID = htmlspecialchars($_POST['txtID']);
    $lname = htmlspecialchars($_POST['txtLName']);
    $fname = htmlspecialchars($_POST['txtFName']);
    $mname = htmlspecialchars($_POST['txtMName']);
    $homeAddress = htmlspecialchars($_POST['txtHomeAddress']);
    $email = htmlspecialchars($_POST['txtEmail']);
    $mobile = htmlspecialchars($_POST['txtMobile']);
    $status = htmlspecialchars($_POST['txtStatus']);
    $password = $_POST['txtPass']; // New password field
    $confirmPassword = $_POST['txtConfirm'];
    $idVerificationStatus = isset($_POST['idVerificationStatus']) ? $_POST['idVerificationStatus'] : 'Unverified'; // Get the ID verification status from the form

    // If password is entered, check for confirmation and hash it
    if (!empty($password)) {
        if ($password !== $confirmPassword) {
            echo "Password and confirm password do not match.";
            exit();
        }
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    } else {
        $hashedPassword = null; // Keep the current password if no new one is provided
    }

    // Handle profile picture upload
    $picPath = null;
    if (!empty($_FILES['PicPath']['name'])) {
        $uploadDir = '../uploads/user/';
        $picFileName = basename($_FILES['PicPath']['name']);
        $picFilePath = $uploadDir . $picFileName;
        if (move_uploaded_file($_FILES['PicPath']['tmp_name'], $picFilePath)) {
            $picPath = $picFilePath;
        }
    }

    // Create the query to update user data
    $query = "UPDATE tbl_user_info SET 
                Last_Name = :lname, 
                First_Name = :fname, 
                Middle_Name = :mname, 
                Home_Address = :homeAddress, 
                Email_Address = :email, 
                Mobile_Number = :mobile, 
                Status = :status, 
                ID_Verification_Status = :idVerificationStatus" . 
                ($hashedPassword ? ", Password = :password" : "") . 
                ($picPath ? ", PicPath = :picPath" : "") . 
                " WHERE User_ID = :userID";
    
    // Prepare and bind parameters
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':lname', $lname);
    $stmt->bindParam(':fname', $fname);
    $stmt->bindParam(':mname', $mname);
    $stmt->bindParam(':homeAddress', $homeAddress);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':mobile', $mobile);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':idVerificationStatus', $idVerificationStatus); // Bind the ID verification status
    if ($hashedPassword) {
        $stmt->bindParam(':password', $hashedPassword);
    }
    if ($picPath) {
        $stmt->bindParam(':picPath', $picPath);
    }
    $stmt->bindParam(':userID', $userID);

    // Execute the query and check for success
    if ($stmt->execute()) {
        header("Location: ../user/read-all-user-form.php");
        exit();
    } else {
        echo "Error updating record: " . implode(":", $stmt->errorInfo());
    }
} else {
    // If the form is not submitted, fetch the existing user data for editing
    if (isset($_GET['id'])) {
        $userId = $_GET['id'];
        
        // Create the query to select user record
        $query = "SELECT * FROM tbl_user_info WHERE User_ID = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        // Fetch user record
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            echo "User not found.";
            exit();
        }

        // Store user data in variables
        $userID = htmlspecialchars($user["User_ID"]);
        $lname = htmlspecialchars($user["Last_Name"]);
        $fname = htmlspecialchars($user["First_Name"]);
        $mname = htmlspecialchars($user["Middle_Name"]);
        $homeAddress = htmlspecialchars($user["Home_Address"]);
        $email = htmlspecialchars($user["Email_Address"]);
        $mobile = htmlspecialchars($user["Mobile_Number"]);
        $status = htmlspecialchars($user["Status"]);
        $validIDPath = htmlspecialchars($user["Valid_ID_Path"]);
    } else {
        echo "No user ID provided.";
        exit();
    }
}
?>
