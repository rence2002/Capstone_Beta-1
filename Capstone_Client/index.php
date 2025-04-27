<?php
session_start();
include("./config/database.php");

// Test the database connection
if (!$pdo) {
    die("Database connection failed.");
}

// Fetch products from the database using PDO
$query = "SELECT Product_Name, GLB_File_URL, ImageURL, Price FROM tbl_prod_info WHERE product_type = 'readymade'";
$stmt = $pdo->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch product images for the about section
$imageQuery = "SELECT ImageURL FROM tbl_prod_info WHERE ImageURL IS NOT NULL AND product_type = 'readymade' LIMIT 5";
$imageStmt = $pdo->prepare($imageQuery);
$imageStmt->execute();
$productImages = $imageStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch reviews from the database
$reviewQuery = "
    SELECT r.Review_Text, r.Rating, r.Review_Date, u.First_Name, u.Last_Name, p.Product_Name
    FROM tbl_reviews r
    JOIN tbl_user_info u ON r.User_ID = u.User_ID
    LEFT JOIN tbl_prod_info p ON r.Product_ID = p.Product_ID
    WHERE r.Rating IN (4, 5) -- Only include reviews with a rating of 4 or 5
    ORDER BY r.Review_Date DESC
    LIMIT 5"; // Limit to 5 reviews
$reviewStmt = $pdo->prepare($reviewQuery);
$reviewStmt->execute();
$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

// Define the base path for 3D files
$base3DPath = "http://localhost/uploads/product/3d/";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="static/css-files/landingpage.css" />
  <title>RM Betis Furniture</title>
  <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
</head>

<style>
  #products {
  padding: 2rem;
  background-color: #f8f8f8;
  text-align: center;
}

.sampleAr {
  font-size: 2rem;
  color: #333;
  margin-bottom: 1.5rem;
}

.products {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  padding: 0 1rem;
}

.product-link {
  text-decoration: none;
  color: inherit;
}

.product {
  background: white;
  border-radius: 15px;
  padding: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.product:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

model-viewer {
  width: 100%;
  height: 200px;
  border-radius: 10px;
  background: #eee;
}

.product h3 {
  font-size: 1.2rem;
  margin-top: 0.75rem;
  color: #222;
}

.product p {
  font-size: 1rem;
  color: #888;
  margin-top: 0.5rem;
}

</style>
<body>
  <header>
    <h1>Welcome to RM Betis Furniture</h1>
    <p>Elegant Furniture. Smart Experience.</p>
  </header>

  <nav>
    <a href="#about">About</a>
    <a href="#tutorial">AR Tutorial</a>
    <a href="#products">Products</a>
    <a href="login/login.php" >Login</a>
    <a href="login/signup.php">Signup</a>
  </nav>

  <!-- About AR Section with Rotating Images -->
  <section id="about">
    <div class="about-images">
      <?php foreach ($productImages as $index => $image): 
        $imageUrls = explode(',', $image['ImageURL']);
        $firstImage = trim($imageUrls[0]);
      ?>
        <img src="<?= $firstImage ?>" alt="Product Image <?= $index + 1 ?>" <?= $index === 0 ? 'class="active"' : '' ?>>
      <?php endforeach; ?>
    </div>

    <div class="about-text">
      <h2>About AR 3D Models and Measurement</h2>
      <p>Our AR (Augmented Reality) 3D models allow you to preview our furniture in your own space through your mobile device. This gives you accurate spatial visualization and measurement before making a purchase.</p>
      <p>RM Betis Furniture also offers customizable furniture to match your personal style and space requirements. Choose your preferred design, materials, and dimensions—we'll craft it just the way you want.</p>
    </div>
  </section>

  <section id="tutorial">
  <div class="tutorial-container">
    <div class="tutorial-text speech-bubble">
      <div class="ARTitle">
        <h2>How to Use Augmented Reality?</h2>
      </div>
      <h2>3D Models AR</h2>
      <ol>
        <li>Open this site on your phone or tablet.</li>
        <li>Go to the Products section below.</li>
        <li>Click the AR icon on any product's 3D viewer.</li>
        <li>Follow the on-screen instructions to place the furniture in your room.</li>
      </ol>
      <br>
      <h1>How to Use the Measurement Tool</h1>
      <p>Follow these steps after pressing the <b>START AR</b> button:</p>
      <ol>
        <li>Move your phone around until a marker appears on your screen.</li>
        <li>Position the marker at the first measurement point.</li>
        <li>Tap the screen to begin the measurement.</li>
        <li>Move the marker to the second measurement point.</li>
        <li>Tap the screen again to complete the measurement.</li>
        <li>The measured distance will be displayed at the center of the line connecting the two points.</li>
        <li>Repeat steps 2 to 5 for additional measurements.</li>
      </ol>
    </div>

    <div class="phones-wrapper">
      <div class="cellphone2">
      <video autoplay muted loop>
    <source src="static/images/Furniture Augmented Reality (1).mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>
</div>
<div class="cellphone">
  <video autoplay muted loop>
    <source src="static/images/Furniture Augmented Reality (1).mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>
      </div>
    </div>
  </div>

  <div class="tutorial-text2 speech-bubble2">
    <div class="ARTitle2">
      <h2>How to Use Augmented Reality?</h2>
    </div>
    <h2>3D Models AR</h2>
    <ol>
      <li>Open this site on your phone or tablet.</li>
      <li>Go to the Products section below.</li>
      <li>Click the AR icon on any product's 3D viewer.</li>
      <li>Follow the on-screen instructions to place the furniture in your room.</li>
    </ol>
    <br>
    <h1>How to Use the Measurement Tool</h1>
    <p>Follow these steps after pressing the <b>START AR</b> button:</p>
    <ol>
      <li>Move your phone around until a marker appears on your screen.</li>
      <li>Position the marker at the first measurement point.</li>
      <li>Tap the screen to begin the measurement.</li>
      <li>Move the marker to the second measurement point.</li>
      <li>Tap the screen again to complete the measurement.</li>
      <li>The measured distance will be displayed at the center of the line connecting the two points.</li>
      <li>Repeat steps 2 to 5 for additional measurements.</li>
    </ol>
  </div>
</section>


  <section id="products">
    <h2 class="sampleAr">Sample 3D Models</h2>
    <div class="products">
      <?php foreach ($products as $product): ?>
        <a href="login/signup.php" class="product-link">
          <div class="product">
            <model-viewer 
              src="<?= $base3DPath . basename($product['GLB_File_URL']) ?>" 
              alt="<?= htmlspecialchars($product['Product_Name']) ?>" 
              ar ar-modes="scene-viewer webxr quick-look" 
              auto-rotate 
              camera-controls>
            </model-viewer>
            <h3><?= htmlspecialchars($product['Product_Name']) ?></h3>
            <p>₱ <?= number_format($product['Price'], 2) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="reviews">
    <h2>Customer Reviews</h2>
    <div class="reviews-container">
      <?php if (!empty($reviews)): ?>
        <?php foreach ($reviews as $review): ?>
          <div class="review-item">
            <h3><?= htmlspecialchars($review['First_Name'] . ' ' . $review['Last_Name']) ?></h3>
            <p><strong>Product:</strong> <?= htmlspecialchars($review['Product_Name'] ?? 'General Review') ?></p>
            <p><strong>Rating:</strong> <?= str_repeat('⭐', $review['Rating']) ?></p>
            <p><?= htmlspecialchars($review['Review_Text']) ?></p>
            <p class="review-date"><?= date('F j, Y', strtotime($review['Review_Date'])) ?></p>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No reviews available at the moment.</p>
      <?php endif; ?>
    </div>
  </section>

  <footer>
    <p>&copy; 2025 RM Betis Furniture. All rights reserved.</p>
  </footer>

  <script>
    let currentImageIndex = 0;
    const images = document.querySelectorAll('.about-images img');
  
    function changeImage() {
      images[currentImageIndex].classList.remove('active');
      currentImageIndex = (currentImageIndex + 1) % images.length;
      images[currentImageIndex].classList.add('active');
    }
  
    setInterval(changeImage, 2000); // Change image every 2 seconds
  </script>
</body>
</html>