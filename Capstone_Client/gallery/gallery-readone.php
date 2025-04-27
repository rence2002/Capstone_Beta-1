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
      <li><a href="../reviews/review.php">Reviews</a></li>
      <li><a href="../gallery/gallery.php" class="active">Gallery</a></li>
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
    <div id="single-product-container">
        <div id="single-product-box">
              <!-- Exit Button -->
              <button class="exit-button" onclick="window.location.href='gallery.php'">
                    <i class="fas fa-times"></i>
                </button>
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
                            <!-- Buy Now Button -->
                            <button class="single-product-buy-btn" id="buy-now" disabled>Buy Now</button>
                        <?php else: ?>
                            <!-- Pre-Order Button -->
                            <button class="single-product-preorder-btn" id="pre-order" disabled>Pre-Order</button>
                        <?php endif; ?>
                        <!-- Add to Cart Button -->
                        <button class="single-product-cart-btn" id="add-to-cart" disabled>Add To Cart</button>
                    </div>
                </div>
            </div>
        </div>
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

<!-- Pre-Order Modal -->
<div id="pre-order-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Pre-Order Confirmation</h2>
        <p>Your pre-order request has been submitted and is now under review.</p>
        <div class="modal-buttons">
            <button id="pre-order-ok-button">OK</button>
            <a href="../profile/profile.php#pending-orders" class="btn btn-primary" style="text-decoration: none;">View Pending Orders</a>
        </div>
    </div>
</div>

<!-- Buy Now Modal -->
<div id="buy-now-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Order Confirmation</h2>
        <p>Your order has been submitted and is now under review.</p>
        <div class="modal-buttons">
            <button id="buy-now-ok-button">OK</button>
            <a href="../profile/profile.php#pending-orders" class="btn btn-primary" style="text-decoration: none;">View Pending Orders</a>
        </div>
    </div>
</div>

<!-- Modal for ID Verification Warning -->
<div id="idVerificationModal" class="modal">
    <div class="modal-content">
      
        <h2>Action Restricted</h2>
        <p>Your ID verification status is either <strong>Unverified</strong> or <strong>Invalid</strong>. Please verify your ID to proceed with this action.</p>
    <p style="color:red; font-style: italic; font-size:12px;">Note: Go to your <a class="underline" style="color:red; font-style: none; " href="../profile/profile.php">Profile</a> to check your ID verification status.</p>
    <style>
.underline {
  color: red;
  text-decoration: none;
}

.underline:hover {
  text-decoration: underline;
  color: red; /* Optional: Keep the same color on hover */
}
</style>
 <button id="closeModalButton" class="btn">Close</button>
    </div>
</div>

<style>
    /* Modal Styles */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 400px;
        /* position: relative; */
        text-align: center;
        margin: auto;
        /* transform: translateY(0); */
    }

    .close-modal {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 20px;
        cursor: pointer;
    }

    .btn {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn:hover {
        background-color: #0056b3;
    }

    /* Button Styles */
    .single-product-buy-btn,
    .single-product-preorder-btn,
    .single-product-cart-btn {
        cursor: pointer;
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    .single-product-buy-btn:disabled,
    .single-product-preorder-btn:disabled,
    .single-product-cart-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="../static/Javascript-files/script.js"></script>
    <script src="../static/Javascript-files/readone.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const images = document.querySelectorAll(".product-image");
            const nextBtn = document.querySelector(".next-btn");
            const prevBtn = document.querySelector(".prev-btn");
            let currentIndex = 0;

            // Function to update the active image
            function updateActiveImage(index) {
                images.forEach((img, i) => {
                    img.classList.toggle("active", i === index);
                });
            }

            // Event listener for the next button
            nextBtn.addEventListener("click", function () {
                currentIndex = (currentIndex + 1) % images.length; // Loop back to the first image
                updateActiveImage(currentIndex);
            });

            // Event listener for the previous button
            prevBtn.addEventListener("click", function () {
                currentIndex = (currentIndex - 1 + images.length) % images.length; // Loop back to the last image
                updateActiveImage(currentIndex);
            });

            // Initialize the first image as active
            updateActiveImage(currentIndex);
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            console.log("DOM Content Loaded");
            
            const buyNowButton = document.getElementById("buy-now");
            const preOrderButton = document.getElementById("pre-order");
            const addToCartButton = document.getElementById("add-to-cart");
            const modal = document.getElementById("idVerificationModal");
            const closeModal = document.getElementById("closeModal");
            const closeModalButton = document.getElementById("closeModalButton");
            const modalContent = modal.querySelector(".modal-content p");

            console.log("Buttons and Modal Elements:", {
                buyNowButton,
                preOrderButton,
                addToCartButton,
                modal,
                closeModal,
                closeModalButton,
                modalContent
            });

            // Fetch the user's ID verification status from the server
            let idVerificationStatus = "<?php
                $stmt = $pdo->prepare('SELECT ID_Verification_Status FROM tbl_user_info WHERE User_ID = :user_id');
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                echo $user['ID_Verification_Status'];
            ?>";
            
            console.log("ID Verification Status:", idVerificationStatus);

            // Update the modal message dynamically
            if (modalContent) {
                modalContent.innerHTML = `
                    Your ID verification status is <strong>${idVerificationStatus}</strong>. 
                    Please verify your ID to proceed with this action. 
                    
                `;
            }

            // Function to show the modal
            function showIdVerificationModal() {
                console.log("Showing ID verification modal");
                if (modal) {
                    modal.classList.add('active');
                    console.log("Modal class added");
                }
            }

            // Function to hide the modal
            function hideIdVerificationModal() {
                console.log("Hiding ID verification modal");
                if (modal) {
                    modal.classList.remove('active');
                    console.log("Modal class removed");
                }
            }

            // Show modal automatically if ID is not verified
            if (idVerificationStatus !== "Valid") {
                console.log("ID not verified, showing modal automatically");
                showIdVerificationModal();
            }

            // Add click event listeners to the buttons that exist
            if (buyNowButton) {
                buyNowButton.addEventListener("click", function(e) {
                    console.log("Buy Now button clicked");
                    if (idVerificationStatus !== "Valid") {
                        e.preventDefault();
                        showIdVerificationModal();
                    }
                });
            }
            if (preOrderButton) {
                preOrderButton.addEventListener("click", function(e) {
                    console.log("Pre-order button clicked");
                    if (idVerificationStatus !== "Valid") {
                        e.preventDefault();
                        showIdVerificationModal();
                    }
                });
            }
            if (addToCartButton) {
                addToCartButton.addEventListener("click", function(e) {
                    console.log("Add to Cart button clicked");
                    if (idVerificationStatus !== "Valid") {
                        e.preventDefault();
                        showIdVerificationModal();
                    }
                });
            }

            // Enable buttons only if the ID verification status is "Valid"
            if (idVerificationStatus === "Valid") {
                console.log("Enabling buttons because ID is valid");
                if (buyNowButton) buyNowButton.disabled = false;
                if (preOrderButton) preOrderButton.disabled = false;
                if (addToCartButton) addToCartButton.disabled = false;
            }

            // Close the modal when the close button is clicked
            if (closeModal) {
                closeModal.addEventListener("click", hideIdVerificationModal);
            }

            if (closeModalButton) {
                closeModalButton.addEventListener("click", hideIdVerificationModal);
            }

            // Close modal when clicking outside
            window.addEventListener("click", function (event) {
                if (event.target === modal) {
                    hideIdVerificationModal();
                }
            });
        });
    </script>
</body>

</html>