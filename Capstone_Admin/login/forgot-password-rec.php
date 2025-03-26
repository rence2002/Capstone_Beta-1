<?php
session_start();
include './config/database.php'; 
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['Email'] ?? '';
    $new_password = $_POST['New_Password'] ?? '';
    $confirm_password = $_POST['Confirm_Password'] ?? '';

    // Validate input
    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        echo "<script>alert('Error: All fields are required.'); window.location.href='forgot-password-form.php';</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Error: Invalid email address.'); window.location.href='forgot-password-form.php';</script>";
        exit();
    }

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Error: New password and confirmation password do not match.'); window.location.href='forgot-password-form.php';</script>";
        exit();
    }

    try {
        // Check if email exists in database
        $query = "SELECT * FROM tbl_admin_info WHERE Email_Address = :email";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password in database
            $updateQuery = "UPDATE tbl_admin_info SET Password = :password WHERE Email_Address = :email";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->bindParam(':password', $hashed_password);
            $updateStmt->bindParam(':email', $email);
            $updateStmt->execute();

            // Send confirmation email to user
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
                $mail->Subject = 'Password Changed Successfully';
                $mail->Body    = "Your password has been successfully changed.";

                $mail->send();
                header("Location: password-reset-success.php");
                exit();
            } catch (Exception $e) {
                echo "<script>alert('Error: Failed to send confirmation email. Mailer Error: {$mail->ErrorInfo}'); window.location.href='forgot-password-form.php';</script>";
            }
        } else {
            echo "<script>alert('Error: Email address not found.'); window.location.href='forgot-password-form.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . $e->getMessage() . "'); window.location.href='forgot-password-form.php';</script>";
    }
}
?>
