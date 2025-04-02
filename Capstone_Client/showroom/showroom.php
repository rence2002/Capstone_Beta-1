<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit();
}

// Include the database connection file
require_once '../config/database.php'; // Corrected path to database.php

// Fetch product data from the database using PDO
try {
    // Prepare the SQL statement
    $stmt = $pdo->prepare("SELECT 
                                Product_ID, 
                                Product_Name, 
                                ImageURL, 
                                GLB_File_URL 
                            FROM tbl_prod_info");

    // Execute the statement
    $stmt->execute();

    // Fetch all results as an associative array
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process the products to extract the first image and generate .usdz paths
    foreach ($products as &$product) {
        // Extract the first image from the comma-separated ImageURL
        $imageUrls = explode(',', $product['ImageURL']);
        $firstImageUrl = trim($imageUrls[0]); // Trim to remove potential whitespace

        $product['image_path'] = $firstImageUrl; // Use the first image
        $product['model_usdz_path'] = str_replace('.glb', '.usdz', $product['GLB_File_URL']); // Generate .usdz path (assumes same name)
    }
    unset($product); // Unset the reference to the last element

} catch (PDOException $e) {
    // Handle database errors
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
  <link rel="stylesheet" href="../static/css-files/Home.css">
  <link rel="stylesheet" href="../static/css-files/Showroom.css">
  <!-- Google Icons Link -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
  <!-- Link Swiper's CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <!-- font awesome cdn link  -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
</head>
<body>

<header>
  <nav class="navbar">
    <a href="../dashboard/home.php" class="logo">
      <img src="../static/images/rm raw png.png" alt="" class="logo">
    </a>
    <ul class="menu-links">
      <li class="dropdown">
        <a href="../dashboard/home.php"  class="">Home</a>
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

    <main class="main">
        <div id="content">
            <div id="mySidenav" class="sidenav">
                <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
                <?php foreach ($products as $product): ?>
                    <a class="ar-object" id="<?php echo $product['Product_ID']; ?>" href="#" onclick="showModel('<?php echo $product['GLB_File_URL']; ?>', '<?php echo $product['model_usdz_path']; ?>')">
                        <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['Product_Name']; ?>" class="click-image">
                    </a>
                <?php endforeach; ?>
                <div>
                    <button class="background-button" onclick="setBackground('../static/images/Designer1.jpg')">Minimalist Serenity</button>
                    <button class="background-button" onclick="setBackground('../static/images/Designer2.jpeg')">Modern Elegance</button>
                    <button class="background-button" onclick="setBackground('../static/images/Designer3.jpeg')">Cozy Retreat</button>
                </div>
                
            </div>

            <span id="open-btn" onclick="openNav()"> open</span>

            <!-- Model Viewer Area -->
            <div class="demo"> 
                <model-viewer id="mainModelViewer" class="three-d" 
                    src="<?php echo isset($products[0]['GLB_File_URL']) ? $products[0]['GLB_File_URL'] : '3d/yellow.glb'; ?>" 
                    ios-src="<?php echo isset($products[0]['model_usdz_path']) ? $products[0]['model_usdz_path'] : '3d/yellow.usdz'; ?>" 
                    ar ar-modes="scene-viewer webxr quick-look" shadow-intensity="1" camera-controls touch-action="pan-y">
                </model-viewer>
            </div>
        </div>
    </main>

    <script src="../static/Javascript-files/Showroom.js"></script>
    <script src="../static/Javascript-files/script.js"></script>
</body>
</html>
