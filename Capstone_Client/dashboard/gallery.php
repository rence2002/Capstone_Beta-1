<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include the database connection
include("../config/database.php");

// Fetch readymade products from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_prod_info WHERE product_type = 'readymade'");
    $stmt->execute();
    $readymadeProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch pre-order products from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_prod_info WHERE product_type = 'readymade'"); //we fetch the same product
    $stmt->execute();
    $preorderProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link rel="stylesheet" href="../static/css-files/Gallery.css">
   <!-- Google Icons Link -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
  <!-- Link Swiper's CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Icons+Outlined:wght@100;200;300;400;500;600;700;800;900&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Icons+Round:wght@100;200;300;400;500;600;700;800;900&display=swap">

</head>

<body>
<header>
    <nav class="navbar">
      <a href="home.php" class="logo">
        <img src="../static/images/rm raw png.png" alt=""  class="logo">
      </a>
        <ul class="menu-links">
            <li class="dropdown">
                <a href="home.php" class=" dropdown-toggle">Home</a>
                <ul class="dropdown-menu">
                    <li><a href="#about-section">About</a></li>
                    <li><a href="#contact-section">Contacts</a></li>
                    <li><a href="#offers-section">Offers</a></li>
                </ul>
            </li>
            <li><a href="Review.php">Reviews</a></li>
            <li><a href="../dashboard/gallery.php" class="active">Gallery</a></li>
            <li><a href="cart.php" class="cart" id="cart">Cart</a></li>
            <ul class="menu-links">
            <li class="dropdown">
            <a href="profile.php" class="profile" id="sign_in">Profile</a>
                <ul class="dropdown-menu">
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            <span id="close-menu-btn" class="material-symbols-outlined">close</span>
        </ul>
        <span id="hamburger-btn" class="material-symbols-outlined">menu</span>
    </nav>
</header>
    <main>
        <div class="hero-section">
            <div class="content">
                <h1>What Furniture you are looking for?</h1>
                <form action="#" class="search-form">
                    <input type="text" placeholder="Search a type of furniture" required>
                    <button class="material-symbols-outlined" type="submit">search</button>
                </form>
            </div>
        </div>
        <div class="Show_Rooms">
            <a href="Showroom.php" class="showroom">Showroom</a>
            <a href="../static/webxr-measuring-tape-master/measure.html" class="showroom">Measure</a>
        </div>

        <!-- Here will be the products -->
        <div class="container">
            <h3 class="title">READY MADE FURNITURES</h3>
            <div class="products-container">
                <?php if (!empty($readymadeProducts)) : ?>
                    <?php foreach ($readymadeProducts as $product) : ?>
                        <div class="product" data-name="p-<?= $product['Product_ID'] ?>">
                            <?php
                            // Construct correct paths
                            $glbFilePath = 'uploads/product/3d/' . basename($product['GLB_File_URL']);
                            $imageURLs = explode(',', $product['ImageURL']);
                            $firstImageUrl = trim($imageURLs[0]);
                            $imageFilePath = 'uploads/product/' . basename($firstImageUrl);

                            if (!empty($product['GLB_File_URL']) && file_exists(dirname(__FILE__) . '/../' . $glbFilePath)) : ?>
                                <model-viewer class="image-card three-d " src="../<?= $glbFilePath ?>" ar shadow-intensity="1" camera-controls auto-rotate auto-rotate-delay="2000"></model-viewer>
                            <?php elseif (!empty($product['ImageURL']) && file_exists(dirname(__FILE__) . '/../' . $imageFilePath)) : ?>
                                <img src="../<?= $imageFilePath ?>" alt="<?= $product['Product_Name'] ?>" class="image-card">
                            <?php else : ?>
                                <p>No Model or image Available</p>
                            <?php endif; ?>

                            <h3><?= $product['Product_Name'] ?></h3>
                            <!-- <p class="price"><?= $product['Sizes'] ?></p> Display the product size here -->
                            <div class="price">₱ <?= number_format($product['Price'], 2) ?></div>
                            <div class="view-btn-con"><a href="gallery-readone.php?product_id=<?= $product['Product_ID'] ?>" class="view-btn">View</a></div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>No readymade products found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- FOR CUSTOMIZATION -->
        <div class="container">
            <h3 class="title">CUSTOMIZE NOW!</h3>
            <p class="explain-cus">
                In this section, you have the power to build your own personalized furniture! Select your desired items, styles, and features to create a piece that perfectly matches your taste and needs.<br><br>
                If you don’t find exactly what you’re looking for, don’t worry! You can <strong>type in your specific request</strong> or <strong>upload a photo or file</strong> of the design you have in mind. Our team will work closely with you to bring your vision to life and ensure you get exactly what you want.<br><br>
                Feel free to explore, customize, and create your perfect furniture today. <strong>Your dream furniture is just a few clicks away!</strong>
            </p>
        </div>

        <div class="customized-container">
        <form id="customization-form" action="gallery-custom-rec.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            <!-- Furniture Section -->
            <div class="cus-boxed">
                <!-- Furniture Selection -->
                <label for="furniture">Select a kind of furniture:</label>
                <select id="furniture" name="furniture" onchange="updateSizes()" required>
                    <option value="" disabled selected>Select one</option>
                    <option value="chair">Chair</option>
                    <option value="table">Table</option>
                    <option value="salaset">Sala Set</option>
                    <option value="bedframe">Bed Frame</option>
                    <option value="sofa">Sofa</option>
                </select>
                
                <!-- Additional Information -->
                <label for="furniture-info">Additional Information:</label>
                <input type="text" id="furniture-info" name="furniture-info" placeholder="Enter any details here" style="width: 100%; margin-top: 10px; height:30px;" maxlength="100">
            </div>

            <!-- Sizes Section -->
            <div class="cus-boxed">
                <!-- Sizes Selection -->
                <label for="sizes">Select standard sizes:</label>
                <select id="sizes" name="sizes" onchange="toggleCustomField(this, 'sizes-custom-info')">
                    <option value="" disabled selected>Select one</option>
                </select>
                <input type="text" id="sizes-custom-info" name="sizes-custom-info" placeholder="Enter custom size" style="display: none; width: 100%; margin-top: 10px; height:30px;">
                 </div>

            <!-- Color Section -->
            <div class="cus-boxed">
                <!-- Color Selection -->
                <label for="color">Select a color:</label>
                <select id="color" name="color" onchange="toggleCustomElements(this, 'color-custom-info', 'color-file-upload', 'color-image-preview')">
                    <option value="" disabled selected>Select one</option>
                    <option value="custom">Custom</option>
                    <option value="natural_oak">Natural Oak</option>
                    <option value="dark_walnut">Dark Walnut</option>
                    <option value="espresso">Espresso</option>
                    <option value="driftwood_gray">Driftwood Gray</option>
                    <option value="mahogany">Mahogany</option>
                    <option value="cherry_wood">Cherry Wood</option>
                    <option value="black">Black</option>
                    <option value="white">White</option>
                    <option value="charcoal_gray">Charcoal Gray</option>
                    <option value="antique_white">Antique White</option>
                    <option value="weathered_oak">Weathered Oak</option>
                    <option value="honey_pine">Honey Pine</option>
                    <option value="maple">Maple</option>
                    <option value="birch">Birch</option>
                    <option value="teak">Teak</option>
                    <option value="rosewood">Rosewood</option>
                    <option value="ebony">Ebony</option>
                    <option value="gunmetal">Gunmetal</option>
                    <option value="brushed_gold">Brushed Gold</option>
                    <option value="brushed_silver">Brushed Silver</option>
                    
                </select>
                
                <!-- File Upload for Color -->
                <div id="color-custom-options" style="display: none;">
                <label class="upload-file" for="color-file-upload">Upload a file:</label>
                <input type="file" id="color-file-upload" name="color-file-upload" accept="image/*" style="display: none;">
                 <div id="color-image-preview" style="margin-top: 10px;"></div>
                 
                <!-- Additional Information -->
                <label for="color-custom-info">Additional Information:</label>
                <input type="text" id="color-custom-info" name="color-custom-info" placeholder="Enter custom color" style="width: 100%; margin-top: 10px; height:30px;">
                 </div>
                 </div>


             <!-- Texture Section -->
             <div class="cus-boxed">
                <!-- Texture Selection -->
                <label for="texture">Select a texture:</label>
                <select id="texture" name="texture" onchange="toggleCustomElements(this, 'texture-custom-options', 'texture-file-upload', 'texture-image-preview')">
                    <option value="" disabled selected>Select one</option>
                    <option value="custom">Custom</option>
                    <option value="matte">Matte</option>
                    <option value="glossy">Glossy</option>
                    <option value="semi_glossy">Semi Glossy</option>
                    <option value="duco_finish">Duco Finish</option>
                    <option value="marble_finish">Marble Finish</option>
                    <option value="smooth">Smooth</option>
                    <option value="satin_finish">Satin Finish</option>
                    <option value="distressed">Distressed</option>
                    <option value="rustic">Rustic</option>
                    <option value="textured">Textured</option>
                    <option value="brushed">Brushed</option>
                    <option value="weathered">Weathered</option>
                    <option value="wood_grain">Wood Grain</option>
                    <option value="laminated">Laminated</option>
                    <option value="veneer">Veneer</option>
                    <option value="high_gloss">High Gloss</option>
                    <option value="powder_coated">Powder Coated</option>
                    <option value="patina">Patina</option>
                    <option value="raw_unfinished">Raw / Unfinished</option>
                    <option value="polished">Polished</option>
                </select>
                
                <div id="texture-custom-options" style="display: none;">  <!-- Wrapper div -->
                    <!-- File Upload for Texture -->
                    <label class="upload-file" for="texture-file-upload">Upload a file:</label>
                    <input type="file" id="texture-file-upload" name="texture-file-upload" accept="image/*">
                    <div id="texture-image-preview" style="margin-top: 10px;"></div>

                    <!-- Additional Information -->
                    <label for="texture-custom-info">Additional Information:</label>
                    <input type="text" id="texture-custom-info" name="texture-custom-info" placeholder="Enter custom texture" style="width: 100%; margin-top: 10px; height:30px;">
                </div>
            </div>

            <!-- Woods Section -->
            <div class="cus-boxed">
                <label for="woods">Select a wood type:</label>
                <select id="woods" name="woods" onchange="toggleCustomElements(this, 'woods-custom-options', 'wood-file-upload', 'wood-image-preview')">
                    <option value="" disabled selected>Select one</option>
                    <option value="custom">Custom</option>
                    <option value="mahogany">Mahogany</option>
                    <option value="tangile">Tangile</option>
                    <option value="nara">Nara</option>
                </select>

                <div id="woods-custom-options" style="display: none;">
                    <label class="upload-file" for="wood-file-upload">Upload a file:</label>
                    <input type="file" id="wood-file-upload" name="wood-file-upload" accept="image/*">
                    <div id="wood-image-preview" style="margin-top: 10px;"></div>

                    <label for="woods-custom-info">Additional Information:</label>
                    <input type="text" id="woods-custom-info" name="woods-custom-info" placeholder="Enter custom wood type">
                </div>
            </div>

            <!-- Foam Section -->
            <div class="cus-boxed">
                <!-- Foam Selection -->
                <label for="foam">Select a foam:</label>
                <select id="foam" name="foam" onchange="toggleCustomField(this, 'foam-custom-info')">
                    <option value="" disabled selected>Select one</option>
                    <option value="uratex">Uratex</option>
                    <option value="custom">Custom</option>
                </select>
                <input type="text" id="foam-custom-info" name="foam-custom-info" placeholder="Enter custom foam" style="display: none; width: 100%; margin-top: 10px; height:30px;">
                
                <!-- File Upload for Foam -->
                <label class="foam-image-preview" for="foam-file-upload">Upload a file:</label>
                <input type="file" id="foam-file-upload" name="foam-file-upload" accept="image/*">
                <input type="file" id="foam-file-upload" name="foam-file-upload" accept="image/*" style="display: none;">
                 <div id="foam-image-preview" style="margin-top: 10px;"></div>
 
                <!-- Additional Information -->
                <label for="foam-info">Additional Information:</label>
                <input type="text" id="foam-info" name="foam-info" placeholder="Enter any details here" style="width: 100%; margin-top: 10px; height:30px;">
            </div>

            <!-- Cover Section -->
            <div class="cus-boxed">
                <!-- Cover Selection -->
                <label for="cover">Select a cover type:</label>
                <select id="cover" name="cover" onchange="toggleCustomField(this, 'cover-custom-info')">
                    <option value="" disabled selected>Select one</option>
                    <option value="velvet">Velvet</option>
                    <option value="linen">Linen Type</option>
                    <option value="suede">Suede</option>
                    <option value="frenchleather">French Leather</option>
                    <option value="germanleather">German Leather</option>
                    <option value="koreanleather">Korean Leather</option>
                    <option value="italianleather">Italian Leather</option>
                    <option value="custom">Custom</option>
                </select>
                <input type="text" id="cover-custom-info" name="cover-custom-info" placeholder="Enter custom cover" style="display: none; width: 100%; margin-top: 10px; height:30px;">
                
                 <!-- File Upload for Cover -->
                 <label class="cover-image-preview" for="cover-file-upload">Upload a file:</label>
               <input type="file" id="cover-file-upload" name="cover-file-upload" accept="image/*">
                <input type="file" id="cover-file-upload" name="cover-file-upload" accept="image/*" style="display: none;">
                 <div id="cover-image-preview" style="margin-top: 10px;"></div>
 
                <!-- Additional Information -->
                <label for="cover-info">Additional Information:</label>
                <input type="text" id="cover-info" name="cover-info" placeholder="Enter any details here" style="width: 100%; margin-top: 10px; height:30px;">
            </div>

            <!-- Design Section -->
            <div class="cus-boxed">
                <!-- Design Selection -->
                <label for="design">Select a design:</label>
                <select id="design" name="design" onchange="toggleCustomField(this, 'design-custom-info')">
                    <option value="" disabled selected>Select one</option>
                    <option value="modern">Modern</option>
                    <option value="contemporary">Contemporary</option>
                    <option value="rustic">Rustic</option>
                    <option value="industrial">Industrial</option>
                    <option value="scandinavian">Scandinavian</option>
                    <option value="midcentury">Mid-Century Modern</option>
                    <option value="minimalist">Minimalist</option>
                    <option value="traditional">Traditional</option>
                    <option value="bohemian">Bohemian</option>
                    <option value="artdeco">Art Deco</option>
                    <option value="farmhouse">Farmhouse</option>
                    <option value="vintage">Vintage</option>
                    <option value="eclectic">Eclectic</option>
                    <option value="shabbychic">Shabby Chic</option>
                    <option value="asianinspired">Asian Inspired</option>
                    <option value="custom">Custom</option>
                </select>
                <input type="text" id="design-custom-info" name="design-custom-info" placeholder="Enter custom design" style="display: none; width: 100%; margin-top: 10px; height:30px;">
                
                <!-- File Upload for Design -->
                <label class="design-image-preview" for="design-file-upload">Upload a file:</label>
                <input type="file" id="design-file-upload" name="design-file-upload" accept="image/*">
                <input type="file" id="design-file-upload" name="design-file-upload" accept="image/*" style="display: none;">
                 <div id="design-image-preview" style="margin-top: 10px;"></div>
 
                <!-- Additional Information -->
                <label for="design-info">Additional Information:</label>
                <input type="text" id="design-info" name="design-info" placeholder="Enter any details here" style="width: 100%; margin-top: 10px; height:30px;">
            </div>

            <!-- Tile Section -->
            <div class="cus-boxed">
                <!-- Tile Selection -->
                <label for="tile">Select a tile type:</label>
                <select id="tile" name="tile" onchange="toggleCustomField(this, 'tile-custom-info')">
                    <option value="" disabled selected>Select one</option>
                    <option value="marble">Marble</option>
                    <option value="porcelain">Porcelain</option>
                    <option value="quartz">Quartz</option>
                    <option value="granite">Granite</option>
                    <option value="custom">Custom</option>
                </select>
                <input type="text" id="tile-custom-info" name="tile-custom-info" placeholder="Enter custom tile" style="display: none; width: 100%; margin-top: 10px; height:30px;">
                
                <!-- File Upload for Tile -->
                <label class="tile-image-preview" for="tile-file-upload">Upload a file:</label>
                <input type="file" id="tile-file-upload" name="tile-file-upload" accept="image/*">
                <input type="file" id="tile-file-upload" name="tile-file-upload" accept="image/*" style="display: none;">
                 <div id="tile-image-preview" style="margin-top: 10px;"></div>
 
                <!-- Additional Information -->
                <label for="tile-info">Additional Information:</label>
                <input type="text" id="tile-info" name="tile-info" placeholder="Enter any details here" style="width: 100%; margin-top: 10px; height:30px;">
            </div>

            <!-- Metal Section -->
            <div class="cus-boxed">
                <!-- Metal Selection -->
                <label for="metal">Select a metal type:</label>
                <select id="metal" name="metal" onchange="toggleCustomField(this, 'metal-custom-info')">
                    <option value="" disabled selected>Select one</option>
                    <option value="flat">Flat Bar</option>
                    <option value="tubular">Tubular</option>
                    <option value="custom">Custom</option>
                </select>
                <input type="text" id="metal-custom-info" name="metal-custom-info" placeholder="Enter custom metal" style="display: none; width: 100%; margin-top: 10px; height:30px;">
                
                <!-- File Upload for Metal -->
                <label class="metal-image-preview" for="metal-file-upload">Upload a file:</label>
                <input type="file" id="metal-file-upload" name="metal-file-upload" accept="image/*">
                <input type="file" id="metal-file-upload" name="metal-file-upload" accept="image/*" style="display: none;">
                 <div id="metal-image-preview" style="margin-top: 10px;"></div>
 
                <!-- Additional Information -->
                <label for="metal-info">Additional Information:</label>
                <input type="text" id="metal-info" name="metal-info" placeholder="Enter any details here" style="width: 100%; margin-top: 10px; height:30px;">
            </div>

            <!-- Print and Reset Buttons -->
           <div class="cus-boxed">
                 <button type="button" id="print-button">Print</button>
                 <button type="button" id="reset-button" onclick="resetFields()">Reset</button>
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
    <script src="../static/Javascript-files/customization.js"></script>
</body>

</html>
