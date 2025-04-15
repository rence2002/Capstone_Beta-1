<?php
session_start();
include("../config/database.php");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enteredCode = $_POST['verificationCode'] ?? '';

    // Check if the registration data and verification code are set in the session
    if (isset($_SESSION['registration']) && isset($_SESSION['registration']['verificationCode'])) {
        $storedCode = $_SESSION['registration']['verificationCode'];

        // Check if entered code matches the stored code
        if ($enteredCode == $storedCode) {
            // Extract user data from the session
            $userId = $_SESSION['registration']['userId'];
            $lastName = $_SESSION['registration']['lastName'];
            $firstName = $_SESSION['registration']['firstName'];
            $middleName = $_SESSION['registration']['middleName'];
            $homeAddress = $_SESSION['registration']['homeAddress'];
            $email = $_SESSION['registration']['email'];
            $mobileNumber = $_SESSION['registration']['mobileNumber'];
            $password = $_SESSION['registration']['hashedPass'];
            $status = $_SESSION['registration']['status'];
            $profilePicPath = $_SESSION['registration']['profilePicPath'];
            $validIDPath = $_SESSION['registration']['validIDPath']; // Add valid ID path

            // Insert user into database
            try {
                $stmt = $pdo->prepare("INSERT INTO tbl_user_info (
                    User_ID, 
                    Last_Name, 
                    First_Name,
                    Middle_Name, 
                    Home_Address, 
                    Email_Address,
                    Mobile_Number, 
                    Password, 
                    Status,
                    PicPath,
                    Valid_ID_Path
                ) VALUES (
                    :userId, 
                    :lastName, 
                    :firstName,
                    :middleName, 
                    :homeAddress, 
                    :email,
                    :mobileNumber, 
                    :password, 
                    :status,
                    :profilePicPath,
                    :validIDPath
                )");
                
                $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
                $stmt->bindParam(':lastName', $lastName, PDO::PARAM_STR);
                $stmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);
                $stmt->bindParam(':middleName', $middleName, PDO::PARAM_STR);
                $stmt->bindParam(':homeAddress', $homeAddress, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':mobileNumber', $mobileNumber, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                $stmt->bindParam(':profilePicPath', $profilePicPath, PDO::PARAM_STR);
                $stmt->bindParam(':validIDPath', $validIDPath, PDO::PARAM_STR); // Bind valid ID path
                $stmt->execute();

                // Set a session flag for successful registration
                $_SESSION['registration_success'] = true;
                
                // Clear the session and redirect to the confirmation page
                unset($_SESSION['registration']);
                // Redirect to the confirmation page
                header("Location: confirmation.php");
                exit();
            } catch (PDOException $e) {
                die("Database error: " . $e->getMessage());
            }
        } else {
            $error = "Incorrect verification code.";
        }
    } else {
        //if registration data is not found redirect to the signup page
        header("Location: signup.php");
        exit;
        $error = "Registration data not found.";
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
        /* Add any additional styles here */
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
