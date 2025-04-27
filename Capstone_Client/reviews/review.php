<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include the database connection
include("../config/database.php");

// Check if product_id is passed via query parameter
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;

// Fetch product details if product_id is provided
$product = null;
if ($product_id) {
    // --- CORRECTION ---
    // Use the existing 'Product_Status' column instead of 'Order_Status'
    $sql = "
        SELECT DISTINCT
            p.Product_ID,
            p.Product_Name
        FROM tbl_prod_info p
        INNER JOIN tbl_purchase_history ph ON p.Product_ID = ph.Product_ID
        WHERE ph.User_ID = :user_id
        AND ph.Product_Status = '100'  -- Corrected column name
        AND ph.Product_Status = '100'  -- You have this condition twice, maybe one was meant for Order_Status? Check your logic.
        AND p.Product_ID = :product_id
    ";
    $stmt = $pdo->prepare($sql);
    // --- END CORRECTION ---

    $stmt->execute(['user_id' => $_SESSION["user_id"], 'product_id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        // It's good practice to provide more specific error messages if possible
        // For example, check if the product exists but wasn't purchased/completed by this user
        $_SESSION['error'] = "You can only review products you have purchased and received.";
        header("location: ../profile/profile.php");
        exit;
    }
}

// Fetch existing reviews with profile pictures
$reviewStmt = $pdo->prepare("
    SELECT r.*, u.First_Name, u.Last_Name, u.PicPath as Profile_Pic, p.Product_Name, p.ImageURL
    FROM tbl_reviews r
    JOIN tbl_user_info u ON r.User_ID = u.User_ID
    JOIN tbl_prod_info p ON r.Product_ID = p.Product_ID
    ORDER BY r.Review_Date DESC
");
$reviewStmt->execute();
$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - RM BETIS FURNITURE</title>
    <link rel="stylesheet" href="../static/css-files/home.css">
    <link rel="stylesheet" href="../static/css-files/review.css">
    <link rel="stylesheet" href="../static/css-files/edit-review.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
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
        <div class="header">
            <h1>Reviews for RM BETIS FURNITURE</h1>
            <select id="sort-reviews">
                <option value="newest">SORT BY: NEWEST FIRST</option>
                <option value="oldest">SORT BY: OLDEST FIRST</option>
            </select>
        </div>

        <?php if ($product): ?>
            <!-- Show review submission form for the selected product -->
            <div class="form-container">
                <h2>Submit Review for <?= htmlspecialchars($product['Product_Name']) ?></h2>
                <form id="review-form" method="POST" action="submit_review.php" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['Product_ID']) ?>">
                    <textarea name="review_text" placeholder="Your Review" required></textarea>
                    <label for="rating">Rating:</label>
                    <select name="rating" id="rating" required>
                        <option value="">Select Rating</option
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                    <label for="review_image">Upload Images:</label>
                    <input type="file" name="review_image[]" accept="image/*" multiple>
                    <button type="submit">Submit Review</button>
                </form>
            </div>
        <?php elseif (!$product_id): ?>
            <!-- Show existing reviews if no product_id is provided -->
            <div class="no-reviews-message">
                <p>To write a review, please go to your <a href="../profile/profile.php">profile</a> and select a completed order.</p>
            </div>
        <?php endif; ?>

      <!-- Reviews List -->
<div id="reviews">
    <?php foreach ($reviews as $review): ?>
        <div class="review-card">
            <div class="review-header">
                <!-- Profile Picture -->
                <?php
                $baseUrl = 'http://localhost/Capstone_Beta/';
                $profilePicPath = !empty($review['Profile_Pic']) ? $baseUrl . $review['Profile_Pic'] : $baseUrl . 'static/images/default-profile.png';
                ?>
                <img src="<?= htmlspecialchars($profilePicPath) ?>" 
                     alt="Profile picture" width="50" height="50"/>
                <div class="info">
                    <h3><?php echo htmlspecialchars($review['First_Name'] . ' ' . $review['Last_Name']); ?></h3>
                    <p class="product">Product: <?php echo htmlspecialchars($review['Product_Name']); ?></p>
                </div>
                <div class="date">
                    <?php echo date('d/m/Y', strtotime($review['Review_Date'])); ?>
                </div>
            </div>
            <div class="review-body">
                <div class="rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= $review['Rating'] ? 'active' : ''; ?>"></i>
                    <?php endfor; ?>
                </div>
                <?php 
                if (!empty($review['PicPath'])) {
                    $images = json_decode($review['PicPath'], true);
                    if (is_array($images)) {
                        foreach ($images as $image) {
                            $fullImagePath = $baseUrl . $image;
                            echo '<img src="' . htmlspecialchars($fullImagePath) . '" alt="Review Image" class="review-image" onclick="openModal(\'' . htmlspecialchars($fullImagePath) . '\')">';
                        }
                    }
                }
                ?>
                <p><?php echo htmlspecialchars($review['Review_Text']); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal for Zooming Image -->
<div id="imageModal" class="modal" onclick="closeModal()">
    <img id="modalImage" class="modal-content">
</div>

</main>
<footer class="footer">
  <div class="footer-row">
    <div class="footer-col">
      <h4>Info</h4>
      <ul class="links">
        <li><a href="home.php">Home</a></li>
        <li><a href="#about-section">About Us</a></li>
        <li><a href="../gallery/gallery.php">Gallery</a></li>
        <li><a href="../reviews/review.php">Reviews</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Legal</h4>
      <ul class="links">
      <li><a href="../agreement/agreement.html">Customer Agreement & Privacy Policy</a></li>
      </ul>
    </div>

    <div class="footer-col">
    <h4>Contact</h4>
    <ul class="links">
      <li><a href="https://mail.google.com/mail/u/0/?fs=1&to=Rmbetisfurniture@yahoo.com&su=Your+Subject+Here&body=Your+message+here.&tf=cm" target="_blank">Email</a></li>
      <li><a href="https://www.facebook.com/BetisFurnitureExtension" target="_blank">Facebook</a></li>
      <li><a href="viber://chat?number=%2B6396596602006">Phone & Viber</a></li>
    </ul>
</div>

    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="../static/Javascript-files/script.js"></script>
<script>
    document.getElementById('sort-reviews').addEventListener('change', function() {
        const reviews = document.getElementById('reviews');
        const reviewCards = Array.from(reviews.getElementsByClassName('review-card'));
        reviewCards.sort((a, b) => {
            const dateA = new Date(a.querySelector('.date').textContent);
            const dateB = new Date(b.querySelector('.date').textContent);
            return this.value === 'newest' ? dateB - dateA : dateA - dateB;
        });
        reviewCards.forEach(card => reviews.appendChild(card));
    });


            // Open the modal and display the clicked image
        function openModal(imageSrc) {
            const modal = document.getElementById("imageModal");
            const modalImage = document.getElementById("modalImage");

            // Set the image source of the modal to the clicked image's source
            modalImage.src = imageSrc;

            // Display the modal
            modal.style.display = "flex";
        }

        // Close the modal when clicked outside of the image
        function closeModal() {
            const modal = document.getElementById("imageModal");
            modal.style.display = "none";
        }

</script>
</body>
</html>
