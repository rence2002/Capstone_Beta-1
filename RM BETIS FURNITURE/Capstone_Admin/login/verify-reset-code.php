<?php
include("../config/database.php");

if (isset($_GET['email'])) {
    $email = $_GET['email'];
} else {
    header("Location: forgot-password-form.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $enteredCode = $_POST['verification_code'] ?? '';

    // Fetch user data from the database
    $query = "SELECT * FROM tbl_admin_info WHERE Email_Address = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $storedCode = $user['verification_code'];
        $codeExpiry = $user['verification_code_expiry'];

        // Validate the entered code and check if it's expired
        if ($enteredCode == $storedCode && $codeExpiry > date('Y-m-d H:i:s')) {
             header("Location: reset-password.php?email=$email");
             exit();
        } else {
            $error = "Invalid or expired verification code.";
        }
    }else{
         $error = "Invalid or expired verification code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Reset Code</title>
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <link href="../static/css-files/LogIn.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <form method="POST" action="verify-reset-code.php">
            <h2>Verify Code</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <input type="hidden" name="email" value="<?php echo $email; ?>">
            <div class="input-field">
                <input type="text" name="verification_code" placeholder="Enter Verification Code" required>
            </div>

            <button type="submit">Verify</button>
        </form>
    </div>
</body>

</html>
