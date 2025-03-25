<?php
session_start();
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit();
}
include("../config/database.php");

// Fetch readymade products
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_prod_info WHERE product_type = 'readymade' AND Stock != '0'");
    $stmt->execute();
    $readymadeProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch pre-order products (Stock = '0')
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_prod_info WHERE product_type = 'readymade' AND Stock = '0'");
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
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
            <li><a href="../reviews/review.php">Reviews</a></li>
            <li><a href="../gallery/gallery.php" class="active">Gallery</a></li>
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
    <div class="hero-section">
        <div class="content">
            <h1>What Furniture Are You Looking For?</h1>
            <form action="#" class="search-form">
                <input type="text" placeholder="Search a type of furniture" required>
                <button type="submit" class="material-symbols-outlined">search</button>
            </form>
        </div>
    </div>

    <div class="Show_Rooms">
        <a href="Showroom.php" class="showroom">Showroom</a>
        <a href="../static/webxr-measuring-tape-master/measure.html" class="showroom">Measure</a>
    </div>

    <!-- Ready Made Section -->
    <div class="container">
        <h3 class="title">Ready Made Furnitures</h3>
        <div class="products-container">
            <?php foreach ($readymadeProducts as $product): ?>
                <div class="product" data-name="p-<?= $product['Product_ID'] ?>">
                    <?php
                    $imageURLs = explode(',', $product['ImageURL']);
                    $firstImageUrl = trim($imageURLs[0]);
                    $imageFilePath = '../uploads/product/' . basename($firstImageUrl);
                    $glbFilePath = '../uploads/product/3d/' . basename($product['GLB_File_URL']);
                    ?>
                    <?php if (!empty($product['GLB_File_URL']) && file_exists($glbFilePath)): ?>
                        <model-viewer class="image-card three-d" src="<?= $glbFilePath ?>" ar shadow-intensity="1" camera-controls auto-rotate></model-viewer>
                    <?php elseif (!empty($firstImageUrl) && file_exists($imageFilePath)): ?>
                        <img src="<?= $imageFilePath ?>" alt="<?= $product['Product_Name'] ?>" class="image-card">
                    <?php else: ?>
                        <p>No Media Available</p>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($product['Product_Name']) ?></h3>
                    <div class="price">₱ <?= number_format($product['Price'], 2) ?></div>
                    <div class="view-btn-con">
                        <a href="gallery-readone.php?product_id=<?= $product['Product_ID'] ?>" class="view-btn">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Pre-order Section -->
    <?php if (!empty($preorderProducts)): ?>
    <div class="container">
        <h3 class="title">Pre-order Furnitures</h3>
        <div class="products-container">
            <?php foreach ($preorderProducts as $product): ?>
                <div class="product" data-name="p-<?= $product['Product_ID'] ?>">
                    <?php
                    $imageURLs = explode(',', $product['ImageURL']);
                    $firstImageUrl = trim($imageURLs[0]);
                    $imageFilePath = '../uploads/product/' . basename($firstImageUrl);
                    $glbFilePath = '../uploads/product/3d/' . basename($product['GLB_File_URL']);
                    ?>
                    <?php if (!empty($product['GLB_File_URL']) && file_exists($glbFilePath)): ?>
                        <model-viewer class="image-card three-d" src="<?= $glbFilePath ?>" ar shadow-intensity="1" camera-controls auto-rotate></model-viewer>
                    <?php elseif (!empty($firstImageUrl) && file_exists($imageFilePath)): ?>
                        <img src="<?= $imageFilePath ?>" alt="<?= $product['Product_Name'] ?>" class="image-card">
                    <?php else: ?>
                        <p>No Media Available</p>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($product['Product_Name']) ?></h3>
                    <div class="price">₱ <?= number_format($product['Price'], 2) ?></div>
                    <div class="view-btn-con">
                        <a href="gallery-readone.php?product_id=<?= $product['Product_ID'] ?>" class="view-btn">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Customization Section -->
    <div class="container">
        <h3 class="title">Customize Now!</h3>
        <p class="explain-cus">
            Create your personalized furniture! Select options or upload designs. 
            Our team will help bring your vision to life.
        </p>
    </div>

    <div class="customized-container">
        <!-- Furniture Type -->
        <div class="cus-boxed">
            <label for="furniture">Furniture Type:</label>
            <select id="furniture" name="furniture">
                <option value="" disabled selected>Select one</option>
                <option value="chair">Chair</option>
                <option value="table">Table</option>
                <option value="salaset">Sala Set</option>
                <option value="bedframe">Bed Frame</option>
            </select>
            <div id="furniture-custom-options" style="display: none;">
                <label class="upload-file" for="fileFurnitureImage">Upload Design:</label>
                <input type="file" id="fileFurnitureImage" name="fileFurnitureImage" accept="image/*">
                <div id="furniture-image-preview" class="image-preview"></div>
                <label for="furniture-info">Details:</label>
                <input type="text" id="furniture-info" name="txtFurnitureInfo" placeholder="Additional info">
            </div>
        </div>

        <!-- Size Selection -->
        <div class="cus-boxed">
            <label for="sizes">Standard Sizes:</label>
            <select id="sizes" name="sizes">
                <option value="" disabled selected>Select one</option>
            </select>
            <div id="sizes-custom-options" style="display: none;">
                <label for="custom-size">Custom Size:</label>
                <input type="text" id="custom-size" name="sizes-info" placeholder="Width x Length x Height">
            </div>
        </div>

        <!-- Color Section -->
        <div class="cus-boxed">
            <label for="color">Color:</label>
            <select id="color" name="color">
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
            <div id="color-custom-options" style="display: none;">
                <label class="upload-file" for="fileColorImage">Upload Sample:</label>
                <input type="file" id="color-file-upload" name="fileColorImage" accept="image/*">
                <div id="color-image-preview" class="image-preview"></div>
                <label for="color-info">Details:</label>
                <input type="text" id="color-info" name="color-info" placeholder="Additional info">
            </div>
        </div>

        <!-- Texture Section -->
        <div class="cus-boxed">
            <label for="texture">Texture:</label>
            <select id="texture" name="texture">
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
            <div id="texture-custom-options" style="display: none;">
                <label class="upload-file" for="fileTextureImage">Upload Sample:</label>
                <input type="file" id="texture-file-upload" name="fileTextureImage" accept="image/*">
                <div id="texture-image-preview" class="image-preview"></div>
                <label for="texture-info">Details:</label>
                <input type="text" id="texture-info" name="texture-info" placeholder="Additional info">
            </div>
        </div>

        <!-- Wood Type -->
        <div class="cus-boxed">
            <label for="woods">Wood Type:</label>
            <select id="woods" name="wood">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="mahogany">Mahogany</option>
                <option value="tangile">Tangile</option>
                <option value="nara">Nara</option>
                <option value="plywood1">Plywood 1/8</option>
                <option value="plywood2">Plywood 1/4</option>
                <option value="plywood3">Plywood 1/2</option>
                <option value="plywood4">Plywood 3/4</option>
            </select>
            <div id="woods-custom-options" style="display: none;">
                <label class="upload-file" for="fileWoodImage">Upload Sample:</label>
                <input type="file" id="wood-file-upload" name="fileWoodImage" accept="image/*">
                <div id="wood-image-preview" class="image-preview"></div>
                <label for="wood-info">Details:</label>
                <input type="text" id="wood-info" name="wood-info" placeholder="Additional info">
            </div>
        </div>

        <!-- Foam Type -->
        <div class="cus-boxed">
            <label for="foam">Foam Type:</label>
            <select id="foam" name="foam">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="uratex">Uratex</option>
            </select>
            <div id="foam-custom-options" style="display: none;">
                <label class="upload-file" for="fileFoamImage">Upload Sample:</label>
                <input type="file" id="foam-file-upload" name="fileFoamImage" accept="image/*">
                <div id="foam-image-preview" class="image-preview"></div>
                <label for="foam-info">Details:</label>
                <input type="text" id="foam-info" name="foam-info" placeholder="Additional info">
            </div>
        </div>

        <!-- Cover Type -->
        <div class="cus-boxed">
            <label for="cover">Cover Type:</label>
            <select id="cover" name="cover">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="velvet">Velvet</option>
                <option value="linen">Linen Type</option>
                <option value="suede">Suede</option>
                <option value="frenchleather">French Leather</option>
                <option value="germanleather">German Leather</option>
                <option value="koreanleather">Korean Leather</option>
                <option value="italianleather">Italian Leather</option>
            </select>
            <div id="cover-custom-options" style="display: none;">
                <label class="upload-file" for="fileCoverImage">Upload Sample:</label>
                <input type="file" id="cover-file-upload" name="fileCoverImage" accept="image/*">
                <div id="cover-image-preview" class="image-preview"></div>
                <label for="cover-info">Details:</label>
                <input type="text" id="cover-info" name="cover-info" placeholder="Additional info">
            </div>
        </div>

        <!-- Design Section -->
        <div class="cus-boxed">
            <label for="design">Design Style:</label>
            <select id="design" name="design">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
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
            </select>
            <div id="design-custom-options" style="display: none;">
                <label class="upload-file" for="fileDesignImage">Upload Design:</label>
                <input type="file" id="design-file-upload" name="fileDesignImage" accept="image/*">
                <div id="design-image-preview" class="image-preview"></div>
                <label for="design-info">Details:</label>
                <input type="text" id="design-info" name="design-info" placeholder="Additional info">
            </div>
        </div>

        <!-- Tiles Section -->
        <div class="cus-boxed">
            <label for="tiles">Tile Type:</label>
            <select id="tiles" name="tiles">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="marble">Marble</option>
                <option value="porcelain">Porcelain</option>
                <option value="quartz">Quartz</option>
                <option value="granite">Granite</option>
            </select>
            <div id="tiles-custom-options" style="display: none;">
                <label class="upload-file" for="fileTileImage">Upload Sample:</label>
                <input type="file" id="tiles-file-upload" name="fileTileImage" accept="image/*">
                <div id="tiles-image-preview" class="image-preview"></div>
                <label for="tiles-info">Details:</label>
                <input type="text" id="tiles-info" name="tiles-info" placeholder="Additional info">
            </div>
        </div>

        <!-- Metal Section -->
        <div class="cus-boxed">
            <label for="metal">Metal Type:</label>
            <select id="metal" name="metal">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="flat">Flat Bar</option>
                <option value="tubular">Tubular</option>
            </select>
            <div id="metal-custom-options" style="display: none;">
                <label class="upload-file" for="fileMetalImage">Upload Sample:</label>
                <input type="file" id="metal-file-upload" name="fileMetalImage" accept="image/*">
                <div id="metal-image-preview" class="image-preview"></div>
                <label for="metal-info">Details:</label>
                <input type="text" id="metal-info" name="metal-info" placeholder="Additional info">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="cus-actions">
            <button id="print-button">Submit</button>
            <button id="reset-button">Reset</button>
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
            </ul>
        </div>
        <div class="footer-col">
            <h4>Legal</h4>
            <ul class="links">
                <li><a href="#">Customer Agreement</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">GDPR</a></li>
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

<!-- Print Modal -->
<div id="print-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Customization Receipt</h2>
        <div id="modal-preview" class="receipt-content"></div>
        <button id="modal-print-btn">Print</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="../static/Javascript-files/gallery.js"></script>
<script src="../static/Javascript-files/script.js"></script>
<script src="../static/Javascript-files/customization.js"></script>
</body>
</html>