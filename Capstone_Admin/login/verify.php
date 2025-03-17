<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enteredCode = $_POST['verification_code'] ?? '';

    if ($enteredCode == $_SESSION['registration']['verificationCode']) {
        // INCLUDE DATABASE CONNECTION
        include("../config/database.php");

        // RETRIEVE USER DETAILS FROM SESSION
        $admin_id = $_SESSION['registration']['admin_id'];
        $lname = $_SESSION['registration']['lname'];
        $fname = $_SESSION['registration']['fname'];
        $mname = $_SESSION['registration']['mname'];
        $address = $_SESSION['registration']['address'];
        $email = $_SESSION['registration']['email'];
        $mobile = $_SESSION['registration']['mobile'];
        $status = $_SESSION['registration']['status'];
        $hashedPass = $_SESSION['registration']['hashedPass'];
        $picPath = $_SESSION['registration']['picPath'];

        // CREATE SQL QUERY
        $query = "INSERT INTO tbl_admin_info (
            Admin_ID, 
            Last_Name, 
            First_Name, 
            Middle_Name, 
            Home_Address, 
            Email_Address, 
            Mobile_Number, 
            Status, 
            Password, 
            PicPath
        ) VALUES (
            :adminID, 
            :lname, 
            :fname, 
            :mname, 
            :address, 
            :email, 
            :mobile, 
            :status, 
            :pass, 
            :picPath
        )";

        // PREPARE AND EXECUTE QUERY
        try {
            $stmt = $pdo->prepare($query);

            $stmt->bindParam(':adminID', $admin_id);
            $stmt->bindParam(':lname', $lname);
            $stmt->bindParam(':fname', $fname);
            $stmt->bindParam(':mname', $mname);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':mobile', $mobile);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':pass', $hashedPass);
            $stmt->bindParam(':picPath', $picPath);

            $stmt->execute();

            // CLEAR SESSION DATA
            unset($_SESSION['registration']);

            // REDIRECT TO ADMIN LIST PAGE WITH SUCCESS MESSAGE
            header("Location: ../index.php?message=registered");
            exit();
        } catch (PDOException $e) {
            die("Error: Could not execute the query. " . $e->getMessage());
        }
    } else {
        echo "<p>Invalid verification code. Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code</title>
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <link href="../static/css-files/LogIn.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <form method="POST" action="verify.php">
            <h2>Verify Code</h2>

            <div class="input-field">
                <input type="text" name="verification_code" placeholder="Enter Verification Code" required>
            </div>

            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>