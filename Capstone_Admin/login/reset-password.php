<?php
include("../config/database.php");

$email = $_GET['email'] ?? '';
$showForm = false; // Flag to decide whether to show the form
$verificationCode = '';

// Check if email is present
if (!empty($email)) {
    // Fetch user data from the database
    $query = "SELECT * FROM tbl_admin_info WHERE Email_Address = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
         if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $verificationCode = $_POST['verification_code'] ?? '';
            $storedCode = $user['verification_code'];
            $codeExpiry = $user['verification_code_expiry'];

            // Validate the code and check if it's expired
            if ($verificationCode == $storedCode && $codeExpiry > date('Y-m-d H:i:s')) {
                $showForm = true; // Show the form if the code is valid
            } else {
                $error = "Invalid or expired verification code.";
            }
        }
    } else {
        $error = "Invalid email or verification code.";
    }
} else {
    $error = "Invalid email or verification code.";
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <link href="../static/css-files/LogIn.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <?php if (isset($error)) : ?>
            <p style="color:red;"><?php echo $error; ?></p>
            <a href="forgot-password-form.php">Try again</a>
        <?php endif; ?>

        <?php if (!$showForm && !isset($error)) : ?>
            <form method="post" >
                 <h2>Enter Verification Code</h2>
                 <input type="hidden" name="email" value="<?php echo $email; ?>">
                <div class="input-field">
                    <input type="text" name="verification_code" placeholder="Verification Code" required>
                </div>
                <button type="submit" >Verify</button>
            </form>
        <?php endif; ?>

        <?php if ($showForm) : ?>
            <form action="reset-password-rec.php" method="post">
                <h2>Reset Password</h2>

                <input type="hidden" name="email" value="<?php echo $email; ?>">
                <input type="hidden" name="verification_code" value="<?php echo $verificationCode; ?>">

                <div class="input-field">
                    <input type="password" name="New_Password" required id="new_password">
                    <label for="new_password">Enter your New Password</label>
                </div>

                <div class="input-field">
                    <input type="password" name="Confirm_Password" required id="confirm_password">
                    <label for="confirm_password">Confirm your New Password</label>
                </div>

                <button type="submit">Reset Password</button>

                <div class="register">
                    <p>Remembered your password? <a href="../index.php">Log In</a></p>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>
