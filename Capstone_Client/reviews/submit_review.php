<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include the database connection
include("../config/database.php");

// Include Composer's autoload for Intervention Image
require_once '../vendor/autoload.php';

use Intervention\Image\ImageManagerStatic as Image;

// Initialize Intervention Image
Image::configure(['driver' => 'gd']);

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
    $upload_dir = "C:/xampp/htdocs/Capstone_Beta/Capstone_Client/uploads/review_pics/";

    // Ensure the directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    foreach ($_FILES['review_image']['tmp_name'] as $key => $tmp_name) {
        $file_name = basename($_FILES['review_image']['name'][$key]);
        $file_name = preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $file_name); // Sanitize filename
        $target_file = $upload_dir . uniqid() . "_" . $file_name;

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['review_image']['type'][$key];
        $max_file_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid file type for image: $file_name. Only JPEG, PNG, and GIF are allowed.";
            continue;
        }

        if ($_FILES['review_image']['size'][$key] > $max_file_size) {
            $errors[] = "File size exceeds the maximum limit of 2MB: $file_name.";
            continue;
        }

        // Resize and crop the image using Intervention Image
        try {
            $image = Image::make($_FILES['review_image']['tmp_name'][$key]);
            $image->resize(150, 150, function ($constraint) {
                $constraint->aspectRatio(); // Maintain aspect ratio
                $constraint->upsize(); // Prevent upscaling
            })->crop(150, 150); // Crop to exact dimensions

            // Save the resized image with reduced quality
            $image->save($target_file, 80);

            $uploaded_images[] = str_replace("C:/xampp/htdocs/", "../", $target_file);
        } catch (Exception $e) {
            error_log("Error processing image: " . $e->getMessage());
            $errors[] = "An error occurred while processing the image: $file_name. Please try again.";
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
        'pic_path' => !empty($uploaded_images) ? json_encode($uploaded_images) : null
    ]);

    $_SESSION['success'] = "Your review has been submitted successfully!";
} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred while submitting your review. Please try again later.";
}

// Redirect back to the review page
header("location: review.php");
exit;