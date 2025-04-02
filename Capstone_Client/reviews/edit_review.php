<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

include("../config/database.php");

// Fetch the review to be edited or deleted
if (isset($_GET['review_id'])) {
    $review_id = $_GET['review_id'];
    $stmt = $pdo->prepare("
        SELECT * FROM tbl_reviews WHERE Review_ID = :review_id AND User_ID = :user_id
    ");
    $stmt->execute(['review_id' => $review_id, 'user_id' => $_SESSION["user_id"]]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        $_SESSION['error'] = "Review not found or you do not have permission to edit it.";
        header("location: review.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    header("location: review.php");
    exit;
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    try {
        // Delete associated images from the server
        if (!empty($review['PicPath'])) {
            $images = json_decode($review['PicPath'], true);
            if (is_array($images)) {
                foreach ($images as $image) {
                    $file_path = str_replace("../", "C:/xampp/htdocs/", $image); // Convert relative path to absolute path
                    if (file_exists($file_path)) {
                        unlink($file_path); // Delete the file
                    }
                }
            }
        }

        // Delete the review from the database
        $stmt = $pdo->prepare("
            DELETE FROM tbl_reviews WHERE Review_ID = :review_id AND User_ID = :user_id
        ");
        $stmt->execute(['review_id' => $review_id, 'user_id' => $_SESSION["user_id"]]);

        $_SESSION['success'] = "Your review has been deleted successfully!";
        header("location: review.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred while deleting your review. Please try again later.";
        header("location: edit_review.php?review_id=$review_id");
        exit;
    }
}

// Handle form submission for updating the review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_review'])) {
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

    // Validate inputs
    $errors = [];
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
            mkdir($upload_dir, 0755, true);
        }

        foreach ($_FILES['review_image']['tmp_name'] as $key => $tmp_name) {
            $file_name = basename($_FILES['review_image']['name'][$key]);
            $target_file = $upload_dir . uniqid() . "_" . $file_name;

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['review_image']['type'][$key];
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "Invalid file type for image: $file_name. Only JPEG, PNG, and GIF are allowed.";
                continue;
            }

            if (move_uploaded_file($_FILES['review_image']['tmp_name'][$key], $target_file)) {
                $uploaded_images[] = str_replace("C:/xampp/htdocs/", "../", $target_file);
            } else {
                $errors[] = "Failed to upload image: $file_name.";
            }
        }
    }

    // If there are errors, redirect back with error messages
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("location: edit_review.php?review_id=$review_id");
        exit;
    }

    // Update the review in the database
    try {
        $pic_path = !empty($uploaded_images) ? json_encode($uploaded_images) : $review['PicPath'];
        $stmt = $pdo->prepare("
            UPDATE tbl_reviews 
            SET Rating = :rating, Review_Text = :review_text, PicPath = :pic_path
            WHERE Review_ID = :review_id AND User_ID = :user_id
        ");
        $stmt->execute([
            'rating' => $rating,
            'review_text' => $review_text,
            'pic_path' => $pic_path,
            'review_id' => $review_id,
            'user_id' => $_SESSION["user_id"]
        ]);

        $_SESSION['success'] = "Your review has been updated successfully!";
        header("location: review.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred while updating your review. Please try again later.";
        header("location: edit_review.php?review_id=$review_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review - RM BETIS FURNITURE</title>
    <link rel="stylesheet" href="../static/css-files/home.css">
    <link rel="stylesheet" href="../static/css-files/review.css">
    <link rel="stylesheet" href="../static/css-files/edit-review.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <style>
        /* Styling for the Delete Button */
        .delete-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ff4d4d; /* Red color */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .delete-button:hover {
            background-color: #cc0000; /* Darker red on hover */
        }
    </style>
</head>
<body>
<header>
  <nav class="navbar">
    <a href="../dashboard/home.php" class="logo">
      <img src="../static/images/rm raw png.png" alt="" class="logo">
    </a>
    <ul class="menu-links">
      <li class="dropdown">
        <a href="../dashboard/home.php">Home</a>
        <ul class="dropdown-menus">
          <li><a href="#about-section">About</a></li>
          <li><a href="#contact-section">Contacts</a></li>
          <li><a href="#offers-section">Offers</a></li>
        </ul>
      </li>
      <li><a href="../reviews/review.php" class="active">Reviews</a></li>
      <li><a href="../gallery/gallery.php" >Gallery</a></li>
      <li><a href="../cart/cart.php" class="cart" id="cart">Cart</a></li>
      <li class="dropdown">
        <a href="../profile/profile.php" class="profile" id="sign_in">Profile</a>
        <ul class="dropdown-menus">
          <li><a href="../profile/profile.php">Profile</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>

       
      </li>
      <span id="close-menu-btn" class="material-symbols-outlined">close</span>
    </ul>
   
    <span id="hamburger-btn" class="material-symbols-outlined">menu</span>
  </nav>
</header>
<main>
    <div class="container">
        <h1>Edit Your Review</h1>

        <!-- Edit Review Form -->
        <div class="form-container">
        <form id="edit-review-form" method="POST" action="edit_review.php?review_id=<?php echo htmlspecialchars($review['Review_ID']); ?>" enctype="multipart/form-data">
            <textarea name="review_text" placeholder="Your Review" required><?php echo htmlspecialchars($review['Review_Text']); ?></textarea>
            <select name="rating" required>
                <option value="">Select Rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $review['Rating'] == $i ? 'selected' : ''; ?>>
                        <?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?>
                    </option>
                <?php endfor; ?>
            </select>
            <input type="file" name="review_image[]" accept="image/*" multiple>
            <button type="submit">Update Review</button>
        </form>
        </div>

        <!-- Delete Review Button -->
        <!-- <form id="delete-review-form" method="POST" action="edit_review.php?review_id=<?php echo htmlspecialchars($review['Review_ID']); ?>" style="margin-top: 20px;">
            <button type="submit" name="delete_review" class="delete-button">Delete Review</button>
        </form> -->
    </div>
</main>
</body>
</html>