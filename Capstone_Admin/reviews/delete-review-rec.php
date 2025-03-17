<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if the review ID is provided via GET (usually this would be from a form submission)
if (isset($_GET['id'])) {
    $reviewID = $_GET['id'];

    // Fetch the PicPath from the database
    $stmt = $pdo->prepare("SELECT PicPath FROM tbl_reviews WHERE Review_ID = :review_id");
    $stmt->bindParam(':review_id', $reviewID, PDO::PARAM_INT);
    $stmt->execute();
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($review) {
        // Decode the PicPath JSON string to an array
        $picPaths = json_decode($review['PicPath'], true) ?? [];

        // Delete the pictures from the server
        foreach ($picPaths as $picPath) {
            if (file_exists($picPath)) {
                unlink($picPath);
            }
        }

        // Delete the review record from the database
        $deleteStmt = $pdo->prepare("DELETE FROM tbl_reviews WHERE Review_ID = :review_id");
        $deleteStmt->bindParam(':review_id', $reviewID, PDO::PARAM_INT);

        if ($deleteStmt->execute()) {
            // Successfully deleted, redirect to the review list page
            header("Location: read-all-reviews-form.php?status=deleted");
        } else {
            // If there was an error, output a message (optional)
            echo "Failed to delete review.";
        }
    } else {
        // If no review found, redirect to the review list page
        header("Location: read-all-reviews-form.php?status=not_found");
    }
    exit();
} else {
    // If Review ID is not set in the GET request, show an error
    echo "Review ID is missing!";
    exit();
}
?>
