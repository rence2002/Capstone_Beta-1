<?php
session_start();
echo "<!-- error.php reached -->";

// Get the error message from the session
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : "An unknown error occurred.";
$errorDetails = isset($_SESSION['error_details']) ? $_SESSION['error_details'] : "";

// Clear the session variables so the error doesn't persist
unset($_SESSION['error_message']);
unset($_SESSION['error_details']);
echo "<!-- errorMessage: " . htmlspecialchars($errorMessage) . " -->";
echo "<!-- errorDetails: " . htmlspecialchars($errorDetails) . " -->";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link href="./static/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
        }

        .error-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 800px; /* Increased max-width */
        }

        .error-container h1 {
            color: #dc3545; /* Bootstrap danger color */
            margin-bottom: 20px;
        }

        .error-container p {
            margin-bottom: 20px;
        }
        .error-details{
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #f9f9f9;
            white-space: pre-wrap; /* Preserve line breaks */
            overflow-wrap: break-word; /* Allow long words to break */
            text-align: left;
            margin-bottom: 20px;

        }

        .error-container .btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .error-container .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Error</h1>
        <p><?php echo $errorMessage; ?></p>
        <?php if (!empty($errorDetails)): ?>
            <div class="error-details">
                <strong>Error Details:</strong><br>
                <?php echo htmlspecialchars($errorDetails); ?>
            </div>
        <?php endif; ?>
        <a href="index.php" class="btn">Go back to login</a>
    </div>
</body>
</html>
