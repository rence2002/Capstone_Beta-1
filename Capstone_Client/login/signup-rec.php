<?php
// INCLUDE DATABASE CONNECTION
include("../config/database.php");
session_start();

// Send verification email using PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// INITIALIZE VARIABLES AND HANDLE USER INPUT
$userId = $_POST['userId'] ?? "user_" . uniqid(); // Generate a unique user ID
$lname = $_POST['lastName'] ?? '';
$fname = $_POST['firstName'] ?? '';
$middleName = $_POST['middleName'] ?? '';
$homeAddress = $_POST['homeAddress'] ?? '';
$email = $_POST['email'] ?? '';
$mobileNumber = $_POST['mobileNumber'] ?? '';
$pass = $_POST['password'] ?? '';
$confirmPass = $_POST['confirm-password'] ?? '';
$status = 'Inactive'; // Set the status to 'Inactive' until email verification
$errors = [];

// VALIDATE INPUT
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Error: Invalid email address format.";
}

if (!empty($mobileNumber)) {
    $mobileNumber = preg_replace('/[^0-9]/', '', $mobileNumber); // Remove non-numeric characters
    if (strlen($mobileNumber) !== 11) {
        $errors[] = "Error: Mobile number must be 11 digits.";
    }
}

if ($pass !== $confirmPass) {
    $errors[] = "Error: Password and Confirm Password do not match.";
}

if (strlen($pass) < 8) {
    $errors[] = "Error: Password must be at least 8 characters long.";
}

if (empty($fname)) {
    $errors[] = "Error: First name is required.";
}

if (empty($lname)) {
    $errors[] = "Error: Last name is required.";
}

// Handle profile picture upload
$profilePicPath = null;
if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/user/'; // Directory to store uploaded files
    $profilePicName = basename($_FILES['profilePic']['name']); // Get the file name
    $profilePicPath = $uploadDir . $userId . '_' . $profilePicName;
    $tempName = $_FILES['profilePic']['tmp_name']; // Temporary path

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
    }

    if (!move_uploaded_file($tempName, $profilePicPath)) {
        $errors[] = "Error: Failed to upload profile picture.";
    }
    $profilePicPath = str_replace('../', '', $profilePicPath);
}

// Handle valid ID upload
$validIDPath = null;
if (isset($_FILES['validID']) && $_FILES['validID']['error'] === UPLOAD_ERR_OK) {
    $validIDDir = '../uploads/user/validid/'; // Directory to store valid ID files
    $validIDName = basename($_FILES['validID']['name']); // Get the file name
    $validIDPath = $validIDDir . $userId . '_validid_' . $validIDName;
    $tempName = $_FILES['validID']['tmp_name']; // Temporary path

    if (!is_dir($validIDDir)) {
        mkdir($validIDDir, 0777, true); // Create the directory if it doesn't exist
    }

    if (!move_uploaded_file($tempName, $validIDPath)) {
        $errors[] = "Error: Failed to upload valid ID.";
    }
    $validIDPath = str_replace('../', '', $validIDPath);
}

// Handle errors
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo $error . "<br>";
    }
    exit(); // Stop further execution if there are errors
}

// Hash password for security
$hashedPass = password_hash($pass, PASSWORD_DEFAULT);

// Generate a verification code
$verificationCode = random_int(100000, 999999); // 6-digit verification code

// Insert user data into the database
try {
    $stmt = $pdo->prepare("INSERT INTO tbl_user_info 
        (User_ID, Last_Name, First_Name, Middle_Name, Home_Address, Email_Address, Mobile_Number, Status, Password, PicPath, Valid_ID_Path, reset_code) 
        VALUES 
        (:userId, :lastName, :firstName, :middleName, :homeAddress, :email, :mobileNumber, :status, :password, :profilePicPath, :validIDPath, :verificationCode)");

    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':lastName', $lname);
    $stmt->bindParam(':firstName', $fname);
    $stmt->bindParam(':middleName', $middleName);
    $stmt->bindParam(':homeAddress', $homeAddress);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':mobileNumber', $mobileNumber);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':password', $hashedPass);
    $stmt->bindParam(':profilePicPath', $profilePicPath);
    $stmt->bindParam(':validIDPath', $validIDPath);
    $stmt->bindParam(':verificationCode', $verificationCode);

    if ($stmt->execute()) {
        

        require '../vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'rence.b.m@gmail.com'; // SMTP username
            $mail->Password = 'vlnl qsfo iwjo zlgl'; // SMTP password (use App Password if 2FA is enabled)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('rence.b.m@gmail.com', 'RM Betis Furniture');
            $mail->addAddress($email); // Add recipient

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->Body = "Dear $fname,<br><br>Thank you for signing up. Please use the following verification code to verify your email:<br><br>";
            $mail->Body .= "<strong>Verification Code: $verificationCode</strong><br><br>";
            $mail->Body .= "If you did not sign up, please ignore this email.<br><br>Best regards,<br>RM Betis Furniture";

            // Send email
            $mail->send();

            // Redirect to verify.php
            header("Location: verify.php?email=" . urlencode($email));
            exit();
        } catch (Exception $e) {
            die("Error: Failed to send verification email. Mailer Error: {$mail->ErrorInfo}");
        }
    } else {
        echo "Error: Failed to create account.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
