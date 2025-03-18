<?php
// INCLUDE DATABASE CONNECTION
include("../config/database.php");
session_start();

// INITIALIZE VARIABLES AND HANDLE USER INPUT
$userId = $_POST['userId'] ?? "user_" . uniqid(); // Make user ID optional
$lname = $_POST['lastName'] ?? '';
$fname = $_POST['firstName'] ?? '';
$middleName = $_POST['middleName'] ?? '';
$homeAddress = $_POST['homeAddress'] ?? '';
$email = $_POST['email'] ?? '';
$mobileNumber = $_POST['mobileNumber'] ?? '';
$pass = $_POST['password'] ?? '';
$confirmPass = $_POST['confirm-password'] ?? '';
$terms = $_POST['terms'] ?? null; // Get terms and conditions.

// SET STATUS AUTOMATICALLY
$status = 'Active'; // Set the status to 'Active' or any other default value

$errors = [];

// VALIDATE INPUT
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Error: Invalid email address format.";
}

// Validate home address
if (!empty($homeAddress) && !preg_match('/^[a-zA-Z0-9\s\-\.,#]+$/', $homeAddress)) {
    $errors[] = "Error: Invalid home address format.";
}

//Validate mobile number
if (!empty($mobileNumber)) {
    $mobileNumber = preg_replace('/[^0-9]/', '', $mobileNumber); // Remove non-numeric characters
    if (strlen($mobileNumber) !== 11) {
        $errors[] = "Error: Mobile number must be 11 digits.";
    }
}

//Check if terms and condition is checked.
if (!isset($terms)) {
    $errors[] = "You must agree to the terms and conditions";
}
// VALIDATE PASSWORD MATCH
if ($pass !== $confirmPass) {
    $errors[] = "Error: Password and Confirm Password do not match.";
}
// VALIDATE PASSWORD COMPLEXITY
if (strlen($pass) < 8) {
    $errors[] = "Error: Password must be at least 8 characters long.";
}

if (empty($fname)) {
    $errors[] = "Error: First name is required.";
}

if (empty($lname)) {
    $errors[] = "Error: Last name is required.";
}

// Check if User ID is unique
try {
    $stmt = $pdo->prepare("SELECT User_ID FROM tbl_user_info WHERE User_ID = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->fetch()) {
        $errors[] = "Error: User ID is already taken.";
    }
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
}

// Handle file upload
$profilePicPath = null;
if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/user/'; // Directory to store uploaded files
    $profilePicName = basename($_FILES['profilePic']['name']); // Get the file name
    $profilePicPath = $uploadDir . $userId . '_' . $profilePicName;
    $tempName = $_FILES['profilePic']['tmp_name']; //temporary path

    // Check if the upload directory exists and is writable
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        $errors[] = "Error: Upload directory is not accessible or not writable.";
    }

    // Move the file to the uploads directory
    if (!move_uploaded_file($tempName, $profilePicPath)) {
        $errors[] = "Error: Failed to upload file.";
    }
    $profilePicPath = str_replace('../', '', $profilePicPath);
}

// Handle errors
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo $error . "<br>";
    }
    exit(); // Stop further execution if there are errors
}

// Handle file if there are no errors.
// HASH PASSWORD FOR SECURITY
$hashedPass = password_hash($pass, PASSWORD_DEFAULT);

// GENERATE 4-DIGIT VERIFICATION CODE
$verificationCode = rand(1000, 9999);

// STORE USER DETAILS AND VERIFICATION CODE IN SESSION
$_SESSION['registration'] = [
    'userId' => $userId,
    'lastName' => $lname,
    'firstName' => $fname,
    'middleName' => $middleName,
    'homeAddress' => $homeAddress,
    'email' => $email,
    'mobileNumber' => $mobileNumber,
    'status' => $status,
    'hashedPass' => $hashedPass,
    'verificationCode' => $verificationCode,
    'profilePicPath' => $profilePicPath,
];

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// SEND VERIFICATION CODE TO USER EMAIL USING PHPMailer
$mail = new PHPMailer(true);
try {
    //Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
    $mail->SMTPAuth = true;
    $mail->Username = 'rence.b.m@gmail.com'; // SMTP username
    $mail->Password = 'vlnl qsfo iwjo zlgl'; // SMTP password (use App Password if 2FA is enabled)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    //Recipients
    $mail->setFrom('your-email@gmail.com', 'Your App Name');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Verification Code';
    $mail->Body = "Your verification code is: <b>$verificationCode</b>"; //make it bold

    $mail->send();
    // REDIRECT TO VERIFICATION PAGE
    header("Location: verify.php");
    exit();
} catch (Exception $e) {
    echo "Error: Failed to send verification email. Mailer Error: {$mail->ErrorInfo}";
}
?>
