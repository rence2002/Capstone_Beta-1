<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin/login.php");
    exit();
}

try {
    // Check if the form is submitted via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get user input from the web form
        $userID = filter_input(INPUT_POST, 'txtUserID', FILTER_SANITIZE_STRING);
        $productID = filter_input(INPUT_POST, 'txtProductID', FILTER_VALIDATE_INT);
        $rating = filter_input(INPUT_POST, 'txtRating', FILTER_VALIDATE_INT);
        $reviewText = htmlspecialchars(trim($_POST['txtReviewText']));

        // Check if all required fields are filled
        if (!$userID || !$rating || !$reviewText || !$productID) {
            throw new Exception("All fields are required. Please fill out the form correctly.");
        }

        // Validate the input (for example, ensure rating is a valid number between 1 and 5)
        if ($rating < 1 || $rating > 5) {
            throw new Exception("Invalid rating. Please select a value between 1 and 5.");
        }

        // Initialize the PicPath variables
        $picPaths = [];

        // Handle file uploads
        for ($i = 1; $i <= 3; $i++) {
            $fileKey = "reviewPic$i";
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/review_pics/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileTmpPath = $_FILES[$fileKey]['tmp_name'];
                $fileName = basename($_FILES[$fileKey]['name']);
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueIdentifier = time() . '_' . bin2hex(random_bytes(5)); // Unique identifier
                $newFileName = 'review_' . $userID . '_' . $uniqueIdentifier . '.' . $fileExtension;
                $filePath = $uploadDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $filePath)) {
                    $picPaths[] = $filePath;
                }
            }
        }

        // Convert the PicPath array to a JSON string
        $picPathsJson = json_encode($picPaths);

        // Insert the review into the database
        $query = "
            INSERT INTO tbl_reviews (User_ID, Product_ID, Rating, Review_Text, PicPath) 
            VALUES (:userID, :productID, :rating, :reviewText, :picPaths)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':userID', $userID);
        $stmt->bindParam(':productID', $productID);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':reviewText', $reviewText);
        $stmt->bindParam(':picPaths', $picPathsJson);

        if ($stmt->execute()) {
            // Successfully inserted, redirect to the review list page
            header("Location: ../reviews/read-all-reviews-form.php?status=success");
            exit();
        } else {
            throw new Exception("An error occurred while saving the review. Please try again.");
        }
    } else {
        // Redirect to form page if accessed incorrectly
        header("Location: ../reviews/create-review-form.php");
        exit();
    }
} catch (Exception $e) {
    // Handle any errors
    echo "Error: " . $e->getMessage();
    exit();
}
?>
