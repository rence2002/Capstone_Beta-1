<?php
// login/password-reset.php

session_start();
include('../config/database.php'); // Ensure correct database connection

$errors = [];
$success = false;
$user = null;
$codeSent = false; //initialize code sent

// Check if email is in the session
if (!isset($_SESSION['reset_email'])) {
    header("Location: forget-password.php");
    exit;
}

$email = $_SESSION['reset_email'];
// Check if code was already sent
if (isset($_SESSION['reset_code_sent']) && $_SESSION['reset_code_sent'] === true) {
    $codeSent = true;
    unset($_SESSION['reset_code_sent']); // Remove the flag
}

// Verify token
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_user_info WHERE Email_Address = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $errors[] = "Invalid User.";
    } else {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $enteredCode = $_POST['verificationCode'] ?? '';
            if ($enteredCode == $user['reset_code']) {
                if ($user['reset_code_expiry'] > date("Y-m-d H:i:s")) {
                    // Proceed to reset password
                    $password = $_POST['password'] ?? '';
                    $confirmPassword = $_POST['confirmPassword'] ?? '';

                    // Validate password
                    if (strlen($password) < 8) {
                        $errors[] = "Password must be at least 8 characters long.";
                    }
                    if ($password !== $confirmPassword) {
                        $errors[] = "Passwords do not match.";
                    }

                    if (empty($errors)) {
                        // Hash password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                        // Update password and clear code
                        $stmt = $pdo->prepare("UPDATE tbl_user_info SET Password = :password, reset_code = NULL, reset_code_expiry = NULL WHERE Email_Address = :email");
                        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                        $stmt->bindParam(':email', $user['Email_Address'], PDO::PARAM_STR);
                        $stmt->execute();

                        $success = true;
                        // clear session
                        unset($_SESSION['reset_email']);
                    }
                } else {
                    $errors[] = "Code expired.";
                }
            } else {
                $errors[] = "Incorrect code.";
            }
        }
    }
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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

        .info {
            color: blue;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="left">
            <h1>Reset Password</h1>
            <?php if ($codeSent) : ?>
                <div class="info">
                    <p>A verification code has been sent to your email. Please check your inbox.</p>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)) : ?>
                <div class="error">
                    <?php foreach ($errors as $error) : ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($success) : ?>
                <div class="success">
                    <p>Your password has been successfully updated.</p>
                    <a href="../index.php">Back to Login</a>
                </div>
            <?php else : ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="verificationCode">Verification Code</label>
                        <input type="text" id="verificationCode" name="verificationCode" required>
                    </div>
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <button type="submit">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
