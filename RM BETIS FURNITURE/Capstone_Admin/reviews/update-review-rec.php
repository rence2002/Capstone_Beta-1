<?php
// Include the database connection
include("../config/database.php");

// Check if the review ID is provided via POST (usually this would be from a form submission)
if (isset($_POST['txtReviewID'])) {
    // Get user input from the web form
    $reviewID = $_POST['txtReviewID'];
    $rating = $_POST['txtRating'];
    $reviewText = $_POST['txtReviewText'];

    // Validate the input (for example, ensure rating is a valid number between 1 and 5)
    if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        // If invalid, redirect back with an error message
        echo "Invalid rating value.";
        exit();
    }

    // Sanitize the review text to prevent XSS attacks
    $reviewText = htmlspecialchars(trim($reviewText));

    // Fetch existing PicPath from the database
    $stmt = $pdo->prepare("SELECT PicPath FROM tbl_reviews WHERE Review_ID = :review_id");
    $stmt->bindParam(':review_id', $reviewID);
    $stmt->execute();
    $review = $stmt->fetch(PDO::FETCH_ASSOC);
    $existingPicPaths = json_decode($review['PicPath'], true) ?? [];

    // Initialize the PicPath variable
    $newPicPaths = $existingPicPaths;

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
            $newFileName = 'review_' . $reviewID . '_' . $uniqueIdentifier . '.' . $fileExtension;
            $filePath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $filePath)) {
                $newPicPaths[$i - 1] = $filePath;
            }
        }
    }

    // Convert the PicPath array to a JSON string
    $newPicPathsJson = json_encode($newPicPaths);

    // Update review in the database
    $updateQuery = "
        UPDATE tbl_reviews 
        SET Rating = :rating, Review_Text = :review_text, PicPath = :pic_path 
        WHERE Review_ID = :review_id
    ";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->bindParam(':rating', $rating);
    $updateStmt->bindParam(':review_text', $reviewText);
    $updateStmt->bindParam(':pic_path', $newPicPathsJson);
    $updateStmt->bindParam(':review_id', $reviewID);

    if ($updateStmt->execute()) {
        // Successfully updated, redirect to the review list page
        header("Location: read-all-reviews-form.php?status=success");
    } else {
        // If there was an error, output a message (optional)
        echo "Failed to update review.";
    }
    exit();
} else {
    // If Review ID is not set in the POST request, show an error
    echo "Review ID is missing!";
    exit();
}
?>
