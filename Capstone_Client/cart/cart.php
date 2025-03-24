<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}
include("../config/database.php");

$stmt = $pdo->prepare("
    SELECT c.*, p.Product_Name, p.ImageURL, p.Price
    FROM tbl_cart c
    JOIN tbl_prod_info p ON c.Product_ID = p.Product_ID
    WHERE c.User_ID = :user_id
    ORDER BY c.Date_Added DESC
");
$stmt->execute(['user_id' => $_SESSION["user_id"]]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = array_sum(array_column($cartItems, 'Total_Price'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - RM BETIS FURNITURE</title>
    <link rel="stylesheet" href="../static/css-files/nav.css">
    <link rel="stylesheet" href="../static/css-files/cart.css">
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>

<body>
<header>
<nav class="navbar">
      <a href="home.php" class="logo">
        <img src="../static/images/rm raw png.png" alt=""  class="logo">
      </a>
        <ul class="menu-links">
            <li class="dropdown">
                <a href="home.php" class="active dropdown-toggle">Home</a>
                <ul class="dropdown-menu">
                    <li><a href="#about-section">About</a></li>
                    <li><a href="#contact-section">Contacts</a></li>
                    <li><a href="#offers-section">Offers</a></li>
                </ul>
            </li>
            <li><a href="../review/review.php">Reviews</a></li>
            <li><a href="../gallery/gallery.php">Gallery</a></li>
            <li><a href="../cart/cart.php" class="cart" id="cart">Cart</a></li>
            <ul class="menu-links">
            <li class="dropdown">
            <a href="profile.php" class="profile" id="sign_in">Profile</a>
                <ul class="dropdown-menu">
                    <li><a href="../profile/profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            <span id="close-menu-btn" class="material-symbols-outlined">close</span>
        </ul>
        <span id="hamburger-btn" class="material-symbols-outlined">menu</span>
    </nav>
</header>

<main>
    <div id="cart-container">
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Browse our gallery to add some items!</p>
                <a href="gallery.php" class="browse-btn">Browse Gallery</a>
            </div>
        <?php else: ?>
            <div id="cart-items">
                <?php foreach ($cartItems as $item): 
                    $images = explode(',', $item['ImageURL']);
                    $firstImage = isset($images[0]) ? trim($images[0]) : '../static/images/placeholder.jpg';
                ?>
                    <div class="cart-item" data-id="<?= $item['Cart_ID'] ?>">
                        <div class="row">
                            <div class="col-md-7 center-item">
                                <img src="<?= htmlspecialchars($firstImage) ?>" 
                                     alt="<?= htmlspecialchars($item['Product_Name']) ?>"
                                     class="cart-product-image">
                                <h5><?= htmlspecialchars($item['Product_Name']) ?> 
                                    (₱<?= number_format($item['Price'], 2) ?>)</h5>
                            </div>
                            <div class="col-md-5 center-item">
                                <div class="input-group number-spinner">
                                    <button class="btn btn-default qty-btn minus"><i class="fas fa-minus"></i></button>
                                    <input type="number" class="form-control text-center quantity" 
                                           value="<?= $item['Quantity'] ?>" min="1">
                                    <button class="btn btn-default qty-btn plus"><i class="fas fa-plus"></i></button>
                                </div>
                                <h5>₱<span class="item-total"><?= number_format($item['Price'] * $item['Quantity'], 2) ?></span></h5>
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
                <button id="checkout" class="btn btn-primary checkout-btn" onclick="proceedToCheckout()">Check out</button>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">
    <!-- Keep your existing footer content -->
</footer>

<script src="../static/Javascript-files/script.js"></script>
<script> src="../static/Javascript-files/cart.js"</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>