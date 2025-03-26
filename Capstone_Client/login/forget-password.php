<?php
// login/forget-password.php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

session_start();
include('../config/database.php'); // Ensure this file correctly connects to the database

require '../vendor/autoload.php'; // Include PHPMailer

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($errors)) {
        // Check if email exists in the database
        try {
            $stmt = $pdo->prepare("SELECT * FROM tbl_user_info WHERE Email_Address = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Generate a 4-digit verification code
                $verificationCode = rand(1000, 9999);
                $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Code expires in 1 hour

                // Store the code and expiry in the database
                $stmt = $pdo->prepare("UPDATE tbl_user_info SET reset_code = :code, reset_code_expiry = :expiry WHERE Email_Address = :email");
                $stmt->bindParam(':code', $verificationCode, PDO::PARAM_INT);
                $stmt->bindParam(':expiry', $expiry, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();

                // Send email with verification code
                $mail = new PHPMailer(true);
                try {
                    // Server settings (adjust to your email server settings)
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
                    $mail->SMTPAuth = true;
                    $mail->Username = 'rence.b.m@gmail.com'; // Replace with your email
                    $mail->Password = 'vlnl qsfo iwjo zlgl'; // Replace with your app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('your-email@gmail.com', 'RM Betis Furniture'); // Replace with your details
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Verification Code';
                    $mail->Body = "Your password reset verification code is: <b>$verificationCode</b><br>This code will expire in 1 hour.";

                    $mail->send();

                    // Store the email and code in the session for use in password-reset.php
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_code_sent'] = true; // Flag to show a message in password-reset
                    header("Location: password-reset.php");
                    exit();

                } catch (Exception $e) {
                    $errors[] = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $errors[] = "No user found with that email.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../static/css-files/LogIn.css">
    <style>
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="left">
        <h1>Forgot Password</h1>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Send Verification Code</button>
        </form>
        <a href="../index.php">Back to Login</a>
    </div>
</div>
</body>
</html>
