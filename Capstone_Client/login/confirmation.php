<?php
session_start();

// Check if the registration process was indeed successful (you can add more checks here if needed)
if (!isset($_SESSION['registration_success']) || $_SESSION['registration_success'] !== true) {
    // If not, redirect to signup or an error page
    header("Location: signup.php"); // Or some other error page
    exit;
}

// Clear the flag to prevent page from being access by refresh.
unset($_SESSION['registration_success']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <link rel="stylesheet" href="../static/css-files/LogIn.css">
    <style>
        /* Add any additional styles here */
        .success-container {
            text-align: center;
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 50px auto; /* Center the container */
        }

        .success-container h1 {
            color: #28a745; /* Green color for success */
            margin-bottom: 15px;
        }

        .success-container p {
            color: #333;
            margin-bottom: 20px;
        }

        .success-container a {
            background-color: #5bc0de; /* Info color */
            color: #fff;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
        }

        .success-container a:hover {
            background-color: #31b0d5;
        }
        .container{
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .left{
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>

<body>
<div class="container">
        <div class="left">
           <div class="success-container">
                <h1>Registration Successful!</h1>
                <p>Your account has been successfully created. You can now log in.</p>
                <a href="login.php">Continue to Login</a>
            </div>
        </div>
    </div>
</body>

</html>
