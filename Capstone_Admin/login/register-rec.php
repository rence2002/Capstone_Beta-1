<?php
// INCLUDE DATABASE CONNECTION
include("../config/database.php"); // Corrected path
session_start();

$admin_id = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);

// INITIALIZE VARIABLES AND HANDLE USER INPUT
$lname = $_POST['txtLName'] ?? '';
$fname = $_POST['txtFName'] ?? '';
$mname = $_POST['txtMName'] ?? '';
$address = $_POST['txtAddress'] ?? '';
$email = $_POST['txtEmail'] ?? '';
$mobile = $_POST['full_phone_number'] ?? ''; // Use the full phone number
$pass = $_POST['txtPass'] ?? '';
$confirmPass = $_POST['txtConfirm'] ?? '';

// SET STATUS AUTOMATICALLY
$status = 'Active'; // Set the status to 'Active' or any other default value

// VALIDATE INPUT
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: Invalid email address.");
}

if (!preg_match('/^[a-zA-Z0-9\s,.\'-]{3,}$/', $address)) {
    die("Error: Invalid home address.");
}
//VALIDATE PHONE NUMBER
if (strlen($mobile) !== 13 && strlen($mobile) !== 11) {
    die("Error: Invalid mobile number. Please enter a 11 or 13 digit mobile number.");
}

// REMOVE ALL NON DIGIT EXCEPT + FOR INTERNATIONAL NUMBERS
$mobile = preg_replace("/[^0-9+]/", "", $mobile);

//ADD +63 TO THE PHONE NUMBER IF THERE IS NO +
if (strlen($mobile) === 11 && $mobile[0] === '0') {
        $mobile = '+63' . substr($mobile, 1);
    }

require '../vendor/autoload.php'; // Corrected path
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// VALIDATE PASSWORD MATCH
if ($pass !== $confirmPass) {
    die("Error: Password and Confirm Password do not match.");
}

// HANDLE FILE UPLOAD
$picPath = '';
if (isset($_FILES['filePic']) && $_FILES['filePic']['error'] == 0) {
    $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/Capstone_Beta/Capstone_Beta/uploads/admin/"; // Adjusted to be relative to the web root
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $fileName = time() . "_" . basename($_FILES["filePic"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    // Validate file type and size
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileType, $allowedTypes)) {
        die("Error: Only JPG, JPEG, PNG, and GIF files are allowed.");
    }
    if ($_FILES["filePic"]["size"] > 5000000) { // 5MB limit
        die("Error: File size exceeds the 5MB limit.");
    }

    if (move_uploaded_file($_FILES["filePic"]["tmp_name"], $targetFilePath)) {
        $picPath = "../uploads/admin/" . $fileName; // Save the relative path
    } else {
        die("Error: Failed to upload file.");
    }
}

// HASH PASSWORD FOR SECURITY
$hashedPass = password_hash($pass, PASSWORD_DEFAULT);

// GENERATE 4-DIGIT VERIFICATION CODE
$verificationCode = rand(1000, 9999);

// STORE USER DETAILS AND VERIFICATION CODE IN SESSION
$_SESSION['registration'] = [
    'admin_id' => $admin_id,
    'lname' => $lname,
    'fname' => $fname,
    'mname' => $mname,
    'address' => $address,
    'email' => $email,
    'mobile' => $mobile,
    'status' => $status,
    'hashedPass' => $hashedPass,
    'picPath' => $picPath,
    'verificationCode' => $verificationCode
];

// SEND VERIFICATION CODE TO USER EMAIL USING PHPMailer
$mail = new PHPMailer(true);
try {
    //Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
    $mail->SMTPAuth = true;
    $mail->Username = 'rence.b.m@gmail.com'; // SMTP username
    $mail->Password = 'vlnl qsfo iwjo zlgl '; // SMTP password (use App Password if 2FA is enabled)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    //Recipients
    $mail->setFrom('your-email@gmail.com', 'Your App Name');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Verification Code';
    $mail->Body    = "Your verification code is: $verificationCode";

    $mail->send();
    // REDIRECT TO VERIFICATION PAGE
    header("Location: verify.php");
    exit();
} catch (Exception $e) {
    die("Error: Failed to send verification email. Mailer Error: {$mail->ErrorInfo}");
}
?>
