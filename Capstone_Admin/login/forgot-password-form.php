<?php
// INCLUDE DATABASE CONNECTION
include("../config/database.php");
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['Email'] ?? '';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    // Verify reCAPTCHA response
    $secretKey = '6LdGk9wqAAAAAJB1oI6jUNdeLa2IM83P0-02sTBj'; // Replace with your secret key
    $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
    $responseData = json_decode($verifyResponse);

    if (!$responseData->success) {
        echo "<script>alert('Error: reCAPTCHA verification failed. Please try again.'); window.location.href='forgot-password-form.php';</script>";
        exit();
    }

    // VALIDATE INPUT
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Error: Invalid email address.'); window.location.href='forgot-password-form.php';</script>";
        exit();
    }

    // CHECK IF EMAIL EXISTS IN DATABASE
    $query = "SELECT * FROM tbl_admin_info WHERE Email_Address = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // GENERATE VERIFICATION CODE
        $verificationCode = rand(100000, 999999); // 6-digit code
        $verificationCodeExpiry = date('Y-m-d H:i:s', strtotime('+15 minutes')); // 15-minute expiry

        // STORE VERIFICATION CODE IN DATABASE
        $updateQuery = "UPDATE tbl_admin_info SET verification_code = :verification_code, verification_code_expiry = :verification_code_expiry WHERE Email_Address = :email";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->bindParam(':verification_code', $verificationCode);
        $updateStmt->bindParam(':verification_code_expiry', $verificationCodeExpiry);
        $updateStmt->bindParam(':email', $email);
        $updateStmt->execute();

        // SEND VERIFICATION CODE TO USER EMAIL
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'rence.b.m@gmail.com'; // SMTP username
            $mail->Password = 'eefn tvbk hbls asgp'; // SMTP password (use App Password if 2FA is enabled)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('your-email@gmail.com', 'Your App Name');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Verification Code';
            $mail->Body = "Your verification code is: <b>$verificationCode</b><br>This code will expire in 15 minutes.";

            $mail->send();
            // Redirect directly to reset-password.php
            header("Location: reset-password.php?email=$email");
            exit();
        } catch (Exception $e) {
            echo "<script>alert('Error: Failed to send verification code. Mailer Error: {$mail->ErrorInfo}'); window.location.href='forgot-password-form.php';</script>";
        }
    } else {
        echo "<script>alert('Error: Email address not found.'); window.location.href='forgot-password-form.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <link href="../static/css-files/LogIn.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>
    <div class="wrapper">
        <form action="forgot-password-form.php" method="post">
            <h2>Forgot Password</h2>

            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

            <div class="input-field">
                <input type="email" name="Email" required id="email">
                <label for="email">Enter your Email Address</label>
            </div>

            <div class="g-recaptcha" data-sitekey="6LdGk9wqAAAAAPGLtqpTt5f2IdBdSFGjA806AF7X"></div>

            <button type="submit">Send Verification Code</button>

            <div class="register">
                <p>Remembered your password? <a href="../index.php">Log In</a></p>
            </div>
        </form>
    </div>
</body>

</html>
