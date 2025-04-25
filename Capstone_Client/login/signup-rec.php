<?php
// c:\xampp\htdocs\Capstone_Beta\Capstone_Client\login\signup-rec.php

// INCLUDE DATABASE CONNECTION & START SESSION
include("../config/database.php"); // Ensure this path is correct
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
}

// USE PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- INITIALIZE VARIABLES & HANDLE INPUT ---
// ... (Keep the existing input handling and validation code here) ...
$userId = $_POST['userId'] ?? "user_" . uniqid();
$lname = trim($_POST['lastName'] ?? '');
$fname = trim($_POST['firstName'] ?? '');
$middleName = trim($_POST['middleName'] ?? '');
$homeAddress = trim($_POST['homeAddress'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobileNumber = $_POST['mobileNumber'] ?? '';
$pass = $_POST['password'] ?? '';
$confirmPass = $_POST['confirm-password'] ?? '';
$status = 'Inactive';
$errors = [];

// --- VALIDATION ---
// ... (Keep the existing validation logic here) ...
// Validate Email (Required, Format)
if (empty($email)) {
    $errors['email'] = "Error: Email address is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Error: Invalid email address format.";
}
// Note: The AJAX check in signup.php handles the "already registered" check before submission,
// but the database constraint and the catch block below provide final protection.

// Validate Mobile Number (Optional, Format if provided)
if (!empty($mobileNumber)) {
    $mobileNumber = preg_replace('/[^0-9]/', '', $mobileNumber);
    if (strlen($mobileNumber) !== 11) {
        $errors['mobileNumber'] = "Error: Mobile number must be 11 digits.";
    }
}

// Validate Passwords (Required, Length, Match)
if (empty($pass)) {
    $errors['password'] = "Error: Password is required.";
} elseif (strlen($pass) < 8) { // Corrected: Replaced 😎 with 8
    $errors['password'] = "Error: Password must be at least 8 characters long.";

} elseif ($pass !== $confirmPass) {
    $errors['confirm-password'] = "Error: Password and Confirm Password do not match.";
}

// Validate Required Text Fields
if (empty($fname)) {
    $errors['firstName'] = "Error: First name is required.";
}
if (empty($lname)) {
    $errors['lastName'] = "Error: Last name is required.";
}
// Add check for homeAddress if it's required
// if (empty($homeAddress)) {
//     $errors['homeAddress'] = "Error: Home address is required.";
// }


// --- FILE UPLOADS ---
// ... (Keep the existing handleUpload function and calls here) ...
$profilePicPath = null;
$validIDPath = null;

// Function to handle file upload (Improved)
function handleUpload($fileKey, $userId, $type, &$errors) {
    // Check if file exists and was uploaded via HTTP POST
    if (!isset($_FILES[$fileKey]) || !is_uploaded_file($_FILES[$fileKey]['tmp_name'])) {
        // Handle case where file is required but missing
        if ($type === 'validid') { // Assuming 'validid' is required
             $errors[$fileKey] = "Error: Valid ID is required.";
        }
        // No error if optional file is missing (like profile pic)
        return null;
    }

    $file = $_FILES[$fileKey];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'File too large (server limit).',
            UPLOAD_ERR_FORM_SIZE  => 'File too large (form limit).',
            UPLOAD_ERR_PARTIAL    => 'File only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.', // Should be caught above if required
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the upload.',
        ];
        $errorCode = $file['error'];
        $errors[$fileKey] = "Error uploading {$type}: " . ($uploadErrors[$errorCode] ?? 'Unknown upload error.');
        error_log("Upload error for {$fileKey}: code {$errorCode}");
        return null;
    }

    // File properties and validation
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5 MB

    // Validate MIME type (more reliable than extension)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        $errors[$fileKey] = "Error: Invalid file type for {$type}. Only JPG, PNG, GIF allowed.";
        return null;
    }

    // Validate size
    if ($file['size'] > $maxFileSize) {
        $errors[$fileKey] = "Error: File size for {$type} exceeds the 5MB limit.";
        return null;
    }

    // Define paths
    $baseDir = '../uploads/user/'; // Relative to this script's location
    $subDir = ($type === 'validid') ? 'validid/' : '';
    $uploadDir = $baseDir . $subDir;

    // Ensure directory exists and is writable
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) { // Use appropriate permissions
            $errors[$fileKey] = "Error: Could not create upload directory for {$type}.";
            error_log("Failed to create directory: " . $uploadDir);
            return null;
        }
    }
    if (!is_writable($uploadDir)) {
         $errors[$fileKey] = "Error: Upload directory is not writable.";
         error_log("Upload directory not writable: " . $uploadDir);
         return null;
    }


    // Generate a unique and safe filename
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    // Ensure extension is one of the allowed ones (defense in depth)
    if (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
        $errors[$fileKey] = "Error: Invalid file extension for {$type}.";
        return null;
    }
    $safeFilename = $userId . '_' . $type . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension; // Added underscores for readability
    $destinationPath = $uploadDir . $safeFilename;
    // Store the path relative to the web root or a known base for consistency
    $relativePath = 'uploads/user/' . $subDir . $safeFilename;

    // Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
        return $relativePath; // Return relative path on success
    } else {
        $errors[$fileKey] = "Error: Failed to move uploaded {$type}. Check permissions.";
        error_log("Failed to move uploaded file '{$file['tmp_name']}' to '{$destinationPath}'");
        return null;
    }
}

// Handle Profile Picture (Optional)
$profilePicPath = handleUpload('profilePic', $userId, 'profile', $errors);

// Handle Valid ID (Required)
$validIDPath = handleUpload('validID', $userId, 'validid', $errors);


// --- HANDLE VALIDATION ERRORS ---
if (!empty($errors)) {
    $_SESSION['signup_errors'] = $errors;
    // Store submitted form data (excluding passwords and files)
    $formData = $_POST;
    unset($formData['password'], $formData['confirm-password']);
    $_SESSION['signup_form_data'] = $formData;
    header('Location: signup.php'); // Redirect back to the form page
    exit();
}

// --- PROCESS DATA (If no validation errors) ---

// Hash password
$hashedPass = password_hash($pass, PASSWORD_DEFAULT);

// Generate verification code (Using random_int is better)
try {
    $verificationCode = random_int(100000, 999999);
} catch (Exception $e) {
    $verificationCode = mt_rand(100000, 999999); // Fallback
    error_log("random_int failed, used mt_rand for verification code: " . $e->getMessage());
}

// --- DATABASE INSERTION ---
try {
    $stmt = $pdo->prepare("INSERT INTO tbl_user_info
        (User_ID, Last_Name, First_Name, Middle_Name, Home_Address, Email_Address, Mobile_Number, Status, Password, PicPath, Valid_ID_Path, reset_code)
        VALUES
        (:userId, :lastName, :firstName, :middleName, :homeAddress, :email, :mobileNumber, :status, :password, :profilePicPath, :validIDPath, :verificationCode)");

    // Bind parameters
    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':lastName', $lname);
    $stmt->bindParam(':firstName', $fname);
    $stmt->bindParam(':middleName', $middleName);
    $stmt->bindParam(':homeAddress', $homeAddress);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':mobileNumber', $mobileNumber); // Bind even if empty, assuming DB allows NULL or empty string
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':password', $hashedPass);
    $stmt->bindParam(':profilePicPath', $profilePicPath, $profilePicPath === null ? PDO::PARAM_NULL : PDO::PARAM_STR); // Handle NULL correctly
    $stmt->bindParam(':validIDPath', $validIDPath);       // Bind path (should not be NULL if required)
    $stmt->bindParam(':verificationCode', $verificationCode); // Store the code for verification

    // * STEP 1: Execute the INSERT statement *
    if ($stmt->execute()) {
        // --- SEND VERIFICATION EMAIL ---
        require '../vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

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

            // --- DEBUGGING (Enable ONLY during development/testing) ---
            // $mail->SMTPDebug = 2; // Enable verbose debug output (outputs SMTP commands and responses)
            // $mail->Debugoutput = 'html'; // Output debug info as HTML
            // --- END DEBUGGING ---

            // Recipients
            // Set From address - MUST match Username for Gmail usually
            $mail->setFrom('rence.b.m@gmail.com', 'RM Betis Furniture');
            $mail->addAddress($email, $fname . ' ' . $lname); // Add recipient

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'RM Betis Furniture - Email Verification';
            // Consider using a template file for cleaner HTML emails
            $mailBody = "Dear " . htmlspecialchars($fname) . ",<br><br>";
            $mailBody .= "Thank you for signing up with RM Betis Furniture. Please use the following verification code to activate your account:<br><br>";
            $mailBody .= "<div style='font-size: 1.5em; font-weight: bold; margin: 15px 0; padding: 10px; background-color: #f0f0f0; border-radius: 5px; text-align: center; font-family: Courier New, Courier, monospace;'>$verificationCode</div>";
            $mailBody .= "Enter this code on the verification page.<br><br>";
            $mailBody .= "If you did not sign up, please ignore this email.<br><br>";
            $mailBody .= "Best regards,<br>RM Betis Furniture Team";
            $mail->Body = $mailBody;
            $mail->AltBody = "Your verification code is: $verificationCode. Enter this code on the verification page. If you did not sign up, please ignore this email."; // Plain text version

            // * STEP 2: Attempt to send the email *
            $mail->send();

            // Store email in session for verification page
            $_SESSION['verification_email'] = $email;
            
            // Redirect to verification page
            header("Location: verify.php?email=" . urlencode($email));
            exit();

        } catch (Exception $e) {
            // If email fails, still proceed with registration but log the error
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            $_SESSION['signup_errors']['general'] = "Registration successful but verification email could not be sent. Please contact support.";
            header('Location: signup.php');
            exit();
        }
    } else {
        // Handle database insertion failure
        $_SESSION['signup_errors']['database'] = "Error: Registration failed. Please try again.";
        header('Location: signup.php');
        exit();
    }
} catch (PDOException $e) {
    // Handle database errors
    error_log("Database error: " . $e->getMessage());
    $_SESSION['signup_errors']['database'] = "Error: Registration failed. Please try again.";
    header('Location: signup.php');
    exit();
}
?>
