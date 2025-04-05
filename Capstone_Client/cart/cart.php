<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}
include("../config/database.php");

// Use LEFT JOIN to show cart items even if product is deleted
$stmt = $pdo->prepare("
    SELECT 
        c.Cart_ID,
        c.Product_ID,
        c.Quantity,
        c.Price,
        c.Total_Price,
        c.Order_Type,
        c.Date_Added,
        p.Product_Name,
        p.ImageURL AS product_image
    FROM tbl_cart c
    LEFT JOIN tbl_prod_info p 
        ON c.Product_ID = p.Product_ID
    WHERE c.User_ID = :user_id
    ORDER BY c.Date_Added DESC
");
$stmt->execute(['user_id' => $_SESSION["user_id"]]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total from cart items
$total = array_sum(array_column($cartItems, 'Total_Price'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - RM BETIS FURNITURE</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../static/Javascript-files/script.js"></script>
    <script src="../static/Javascript-files/cart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../static/css-files/nav.css">
    <link rel="stylesheet" href="../static/css-files/cart.css">
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
</head>
<body>
<header>
  <nav class="navbar">
    <a href="../dashboard/home.php" class="logo">
      <img src="../static/images/rm raw png.png" alt="" class="logo">
    </a>
    <ul class="menu-links no-bootstrap">
      <li class="dropdown">
        <a href="../dashboard/home.php">Home</a>
        <ul class="dropdown-menus">
          <li><a href="#about-section">About</a></li>
          <li><a href="#contact-section">Contacts</a></li>
          <li><a href="#offers-section">Offers</a></li>
        </ul>
      </li>
      <li><a href="../reviews/review.php">Reviews</a></li>
      <li><a href="../gallery/gallery.php" class="">Gallery</a></li>
      <li><a href="../cart/cart.php" class="cart activecon" id="cart">Cart</a></li>
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
    <div id="cart-container">
    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <!-- <i class="fas fa-shopping-cart fa-4x text-muted"></i> -->
            <h2 class="mt-3">Your cart is empty</h2>
            <p class="text-muted">You haven't added any items to your cart yet.</p>
            <a href="../gallery/gallery.php" class="btn btn-primary browse-btn">
                <i class="fas fa-store me-2"></i> Browse Products
            </a>
        </div>
    <?php else: ?>
            <div id="cart-items">
                <?php foreach ($cartItems as $item): 
                    // Handle missing product images
                    $images = $item['product_image'] 
                        ? explode(',', $item['product_image']) 
                        : [];
                    $firstImage = !empty($images[0]) 
                        ? trim($images[0]) 
                        : '../static/images/placeholder.jpg';
                    
                    // Use cart price if product is missing
                    $displayPrice = $item['Price'];
                ?>
                    <div class="cart-item" 
                         data-id="<?= $item['Cart_ID'] ?>" 
                         data-price="<?= $displayPrice ?>">
                        <div class="row">
                            <div class="col-md-7 center-item">
                                <img src="<?= htmlspecialchars($firstImage) ?>" 
                                     alt="<?= htmlspecialchars($item['Product_Name'] ?? 'Product') ?>"
                                     class="cart-product-image">
                                <h5>
                                    <?= htmlspecialchars($item['Product_Name'] ?? 'Unnamed Product') ?> 
                                    (₱<?= number_format($displayPrice, 2) ?>)
                                </h5>
                            </div>
                            <div class="col-md-5 center-item mt-4">
                                <div class="input-group number-spinner">
                                    <button class="btn btn-default qty-btn minus">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           class="form-control text-center quantity" 
                                           value="<?= $item['Quantity'] ?>" 
                                           min="1">
                                    <button class="btn btn-default qty-btn plus">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <h5 id="amountcart">
                                    ₱<span class="item-total">
                                        <?= number_format($displayPrice * $item['Quantity'], 2) ?>
                                    </span>
                                </h5>
                                <button class="btn btn-danger remove-btn">
                                    <i class="fas fa-trash-alt"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="cart-footer">
                <div class="cart-totals">
                    <p>Subtotal: <span id="subtotal">₱<?= number_format($total, 2) ?></span></p>
                    <p>Tax (8%): <span id="tax">₱<?= number_format($total * 0.08, 2) ?></span></p>
                    <p>Total: <span id="total">₱<?= number_format($total * 1.08, 2) ?></span></p>
                </div>
                <button id="checkout" 
                        class="btn btn-primary checkout-btn" 
                        onclick="proceedToCheckout()">
                    Check out
                </button>
            </div>
        <?php endif; ?>
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
</body>
</html>