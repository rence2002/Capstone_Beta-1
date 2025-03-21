<?php
session_start();
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit();
}
include("../config/database.php");

// Fetch readymade products
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_prod_info WHERE product_type = 'readymade'");
    $stmt->execute();
    $readymadeProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch pre-order products
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_prod_info WHERE product_type = 'pre_order'");
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
        <a href="home.php" class="logo">
            <img src="../static/images/rm raw png.png" alt="Logo" class="logo">
        </a>
        <ul class="menu-links">
            <li class="dropdown">
                <a href="home.php" class="dropdown-toggle">Home</a>
                <ul class="dropdown-menu">
                    <li><a href="#about-section">About</a></li>
                    <li><a href="#contact-section">Contacts</a></li>
                    <li><a href="#offers-section">Offers</a></li>
                </ul>
            </li>
            <li><a href="Review.php">Reviews</a></li>
            <li><a href="../dashboard/gallery.php" class="active">Gallery</a></li>
            <li><a href="cart.php" class="cart" id="cart">Cart</a></li>
            <li class="dropdown profile-menu">
                <a href="profile.php" class="profile" id="sign_in">Profile</a>
                <ul class="dropdown-menu">
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
        <span id="hamburger-btn" class="material-symbols-outlined">menu</span>
        <span id="close-menu-btn" class="material-symbols-outlined">close</span>
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
            <select id="furniture" name="txtFurnitureType">
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
            <select id="sizes" name="txtStandardSize">
                <option value="" disabled selected>Select one</option>
            </select>
            <div id="sizes-custom-options" style="display: none;">
                <label for="custom-size">Custom Size:</label>
                <input type="text" id="custom-size" name="txtDesiredSize" placeholder="Width x Length x Height">
            </div>
        </div>

        <!-- Color Section -->
        <div class="cus-boxed">
            <label for="color">Color:</label>
            <select id="color" name="txtColor">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="natural_oak">Natural Oak</option>
                <option value="dark_walnut">Dark Walnut</option>
            </select>
            <div id="color-custom-options" style="display: none;">
                <label class="upload-file" for="fileColorImage">Upload Sample:</label>
                <input type="file" id="color-file-upload" name="fileColorImage" accept="image/*">
                <div id="color-image-preview" class="image-preview"></div>
                <label for="color-info">Details:</label>
                <input type="text" id="color-info" name="txtColorInfo" placeholder="Additional info">
            </div>
        </div>

        <!-- Texture Section -->
        <div class="cus-boxed">
            <label for="texture">Texture:</label>
            <select id="texture" name="txtTexture">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="matte">Matte</option>
            </select>
            <div id="texture-custom-options" style="display: none;">
                <label class="upload-file" for="fileTextureImage">Upload Sample:</label>
                <input type="file" id="texture-file-upload" name="fileTextureImage" accept="image/*">
                <div id="texture-image-preview" class="image-preview"></div>
                <label for="texture-info">Details:</label>
                <input type="text" id="texture-info" name="txtTextureInfo" placeholder="Additional info">
            </div>
        </div>

        <!-- Wood Type -->
        <div class="cus-boxed">
            <label for="woods">Wood Type:</label>
            <select id="woods" name="txtWoodType">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="mahogany">Mahogany</option>
            </select>
            <div id="woods-custom-options" style="display: none;">
                <label class="upload-file" for="fileWoodImage">Upload Sample:</label>
                <input type="file" id="wood-file-upload" name="fileWoodImage" accept="image/*">
                <div id="wood-image-preview" class="image-preview"></div>
                <label for="wood-info">Details:</label>
                <input type="text" id="wood-info" name="txtWoodInfo" placeholder="Additional info">
            </div>
        </div>

        <!-- Foam Type -->
        <div class="cus-boxed">
            <label for="foam">Foam Type:</label>
            <select id="foam" name="txtFoamType">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="uratex">Uratex</option>
            </select>
            <div id="foam-custom-options" style="display: none;">
                <label class="upload-file" for="fileFoamImage">Upload Sample:</label>
                <input type="file" id="foam-file-upload" name="fileFoamImage" accept="image/*">
                <div id="foam-image-preview" class="image-preview"></div>
                <label for="foam-info">Details:</label>
                <input type="text" id="foam-info" name="txtFoamInfo" placeholder="Additional info">
            </div>
        </div>

        <!-- Cover Type -->
        <div class="cus-boxed">
            <label for="cover">Cover Type:</label>
            <select id="cover" name="txtCoverType">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="velvet">Velvet</option>
            </select>
            <div id="cover-custom-options" style="display: none;">
                <label class="upload-file" for="fileCoverImage">Upload Sample:</label>
                <input type="file" id="cover-file-upload" name="fileCoverImage" accept="image/*">
                <div id="cover-image-preview" class="image-preview"></div>
                <label for="cover-info">Details:</label>
                <input type="text" id="cover-info" name="txtCoverInfo" placeholder="Additional info">
            </div>
        </div>

        <!-- Design Section -->
        <div class="cus-boxed">
            <label for="design">Design Style:</label>
            <select id="design" name="txtDesign">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="modern">Modern</option>
            </select>
            <div id="design-custom-options" style="display: none;">
                <label class="upload-file" for="fileDesignImage">Upload Design:</label>
                <input type="file" id="design-file-upload" name="fileDesignImage" accept="image/*">
                <div id="design-image-preview" class="image-preview"></div>
                <label for="design-info">Details:</label>
                <input type="text" id="design-info" name="txtDesignInfo" placeholder="Additional info">
            </div>
        </div>

        <!-- Tiles Section -->
        <div class="cus-boxed">
            <label for="tiles">Tile Type:</label>
            <select id="tiles" name="txtTileType">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="marble">Marble</option>
            </select>
            <div id="tiles-custom-options" style="display: none;">
                <label class="upload-file" for="fileTileImage">Upload Sample:</label>
                <input type="file" id="tiles-file-upload" name="fileTileImage" accept="image/*">
                <div id="tiles-image-preview" class="image-preview"></div>
                <label for="tiles-info">Details:</label>
                <input type="text" id="tiles-info" name="txtTileInfo" placeholder="Additional info">
            </div>
        </div>

        <!-- Metal Section -->
        <div class="cus-boxed">
            <label for="metal">Metal Type:</label>
            <select id="metal" name="txtMetalType">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="flat">Flat Bar</option>
            </select>
            <div id="metal-custom-options" style="display: none;">
                <label class="upload-file" for="fileMetalImage">Upload Sample:</label>
                <input type="file" id="metal-file-upload" name="fileMetalImage" accept="image/*">
                <div id="metal-image-preview" class="image-preview"></div>
                <label for="metal-info">Details:</label>
                <input type="text" id="metal-info" name="txtMetalInfo" placeholder="Additional info">
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