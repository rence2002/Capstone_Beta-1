<?php
session_start();

// Check if an error message is set in the session
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    // Clear the error message from the session to prevent it from displaying on refresh
    unset($_SESSION['error_message']);
} else {
    // Default error message if none is set in the session
    $errorMessage = "An unexpected error occurred.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="../static/css-files/error.css">
    <style>
        /* basic styles, you should replace these with the styles from your project*/
         body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .error-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .error-container h1 {
            color: #d9534f; /* Red color for error */
            margin-bottom: 15px;
        }

        .error-container p {
            color: #333;
            margin-bottom: 20px;
        }

        .error-container a {
            background-color: #5bc0de; /* Info color */
            color: #fff;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
        }

        .error-container a:hover {
            background-color: #31b0d5;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Oops! Something went wrong.</h1>
        <p><?php echo htmlspecialchars($errorMessage); ?></p>
        <a href="../index.php">Go back to login</a>
    </div>
</body>
</html>
