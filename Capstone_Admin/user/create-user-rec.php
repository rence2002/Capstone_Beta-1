<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
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

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --- Validation Section (Server-Side) ---
    $errors = []; // Array to store any validation errors

    // 1. reCAPTCHA Validation
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $secretKey = '6LdGk9wqAAAAAJB1oI6jUNdeLa2IM83P0-02sTBj'; // Replace with your secret key
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
    $responseKeys = json_decode($response, true);

    if (intval($responseKeys["success"]) !== 1) {
        $errors[] = "Please complete the reCAPTCHA.";
    }

    // Get user input from web form and sanitize
    $userID = htmlspecialchars($_POST['txtUserID']);
    $lname = htmlspecialchars($_POST['txtLName']);
    $fname = htmlspecialchars($_POST['txtFName']);
    $mname = htmlspecialchars($_POST['txtMName']);
    $address = htmlspecialchars($_POST['txtAddress']);
    $email = htmlspecialchars($_POST['txtEmail']);
    $mobile = htmlspecialchars($_POST['txtMobile']);
    $password = $_POST['txtPass'];

    // 2. Email Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // 3. Mobile Number Validation (11 digits)
    if (!preg_match('/^\d{11}$/', $mobile)) {
        $errors[] = "Mobile number must be 11 digits.";
    }

    //4. Home Address validation (not empty)
    if(empty(trim($address))){
      $errors[] = "Home Address must not be empty";
    }
    //5. check if password and confirm password are the same
    $confirmPass = $_POST['txtConfirm'];
    if($password !== $confirmPass){
        $errors[] = "Password and confirm Password does not match";
    }
    // If there are errors, display them and stop processing
    if (!empty($errors)) {
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        exit(); // Stop processing the form
    }

    // --- End Validation Section ---

    // Hash the password
    $pass = password_hash($password, PASSWORD_DEFAULT);

    // Handle file upload for PicPath
    $picPath = "";
    if (isset($_FILES['filePic']) && $_FILES['filePic']['error'] == 0) {
        $targetDir = "../uploads/user/";
        $fileName = basename($_FILES["filePic"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        // Ensure file with the same name doesn't already exist
        if (!file_exists($targetFilePath)) {
            if (move_uploaded_file($_FILES["filePic"]["tmp_name"], $targetFilePath)) {
                $picPath = $targetFilePath;
            } else {
                echo "Error uploading the file.";
                exit();
            }
        } else {
            echo "File already exists.";
            exit();
        }
    }

    // Set default status to Active
    $status = "Active";

    // Create query to insert records into tbl_user_info
    $query = "INSERT INTO tbl_user_info (User_ID, Last_Name, First_Name, Middle_Name, Home_Address, Email_Address, Mobile_Number, Password, PicPath, Status) VALUES
        (:userID, :lname, :fname, :mname, :address, :email, :mobile, :pass, :picPath, :status)";

    // Prepare query and store to a statement variable
    $stmt = $pdo->prepare($query);

    // Bind parameter values
    $stmt->bindParam(":userID", $userID);
    $stmt->bindParam(":lname", $lname);
    $stmt->bindParam(":fname", $fname);
    $stmt->bindParam(":mname", $mname);
    $stmt->bindParam(":address", $address);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":mobile", $mobile);
    $stmt->bindParam(":pass", $pass);
    $stmt->bindParam(":picPath", $picPath);
    $stmt->bindParam(":status", $status);

    // Execute statement
    if ($stmt->execute()) {
        // Redirect to user list after successful insertion
        header("location: ../user/read-all-user-form.php");
        exit();
    } else {
        echo "Error creating user.";
        echo "Error details: " . implode(":", $stmt->errorInfo()); // show error details
    }
}
?>
