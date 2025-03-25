<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

include("../config/database.php");

// Fetch completed products for the current user
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        p.Product_ID,
        p.Product_Name,
        p.ImageURL as Product_Image,
        pr.Order_Type,
        pr.Order_Status,
        pr.Product_Status
    FROM tbl_prod_info p
    INNER JOIN tbl_progress pr ON p.Product_ID = pr.Product_ID
    WHERE pr.User_ID = :user_id 
    AND pr.Order_Status = '100'
    AND pr.Product_Status = '100'
");

$stmt->execute(['user_id' => $_SESSION["user_id"]]);
$completedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing reviews
$reviewStmt = $pdo->prepare("
    SELECT r.*, u.First_Name, u.Last_Name, p.Product_Name, p.ImageURL
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
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link rel="stylesheet" href="../static/css-files/Review.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
</head>

<body>
<header>
<nav class="navbar">
      <a href="../dashboard/home.php" class="logo">
        <img src="../static/images/rm raw png.png" alt=""  class="logo">
      </a>
        <ul class="menu-links">
            <li class="dropdown">
                <a href="../dashboard/home.php" class="">Home</a>
                <ul class="dropdown-menus">
                    <li><a href="#about-section">About</a></li>
                    <li><a href="#contact-section">Contacts</a></li>
                    <li><a href="#offers-section">Offers</a></li>
                </ul>
            </li>
            <li><a href="../reviews/review.php" class="active">Reviews</a></li>
            <li><a href="../gallery/gallery.php">Gallery</a></li>
            <li><a href="../cart/cart.php" class="cart" id="cart">Cart</a></li>
            <ul class="menu-links">
            <li class="dropdown">
            <a href="../profile/profile.php" class="profile" id="sign_in">Profile</a>
                <ul class="dropdown-menus">
                    <li><a href="../profile/profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
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

            <?php if (!empty($completedProducts)): ?>
                <!-- Review Form -->
                <div class="form-container">
                    <h2>Submit a Review</h2>
                    <form id="review-form" method="POST" action="submit_review.php" enctype="multipart/form-data">
                        <select name="product_id" required>
                            <option value="">Select Product</option>
                            <?php foreach ($completedProducts as $product): ?>
                                <option value="<?php echo htmlspecialchars($product['Product_ID']); ?>">
                                    <?php echo htmlspecialchars($product['Product_Name']); ?> 
                                    (<?php echo htmlspecialchars($product['Order_Type']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <textarea name="review_text" placeholder="Your Review" required></textarea>
                        <select name="rating" required>
                            <option value="">Select Rating</option>
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?></option>
                            <?php endfor; ?>
                        </select>
                        <input type="file" name="review_image" accept="image/*" multiple>
                        <button type="submit">Submit Review</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Reviews List -->
            <div id="reviews">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <img src="../static/images/default-profile.png" alt="Profile picture" width="50" height="50"/>
                            <div class="info">
                                <h3><?php echo htmlspecialchars($review['First_Name'] . ' ' . $review['Last_Name']); ?></h3>
                                <p>Product: <?php echo htmlspecialchars($review['Product_Name']); ?></p>
                            </div>
                            <div class="date">
                                <?php echo date('d/m/Y', strtotime($review['Review_Date'])); ?>
                            </div>
                        </div>
                        <div class="review-body">
                            <div class="rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $review['Rating'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <?php 
                            if (!empty($review['PicPath'])) {
                                $images = json_decode($review['PicPath'], true);
                                if (is_array($images)) {
                                    foreach ($images as $image) {
                                        echo '<img src="' . htmlspecialchars($image) . '" alt="Review Image" class="review-image">';
                                    }
                                }
                            }
                            ?>
                            <p><?php echo htmlspecialchars($review['Review_Text']); ?></p>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-row">
            <div class="footer-col">
                <h4>Info</h4>
                <ul class="links">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="Gallery.php">Gallery</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Explore</h4>
                <ul class="links">
                    <li><a href="#">Free Designs</a></li>
                    <li><a href="#">Latest Designs</a></li>
                    <li><a href="#">Themes</a></li>
                    <li><a href="#">Popular Designs</a></li>
                    <li><a href="#">Art Skills</a></li>
                    <li><a href="#">New Uploads</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Legal</h4>
                <ul class="links">
                    <li><a href="#">Customer Agreement</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">GDPR</a></li>
                    <li><a href="#">Security</a></li>
                    <li><a href="#">Testimonials</a></li>
                    <li><a href="#">Media Kit</a></li>
                </ul>
            </div>

            <div class="icons">
                <i class="fa-brands fa-facebook-f"></i>
                <i class="fa-brands fa-twitter"></i>
                <i class="fa-brands fa-linkedin"></i>
                <i class="fa-brands fa-github"></i>
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
    </script>
</body>
</html>