<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('./config/database.php');

echo "<!-- index.php reached -->";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    // Verify the reCAPTCHA response
    $secretKey = '6LdGk9wqAAAAAJB1oI6jUNdeLa2IM83P0-02sTBj';
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
    $responseKeys = json_decode($response, true);

    if (intval($responseKeys["success"]) !== 1) {
        $_SESSION['error_message'] = "Please complete the CAPTCHA.";
        echo "<!-- Redirecting to error.php (CAPTCHA) -->"; // Debug
        header('Location: error.php');
        exit();
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM tbl_admin_info WHERE Email_Address = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                // Debug: Output the hashed password from the database (keep for debugging)
                //echo "<pre>Stored Hashed Password: " . htmlspecialchars($result['Password']) . "</pre>"; //Removed for testing

                if (password_verify($password, $result['Password'])) {
                    $_SESSION['admin_id'] = $result['Admin_ID'];
                    echo "<!-- Redirecting to dashboard.php -->"; // Debug
                    header('Location: dashboard/dashboard.php');
                    exit();
                } else {
                    $_SESSION['error_message'] = "Password is incorrect!";
                    echo "<!-- Redirecting to error.php (Password) -->"; // Debug
                    header('Location: error.php');
                    exit();
                }
            } else {
                $_SESSION['error_message'] = "No user found with email: " . htmlspecialchars($email);
                echo "<!-- Redirecting to error.php (No User) -->"; // Debug
                header('Location: error.php');
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Database error occurred.";
            $_SESSION['error_details'] = "Database error: " . $e->getMessage();
            echo "<!-- Redirecting to error.php (Database) -->"; // Debug
            header('Location: error.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="./static/css/bootstrap.min.css" rel="stylesheet">
    <link href="./static/css-files/LogIn.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
<div class="wrapper">
    <!-- Left Container -->
    <div class="left-container">
        <img src="./static/images/rm raw png.png" alt="Register Admin" class="rmreg"><br>
        <p>Welcome to RM Betis Furniture Admin Panel! Please log in to access the administrative dashboard. As an admin, you have the authority to manage users, update product listings, monitor transactions, and oversee business operations. Ensure your credentials are secure and do not share them with unauthorized personnel. If you experience login issues, please reset your password or contact the system administrator for assistance.</p>
    </div>

    <!-- Right Container (Login Form) -->
    <div class="right-container">
        <form method="POST" action="index.php">
            <h2>ADMIN LOGIN</h2>

            <div class="input-field">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="input-field">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="g-recaptcha" data-sitekey="6LdGk9wqAAAAAPGLtqpTt5f2IdBdSFGjA806AF7X"></div>

            <div class="forget">
                <a href="./login/forgot-password-form.php">Forgot password?</a>
            </div>

            <button type="submit" class="login">LOGIN</button>

            <div class="register">
                <p>Don't have an account? <a href="./login/register-form.php">Register</a></p>
            </div>
        </form>
    </div>
</div>
</body>
</html>
