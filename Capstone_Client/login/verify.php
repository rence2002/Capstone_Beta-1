<?php
session_start();
include("../config/database.php");
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle resend code request
if (isset($_GET['resend']) && $_GET['resend'] == 'true') {
    $email = $_GET['email'] ?? '';
    if (!empty($email)) {
        try {
            // Generate new verification code
            $verificationCode = random_int(100000, 999999);
            
            // Update the code in the database
            $stmt = $pdo->prepare("UPDATE tbl_user_info SET reset_code = :code WHERE Email_Address = :email AND Status = 'Inactive'");
            $stmt->bindParam(':code', $verificationCode, PDO::PARAM_INT);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            // Send new verification email
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'rence.b.m@gmail.com';
                $mail->Password = 'vlnl qsfo iwjo zlgl';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                //Recipients
                $mail->setFrom('rence.b.m@gmail.com', 'RM Betis Furniture');
                $mail->addAddress($email);

                //Content
                $mail->isHTML(true);
                $mail->Subject = 'RM Betis Furniture - New Verification Code';
                $mailBody = "Your new verification code is: <b>$verificationCode</b><br>";
                $mailBody .= "Please use this code to verify your account.<br>";
                $mailBody .= "If you did not request this code, please ignore this email.";
                $mail->Body = $mailBody;

                $mail->send();
                $success = "A new verification code has been sent to your email.";
            } catch (Exception $e) {
                $error = "Failed to send new verification code. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "Database error. Please try again.";
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enteredCode = $_POST['verificationCode'] ?? '';
    $email = $_GET['email'] ?? ''; // Get the email from the query parameter

    if (!empty($email) && !empty($enteredCode)) {
        try {
            // Check if the entered code matches the one in the database
            $stmt = $pdo->prepare("SELECT User_ID FROM tbl_user_info WHERE Email_Address = :email AND reset_code = :verificationCode AND Status = 'Inactive'");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':verificationCode', $enteredCode, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Verification successful, activate the user account
                $stmt = $pdo->prepare("UPDATE tbl_user_info SET Status = 'Active', reset_code = NULL WHERE Email_Address = :email");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();

                // Redirect to a confirmation page
                $_SESSION['registration_success'] = true;
                header("Location: confirmation.php");
                exit();
            } else {
                $error = "Incorrect verification code or account already verified.";
            }
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    } else {
        $error = "Please enter the verification code.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Verify Your Account</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <link rel="stylesheet" href="../static/css-files/LogIn.css">
    <style>
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        .resend-button {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            padding: 0;
            font-size: 14px;
            margin-top: 10px;
            text-decoration: underline;
        }
        .resend-button:hover {
            color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="right">
            <model-viewer src="../static/images/house.glb" shadow-intensity="1" camera-controls touch-action="pan-y"></model-viewer>
        </div>
        <div class="left">
            <h1>Verify Your Account</h1>

            <?php if (isset($error)) : ?>
                <div class="error">
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($success)) : ?>
                <div class="success">
                    <p><?php echo $success; ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="verificationCode">Verification Code</label>
                    <input type="text" id="verificationCode" name="verificationCode" required>
                </div>
                <button type="submit">Verify</button>
            </form>
            
            <button type="button" class="resend-button" onclick="window.location.href='verify.php?email=<?php echo urlencode($_GET['email'] ?? ''); ?>&resend=true'">
                Didn't receive the code? Click here to resend
            </button>
        </div>
    </div>
</body>

</html>
