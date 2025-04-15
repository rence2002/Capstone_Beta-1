<?php
session_start();
include("../config/database.php");

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

            <form method="POST" action="">
                <div class="form-group">
                    <label for="verificationCode">Verification Code</label>
                    <input type="text" id="verificationCode" name="verificationCode" required>
                </div>
                <button type="submit">Verify</button>
            </form>
        </div>
    </div>
</body>

</html>
