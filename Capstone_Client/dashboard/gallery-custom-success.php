<?php
session_start();
// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Order Success</title>
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link rel="stylesheet" href="../static/css-files/Gallery.css">
    <style>
        .success-page-container {
            text-align: center;
            padding: 50px;
            background-color: #f4f4f4;
            border-radius: 5px;
            margin: 50px auto;
            max-width: 600px;
        }
        .success-page-container h2 {
            color: #008000;
        }
        .success-page-container p {
            margin-bottom: 20px;
        }
        .button-container {
            display: flex;
            justify-content: center;
        }

        .success-page-btn {
            background-color: #4CAF50; /* Green */
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header>
       <!-- Your header code here -->
    </header>
    <main>
        <div class="success-page-container">
            <h2>Your Custom Order Is Being Processed!</h2>
            <p>Thank you for your request. We're now working on your custom furniture order.</p>
            <p>What would you like to do next?</p>
            <div class="button-container">
                <a href="gallery.php" class="success-page-btn">Continue Shopping</a>
                <a href="cart.php" class="success-page-btn">View Cart</a>
                <a href="profile.php" class="success-page-btn">View Order</a>
            </div>
        </div>
    </main>
    <footer class="footer">
         <!-- Your footer code here -->
    </footer>
</body>
</html>
