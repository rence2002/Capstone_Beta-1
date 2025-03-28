<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include the database connection
include("../config/database.php");

// Get the product ID from the URL
if (isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];
} else {
    // Handle the error, if no product ID is provided
    header("location: gallery.php"); // Redirect to gallery if no ID
    exit;
}

// Fetch the product details from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_prod_info WHERE Product_ID = :productId");
    $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
if (!$product) {
    header("location: gallery.php"); // Redirect to gallery if no product
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product['Product_Name'] ?></title>
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link rel="stylesheet" href="../static/css-files/readone.css">
    
    <!-- Google Icons Link -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <!-- Link Swiper's CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                <ul class="dropdown-menus">
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
    <div id="single-product-container">
        <div id="single-product-box">
            <div id="single-product-box-image" class="image-slider">
                <?php if (!empty($product['ImageURL']) || !empty($product['GLB_File_URL'])) : ?>
                    <?php
                    $imageURLs = explode(',', $product['ImageURL']);
                    $totalItems = count($imageURLs) + (!empty($product['GLB_File_URL']) ? 1 : 0); 
                    $itemCounter = 0;
                    ?>
                    <button class="slider-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
                    <?php foreach ($imageURLs as $key => $imageUrl) : $itemCounter++;?>
                        <?php
                        $imageUrl = trim($imageUrl);
                        if (!empty($imageUrl) && file_exists(dirname(__FILE__) . '/../uploads/product/' . basename($imageUrl))) : ?>
                            <img src="../uploads/product/<?= basename($imageUrl) ?>" alt="<?= $product['Product_Name'] ?>" class="product-image <?= $itemCounter === 1 ? 'active' : '' ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!empty($product['GLB_File_URL']) && file_exists(dirname(__FILE__) . '/../uploads/product/3d/' . basename($product['GLB_File_URL']))) :  $itemCounter++;?>
                        <?php // Construct correct paths
                        $glbFilePath =  '../uploads/product/3d/' . basename($product['GLB_File_URL']); ?>
                        <model-viewer class="three-d-model product-image <?= $itemCounter === 1 ? 'active' : '' ?>" src="<?= $glbFilePath ?>" ar shadow-intensity="1" camera-controls auto-rotate auto-rotate-delay="2000"></model-viewer>
                    <?php endif; ?>
                    <button class="slider-btn next-btn"><i class="fas fa-chevron-right"></i></button>
                <?php else : ?>
                    <p>No image or 3D model available</p>
                <?php endif; ?>
            </div>
            <div id="single-product-box-content">
                <!-- Exit Button -->
                <button class="exit-button" onclick="window.location.href='gallery.php'">
                    <i class="fas fa-times"></i>
                </button>
                <h1 id="single-product-title"><?= $product['Product_Name'] ?></h1>
                <p id="single-product-price">₱ <?= number_format($product['Price'], 2) ?></p>
                <p class="single-product-text">
                    <?= $product['Description'] ?>
                </p>
                <!-- Display the product size here -->
                <p class="single-product-size">Size: <span id="product-size"><?= $product['Sizes'] ?></span></p>
                <p class="single-product-stock">Stock: <span id="stock"><?= $product['Stock'] ?></span></p>
                <!-- Remove the entire size-block-container -->
                <div id="update-cart-container">
                    <div>
                        <button class="single-product-blocks" id="plus-btn">
                            +
                        </button>
                        <span id="quantity">0</span>
                        <button class="single-product-blocks" id="minus-btn">
                            –
                        </button>
                    </div>
                    <div class="button-readone" style="flex: 2">
                        <?php if ($product['Stock'] > 0): ?>
                            <!-- Buy Now Button (Visible when stock is available) -->
                            <button class="single-product-buy-btn" id="buy-now">
                                Buy Now
                            </button>
                        <?php else: ?>
                            <!-- Pre-Order Button (Visible when stock is 0) -->
                            <button class="single-product-preorder-btn" id="pre-order">
                                Pre-Order
                            </button>
                        <?php endif; ?>
                        <!-- Add to Cart Button (Always visible) -->
                        <button class="single-product-cart-btn" id="add-to-cart">
                            Add To Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

    <footer class="footer">
       <!-- Your footer code here -->
    </footer>
    
<!-- Order Confirmation Modal -->
<div id="order-confirmation-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Order Confirmation</h2>
        <p>Your order has been successfully submitted and is now under review.</p>
        <button id="confirm-ok-button">OK</button>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="../static/Javascript-files/script.js"></script>
    <script src="../static/Javascript-files/readone.js">
    
</script>
</body>

</html>
