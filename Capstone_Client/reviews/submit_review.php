<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include the database connection
include("../config/database.php");

// Initialize variables
$product_id = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

// Validate inputs
$errors = [];
if (empty($product_id)) {
    $errors[] = "Please select a product.";
}
if (empty($review_text)) {
    $errors[] = "Review text is required.";
}
if ($rating < 1 || $rating > 5) {
    $errors[] = "Rating must be between 1 and 5.";
}

// Handle file uploads
$uploaded_images = [];
if (!empty($_FILES['review_image']['name'][0])) {
    $upload_dir = "../uploads/review_pics/"; // Target directory

    // Ensure the directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true); // Create the directory if it doesn't exist
    }

    // Resolve the absolute path of the upload directory
    $absolute_upload_dir = realpath($upload_dir);

    foreach ($_FILES['review_image']['tmp_name'] as $key => $tmp_name) {
        $file_name = basename($_FILES['review_image']['name'][$key]);
        $target_file = $upload_dir . uniqid() . "_" . $file_name; // Unique file name to avoid conflicts

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['review_image']['type'][$key];
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid file type for image: $file_name. Only JPEG, PNG, and GIF are allowed.";
            continue;
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['review_image']['tmp_name'][$key], $target_file)) {
            // Generate the relative path dynamically
            $relative_path = str_replace(realpath("../"), "", $target_file);
            $uploaded_images[] = $relative_path;
        } else {
            $errors[] = "Failed to upload image: $file_name.";
        }
    }
}

// If there are errors, redirect back to the review page with error messages
if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("location: review.php");
    exit;
}

// Insert the review into the database
try {
    $stmt = $pdo->prepare("
        INSERT INTO tbl_reviews (User_ID, Product_ID, Rating, Review_Text, PicPath)
        VALUES (:user_id, :product_id, :rating, :review_text, :pic_path)
    ");
    $stmt->execute([
        'user_id' => $_SESSION["user_id"],
        'product_id' => $product_id,
        'rating' => $rating,
        'review_text' => $review_text,
        'pic_path' => !empty($uploaded_images) ? json_encode($uploaded_images, JSON_UNESCAPED_SLASHES) : null
    ]);

    $_SESSION['success'] = "Your review has been submitted successfully!";
} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred while submitting your review. Please try again later.";
}

// Redirect back to the review page
header("location: review.php");
exit;