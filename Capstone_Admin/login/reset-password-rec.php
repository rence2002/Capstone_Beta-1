<?php
// INCLUDE DATABASE CONNECTION
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $verificationCode = $_POST['verification_code'] ?? '';
    $newPassword = $_POST['New_Password'] ?? '';
    $confirmPassword = $_POST['Confirm_Password'] ?? '';

    // VALIDATE INPUT
    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('Error: Password and Confirm Password do not match.'); window.location.href='reset-password.php?email=$email';</script>";
        exit();
    }

    // HASH NEW PASSWORD FOR SECURITY
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // CHECK IF EMAIL IS VALID
    $query = "SELECT * FROM tbl_admin_info WHERE Email_Address = :email AND verification_code = :verification_code";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':verification_code', $verificationCode);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
         $codeExpiry = $user['verification_code_expiry'];

        if ($codeExpiry < date('Y-m-d H:i:s')) {
             echo "<script>alert('Error: the verfication code has already expired.'); window.location.href='forgot-password-form.php';</script>";
             exit();
        }
        // UPDATE PASSWORD IN DATABASE
        try{
            $updateQuery = "UPDATE tbl_admin_info SET Password = :password, verification_code = NULL, verification_code_expiry = NULL WHERE Email_Address = :email";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':email', $email);
            $updateStmt->execute();

            echo "<script>alert('Password reset successfully. You can now log in with your new password.'); window.location.href='../index.php';</script>";
        }catch (PDOException $e) {
                echo "<script>alert('Error updating password: " . $e->getMessage() . "'); window.location.href='reset-password.php?email=$email';</script>";
                exit();
            }
    } else {
        echo "<script>alert('Error: Invalid email or verification code.'); window.location.href='reset-password.php?email=$email';</script>";
    }
}
?>
