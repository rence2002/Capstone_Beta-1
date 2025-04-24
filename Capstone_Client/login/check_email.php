<?php
// c:\xampp\htdocs\Capstone_Beta\Capstone_Client\login\check_email.php
header('Content-Type: application/json'); // Important: Set response type to JSON

include("../config/database.php"); // Include your database connection

$response = ['exists' => false, 'error' => null, 'message' => '']; // Default response structure

// Check if it's a POST request and email is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Optional: Basic server-side format validation (client-side should handle this first)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // Bad Request
        $response['error'] = true;
        $response['message'] = 'Invalid email format provided.';
        echo json_encode($response);
        exit;
    }

    try {
        // Prepare and execute the query efficiently
        $stmtCheckEmail = $pdo->prepare("SELECT 1 FROM tbl_user_info WHERE Email_Address = :email LIMIT 1");
        $stmtCheckEmail->bindParam(':email', $email, PDO::PARAM_STR);
        $stmtCheckEmail->execute();

        // fetchColumn() returns the value of the first column (1) if a row is found, otherwise false
        if ($stmtCheckEmail->fetchColumn()) {
            $response['exists'] = true;
            $response['message'] = 'Error: This email address is already registered.';
        } else {
            $response['exists'] = false;
            // Optional: Add a success message if needed, though usually not required for just checking
            // $response['message'] = 'Email is available.';
        }

    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        // Log the detailed error for server admin
        error_log("Database error checking email (AJAX): " . $e->getMessage());
        // Provide a generic error message to the client
        $response['error'] = true;
        $response['message'] = 'Could not check email status due to a server issue. Please try again later.';
    }

} else {
    // Handle cases where the request is not POST or email is missing
    http_response_code(400); // Bad Request
    $response['error'] = true;
    $response['message'] = 'Invalid request. Email parameter missing or wrong request method.';
}

// Send the JSON response back to the JavaScript
echo json_encode($response);
exit;
?>