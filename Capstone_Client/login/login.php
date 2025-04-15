<?php
session_start();
include '../config/database.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check if the user exists
    $stmt = $pdo->prepare("SELECT * FROM tbl_user_info WHERE Email_Address = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['Password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['User_ID'];
        $_SESSION['user_name'] = $user['First_Name'];

        // Redirect to dashboard
        header("Location: ../dashboard/home.php");
        exit();
    } else {
        // Redirect back to login with an error
        $_SESSION['error_message'] = "Invalid email or password.";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <link rel="stylesheet" href="../static/css-files/LogIn.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        /* Add any additional styles here */
    </style>
</head>

<body>
    <div class="container">
        <div class="right">
            <model-viewer src="../static/images/house.glb" shadow-intensity="1" camera-controls touch-action="pan-y"></model-viewer>
        </div>
        <div class="left">
            <h1>Welcome to RM Betis Furniture, Please login to your account.</h1>
            <div class="social-login">
               <!-- Remove social login buttons here -->
            </div>
            <!-- <div class="divider">- OR -</div> -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="e.g. rmbetisfurniture@gmail.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <!-- <div class="g-recaptcha" data-sitekey="6LdGk9wqAAAAAPGLtqpTt5f2IdBdSFGjA806AF7X" style="transform: scale(0.7); transform-origin: 0 0;"></div> -->
                <div class="form-options">
                    <!-- <label><input type="checkbox"> Remember me</label> -->
                    <a href="forget-password.php">Forgot password?</a>
                </div>
                <div class="buttons">
                    <a href="signup.php"><button type="button" class="signup">Sign up</button></a>
                    <button type="submit" class="login">Login</button>
                </div>
            </form>
            <br>
            <div class="terms">
                By signing up, you agree to RM Betis Furniture's 
                <button type="button" id="termsButton" class="termsbutton">Terms and Conditions & Privacy Policy</button>
            </div>
            <!-- Modal for Terms and Conditions -->
            <div id="termsModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h1>Terms and Conditions</h1>
                    <p>Welcome to RM Betis Furniture. By using our system and placing an order, you agree to the following terms and conditions:</p>
                    <h2>1. Downpayment Policy</h2>
                    <p>Before we process or accept your order, you are required to pay a 60% downpayment. The remaining balance must be settled upon completion of the order.</p>
                    <h2>2. No Cancellation Policy</h2>
                    <p>Once an order is placed, cancellations are not allowed. Please ensure that all details of your order are correct before confirming.</p>
                    <h2>3. Privacy Policy</h2>
                    <p>All personal information you provide in this system will be kept private and secure. We are committed to protecting your data and will not share it with third parties without your consent.</p>
                    <p>If you have any questions or concerns regarding these terms, please contact us for clarification.</p>
                </div>
            </div>
            <!-- Add modal styles -->
            <style>
                .modal {
                    position: fixed;
                    z-index: 1;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    overflow: auto;
                    background-color: rgba(0, 0, 0, 0.4);
                }
                .modal-content {
                    background-color: #fff;
                    margin: 15% auto;
                    padding: 20px;
                    border: 1px solid #888;
                    width: 80%;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
                }
                .close {
                    color: #aaa;
                    float: right;
                    font-size: 28px;
                    font-weight: bold;
                    cursor: pointer;
                }
                .close:hover,
                .close:focus {
                    color: black;
                    text-decoration: none;
                }
            </style>
            <script>
                const termsButton = document.getElementById('termsButton');
                const termsModal = document.getElementById('termsModal');
                const closeModal = termsModal.querySelector('.close');

                termsButton.addEventListener('click', () => {
                    termsModal.style.display = 'block';
                });

                closeModal.addEventListener('click', () => {
                    termsModal.style.display = 'none';
                });

                window.addEventListener('click', (event) => {
                    if (event.target === termsModal) {
                        termsModal.style.display = 'none';
                    }
                });
            </script>
        </div>
    </div>
    <a class="theme-toggle">
        <span class="entypo--switch1"></span>
    </a>

    <script>
        const toggleButton = document.getElementsByClassName('theme-toggle')[0]; // Access the first element
        const body = document.body;

        toggleButton.addEventListener('click', () => {
            // Toggle the 'dark-mode' class on the body
            body.classList.toggle('dark-mode');

            // Change button icon based on the current theme
            if (body.classList.contains('dark-mode')) {
                toggleButton.innerHTML = '<span class="entypo--switch1"></span>'; // Switch to dark mode icon
            } else {
                toggleButton.innerHTML = '<span class="entypo--switch2"></span>'; // Switch to light mode icon
            }
        });
    </script>
</body>

</html>
