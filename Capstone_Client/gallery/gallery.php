<?php
session_start();
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit();
}
include("../config/database.php");

// Initialize search term
$searchTerm = "";

// Check if a search term is submitted
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = "%" . $_GET['search'] . "%"; // Add wildcards for LIKE clause
}

// Fetch readymade products (with search filter)
try {
    $sql = "SELECT * FROM tbl_prod_info WHERE product_type = 'readymade' AND Stock != '0'";
    if (!empty($searchTerm)) {
        $sql .= " AND Product_Name LIKE :searchTerm";
    }
    $stmt = $pdo->prepare($sql);
    if (!empty($searchTerm)) {
        $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    }
    $stmt->execute();
    $readymadeProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch pre-order products (with search filter)
try {
    $sql = "SELECT * FROM tbl_prod_info WHERE product_type = 'readymade' AND Stock = '0'";
    if (!empty($searchTerm)) {
        $sql .= " AND Product_Name LIKE :searchTerm";
    }
    $stmt = $pdo->prepare($sql);
    if (!empty($searchTerm)) {
        $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    }
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
    <link rel="stylesheet" href="../static/css-files/grid.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <style>
        .search-form {
            position: relative;
        }

        .clear-search {
            position: absolute;
            right: 40px; /* Adjust as needed */
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            display: none; /* Hidden by default */
            color: #888;
            font-size: 1.2em;
        }

        .clear-search.show {
            display: block; /* Show when there's a search term */
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 90%;
            max-width: 400px;
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
    </style>
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
    <div class="hero-section">
        <div class="content">
            <h1>What Furniture Are You Looking For?</h1>
            <form action="" method="GET" class="search-form" id="search-form">
                <input type="text" name="search" id="search-input" placeholder="Search a type of furniture" required value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <span class="clear-search" id="clear-search">&times;</span>
                <button type="submit" class="material-symbols-outlined">search</button>
            </form>
        </div>
    </div>

    <div class="Show_Rooms">
        <a href="../showroom/showroom.php" class="showroom">Showroom</a>
        <a href="../../keme/index.html" class="showroom">Measure</a>




    </div>

    <!-- Ready Made Section -->
    <div class="container" id="readymade-products-container">
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
    <div class="container" id="preorder-products-container">
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
            <p class="dropdown-description" id="furniture-description">Select the type of furniture you want to customize. You can also upload a custom design.</p>
            <select id="furniture" name="furniture">
                <option value="" disabled selected>Select one</option>
                <option value="chair">Chair</option>
                <option value="table">Table</option>
                <option value="salaset">Sala Set</option>
                <option value="bedframe">Bed Frame</option>
                <option value="custom">Custom</option>
            </select>
            <div id="furniture-custom-options" style="display: none;">
                <label class="upload-file" for="fileFurnitureImage">Upload Design:</label>
                <input type="file" id="fileFurnitureImage" name="fileFurnitureImage" accept="image/*">
                <div id="furniture-image-preview" class="image-preview"></div>
                <label for="furniture-info">Details:</label>
                <input type="text" id="furniture-info" name="furniture-info" placeholder="Additional info">
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const furnitureSelect = document.getElementById("furniture");
                const description = document.getElementById("furniture-description");

                // Define descriptions for each furniture type
                const descriptions = {
                    chair: "Customize your chair with unique designs and materials.",
                    table: "Choose the perfect table size, material, and finish.",
                    salaset: "Create a sala set that fits your living room style.",
                    bedframe: "Design a bed frame that matches your bedroom aesthetic.",
                    custom: "Upload your custom design and bring your vision to life.",
                    default: "Select the type of furniture you want to customize. You can also upload a custom design."
                };

                // Update description based on selected furniture type
                furnitureSelect.addEventListener("change", function () {
                    const selectedValue = furnitureSelect.value;
                    description.textContent = descriptions[selectedValue] || descriptions.default;
                });
            });
        </script>

        <!-- Sizes -->
        <div class="cus-boxed">
            <label for="sizes">Sizes:</label>
            <p class="dropdown-description">Select the size for your furniture. You can also upload a custom design.</p>
            <select id="sizes" name="sizes">
                <option value="" disabled selected>Select one</option>
            </select>
            <div id="sizes-custom-options" style="display: none;">
                <label for="sizes-info">Sizes Additional Info:</label>
                <input type="text" id="sizes-info" name="sizes-info" placeholder="Enter additional info">
            </div>
        </div>

        <!-- Color -->
        <div class="cus-boxed">
            <label for="color">Color:</label>
            <p class="dropdown-description" id="color-description">Select the color for your furniture. You can also upload a custom design.</p>
            <select id="color" name="color">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="Teak">Teak</option>
                <option value="Weathered Oak">Weathered Oak</option>
                <option value="Antique White">Antique White</option>
                <option value="Black">Black</option>
                <option value="Driftwood Gray">Driftwood Gray</option>
            </select>
            <div id="color-custom-options" style="display: none;">
                <label for="color-info">Color Additional Info:</label>
                <input type="text" id="color-info" name="color-info" placeholder="Enter additional info">
                <label for="fileColorImage">Upload Color Image:</label>
                <input type="file" id="fileColorImage" name="fileColorImage" accept="image/*">
                <div id="color-image-preview" class="image-preview"></div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const colorSelect = document.getElementById("color");
                const colorDescription = document.getElementById("color-description");

                // Define descriptions for each color
                const colorDescriptions = {
                    custom: "Upload your custom color design and bring your vision to life.",
                    Teak: "Teak is a warm, golden-brown color perfect for classic furniture.",
                    "Weathered Oak": "Weathered Oak gives a rustic and aged look to your furniture.",
                    "Antique White": "Antique White adds a vintage and elegant touch.",
                    Black: "Black is a bold and modern choice for any furniture style.",
                    "Driftwood Gray": "Driftwood Gray offers a neutral and contemporary finish.",
                    default: "Select the color for your furniture. You can also upload a custom design."
                };

                // Update description based on selected color
                colorSelect.addEventListener("change", function () {
                    const selectedColor = colorSelect.value;
                    colorDescription.textContent = colorDescriptions[selectedColor] || colorDescriptions.default;
                });
            });
        </script>

        <!-- Texture -->
<div class="cus-boxed">
    <label for="texture">Texture:</label>
    <p class="dropdown-description" id="texture-description">Select the texture for your furniture. You can also upload a custom design.</p>
    <select id="texture" name="texture">
        <option value="" disabled selected>Select one</option>
        <option value="custom">Custom</option>
        <option value="High Gloss">High Gloss</option>
        <option value="Brushed">Brushed</option>
        <option value="Textured">Textured</option>
        <option value="Smooth">Smooth</option>
        <option value="Semi Glossy">Semi Glossy</option>
        <option value="Duco Finish">Duco Finish</option>
        <option value="Rustic">Rustic</option>
    </select>
    <div id="texture-custom-options" style="display: none;">
        <label for="texture-info">Texture Additional Info:</label>
        <input type="text" id="texture-info" name="texture-info" placeholder="Enter additional info">
        <label for="fileTextureImage">Upload Texture Image:</label>
        <input type="file" id="fileTextureImage" name="fileTextureImage" accept="image/*">
        <div id="texture-image-preview" class="image-preview"></div>
    </div>
</div>

<!-- Wood -->
<div class="cus-boxed">
    <label for="woods">Wood:</label>
    <p class="dropdown-description" id="wood-description">Select the type of wood you want for your furniture. You can also upload a custom design.</p>
    <select id="woods" name="wood">
        <option value="" disabled selected>Select one</option>
        <option value="custom">Custom</option>
        <option value="Nara">Nara</option>
        <option value="Plywood 1/4">Plywood 1/4</option>
        <option value="Plywood 1/8">Plywood 1/8</option>
    </select>
    <div id="woods-custom-options" style="display: none;">
        <label for="wood-info">Wood Additional Info:</label>
        <input type="text" id="wood-info" name="wood-info" placeholder="Enter additional info">
        <label for="fileWoodImage">Upload Wood Image:</label>
        <input type="file" id="fileWoodImage" name="fileWoodImage" accept="image/*">
        <div id="wood-image-preview" class="image-preview"></div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Texture Logic
        const textureSelect = document.getElementById("texture");
        const textureDescription = document.getElementById("texture-description");

        const textureDescriptions = {
            custom: "Upload your custom texture design and bring your vision to life.",
            "High Gloss": "High Gloss provides a shiny and reflective finish.",
            Brushed: "Brushed texture adds a subtle, linear pattern.",
            Textured: "Textured finish adds depth and character to your furniture.",
            Smooth: "Smooth finish offers a sleek and polished look.",
            "Semi Glossy": "Semi Glossy provides a balanced shine.",
            "Duco Finish": "Duco Finish offers a durable and vibrant coating.",
            Rustic: "Rustic texture gives a natural and aged appearance.",
            default: "Select the texture for your furniture. You can also upload a custom design."
        };

        textureSelect.addEventListener("change", function () {
            const selectedTexture = textureSelect.value;
            textureDescription.textContent = textureDescriptions[selectedTexture] || textureDescriptions.default;
        });

        // Wood Logic
        const woodSelect = document.getElementById("woods");
        const woodDescription = document.getElementById("wood-description");

        const woodDescriptions = {
            custom: "Upload your custom wood design and bring your vision to life.",
            Nara: "Nara wood is known for its durability and elegant grain.",
            "Plywood 1/4": "Plywood 1/4 is lightweight and versatile for various designs.",
            "Plywood 1/8": "Plywood 1/8 is thin and ideal for intricate details.",
            default: "Select the type of wood you want for your furniture. You can also upload a custom design."
        };

        woodSelect.addEventListener("change", function () {
            const selectedWood = woodSelect.value;
            woodDescription.textContent = woodDescriptions[selectedWood] || woodDescriptions.default;
        });
    });
</script>

        <!-- Foam -->
        <div class="cus-boxed">
            <label for="foam">Foam:</label>
            <p class="dropdown-description" id="foam-description">Choose the type of foam for your furniture. You can also provide additional details or upload a custom design.</p>
            <select id="foam" name="foam">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="Uratex">Uratex</option>
            </select>
            <div id="foam-custom-options" style="display: none;">
                <label for="foam-info">Foam Additional Info:</label>
                <input type="text" id="foam-info" name="foam-info" placeholder="Enter additional info">
                <label for="fileFoamImage">Upload Foam Image:</label>
                <input type="file" id="fileFoamImage" name="fileFoamImage" accept="image/*">
                <div id="foam-image-preview" class="image-preview"></div>
            </div>
        </div>

        <!-- Cover -->
        <div class="cus-boxed">
            <label for="cover">Cover:</label>
            <p class="dropdown-description" id="cover-description">Select the type of cover for your furniture. You can also upload a custom design.</p>
            <select id="cover" name="cover">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="German Leather">German Leather</option>
                <option value="Korean Leather">Korean Leather</option>
                <option value="Velvet">Velvet</option>
                <option value="Italian Leather">Italian Leather</option>
                <option value="Linen Type">Linen Type</option>
            </select>
            <div id="cover-custom-options" style="display: none;">
                <label for="cover-info">Cover Additional Info:</label>
                <input type="text" id="cover-info" name="cover-info" placeholder="Enter additional info">
                <label for="fileCoverImage">Upload Cover Image:</label>
                <input type="file" id="fileCoverImage" name="fileCoverImage" accept="image/*">
                <div id="cover-image-preview" class="image-preview"></div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Foam Logic
                const foamSelect = document.getElementById("foam");
                const foamDescription = document.getElementById("foam-description");

                const foamDescriptions = {
                    custom: "Upload your custom foam design and bring your vision to life.",
                    Uratex: "Uratex foam provides premium comfort and durability.",
                    default: "Choose the type of foam for your furniture. You can also provide additional details or upload a custom design."
                };

                foamSelect.addEventListener("change", function () {
                    const selectedFoam = foamSelect.value;
                    foamDescription.textContent = foamDescriptions[selectedFoam] || foamDescriptions.default;
                });

                // Cover Logic
                const coverSelect = document.getElementById("cover");
                const coverDescription = document.getElementById("cover-description");

                const coverDescriptions = {
                    custom: "Upload your custom cover design and bring your vision to life.",
                    "German Leather": "German Leather offers a luxurious and durable finish.",
                    "Korean Leather": "Korean Leather provides a sleek and modern look.",
                    Velvet: "Velvet adds a soft and elegant touch to your furniture.",
                    "Italian Leather": "Italian Leather is known for its premium quality and style.",
                    "Linen Type": "Linen Type is a breathable and natural fabric choice.",
                    default: "Select the type of cover for your furniture. You can also upload a custom design."
                };

                coverSelect.addEventListener("change", function () {
                    const selectedCover = coverSelect.value;
                    coverDescription.textContent = coverDescriptions[selectedCover] || coverDescriptions.default;
                });
            });
        </script>

        <!-- Design -->
        <div class="cus-boxed">
            <label for="design">Design:</label>
            <p class="dropdown-description" id="design-description">Select the design for your furniture. You can also upload a custom design.</p>
            <select id="design" name="design">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="Eclectic">Eclectic</option>
                <option value="Shabby Chic">Shabby Chic</option>
                <option value="Rustic">Rustic</option>
                <option value="Asian Inspired">Asian Inspired</option>
                <option value="Farmhouse">Farmhouse</option>
            </select>
            <div id="design-custom-options" style="display: none;">
                <label for="design-info">Design Additional Info:</label>
                <input type="text" id="design-info" name="design-info" placeholder="Enter additional info">
                <label for="fileDesignImage">Upload Design Image:</label>
                <input type="file" id="fileDesignImage" name="fileDesignImage" accept="image/*">
                <div id="design-image-preview" class="image-preview"></div>
            </div>
        </div>

        <!-- Tiles -->
        <div class="cus-boxed">
            <label for="tiles">Tiles:</label>
            <p class="dropdown-description" id="tiles-description">Select the type of tiles for your furniture. You can also upload a custom design.</p>
            <select id="tiles" name="tiles">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="Porcelain">Porcelain</option>
                <option value="Quartz">Quartz</option>
                <option value="Marble">Marble</option>
                <option value="Granite">Granite</option>
            </select>
            <div id="tiles-custom-options" style="display: none;">
                <label for="tiles-info">Tiles Additional Info:</label>
                <input type="text" id="tiles-info" name="tiles-info" placeholder="Enter additional info">
                <label for="fileTileImage">Upload Tile Image:</label>
                <input type="file" id="fileTileImage" name="fileTileImage" accept="image/*">
                <div id="tiles-image-preview" class="image-preview"></div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Design Logic
                const designSelect = document.getElementById("design");
                const designDescription = document.getElementById("design-description");

                const designDescriptions = {
                    custom: "Upload your custom design and bring your vision to life.",
                    Eclectic: "Eclectic design combines various styles for a unique look.",
                    "Shabby Chic": "Shabby Chic offers a vintage and cozy aesthetic.",
                    Rustic: "Rustic design emphasizes natural and rugged elements.",
                    "Asian Inspired": "Asian Inspired design reflects cultural elegance.",
                    Farmhouse: "Farmhouse design is warm, simple, and inviting.",
                    default: "Select the design for your furniture. You can also upload a custom design."
                };

                designSelect.addEventListener("change", function () {
                    const selectedDesign = designSelect.value;
                    designDescription.textContent = designDescriptions[selectedDesign] || designDescriptions.default;
                });

                // Tiles Logic
                const tilesSelect = document.getElementById("tiles");
                const tilesDescription = document.getElementById("tiles-description");

                const tilesDescriptions = {
                    custom: "Upload your custom tile design and bring your vision to life.",
                    Porcelain: "Porcelain tiles are durable and versatile for any furniture.",
                    Quartz: "Quartz tiles offer a sleek and modern finish.",
                    Marble: "Marble tiles add a luxurious and timeless touch.",
                    Granite: "Granite tiles are strong and perfect for heavy-duty use.",
                    default: "Select the type of tiles for your furniture. You can also upload a custom design."
                };

                tilesSelect.addEventListener("change", function () {
                    const selectedTile = tilesSelect.value;
                    tilesDescription.textContent = tilesDescriptions[selectedTile] || tilesDescriptions.default;
                });
            });
        </script>

        <!-- Metal -->
        <div class="cus-boxed">
            <label for="metal">Metal:</label>
            <p class="dropdown-description" id="metal-description">Select the type of metal for your furniture. You can also upload a custom design.</p>
            <select id="metal" name="metal">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="Flat Bar">Flat Bar</option>
                <option value="Tubular">Tubular</option>
            </select>
            <div id="metal-custom-options" style="display: none;">
                <label for="metal-info">Metal Additional Info:</label>
                <input type="text" id="metal-info" name="metal-info" placeholder="Enter additional info">
                <label for="fileMetalImage">Upload Metal Image:</label>
                <input type="file" id="fileMetalImage" name="fileMetalImage" accept="image/*">
                <div id="metal-image-preview" class="image-preview"></div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Metal Logic
                const metalSelect = document.getElementById("metal");
                const metalDescription = document.getElementById("metal-description");

                const metalDescriptions = {
                    custom: "Upload your custom metal design and bring your vision to life.",
                    "Flat Bar": "Flat Bar metal is strong and ideal for structural designs.",
                    Tubular: "Tubular metal offers a lightweight and modern look.",
                    default: "Select the type of metal for your furniture. You can also upload a custom design."
                };

                metalSelect.addEventListener("change", function () {
                    const selectedMetal = metalSelect.value;
                    metalDescription.textContent = metalDescriptions[selectedMetal] || metalDescriptions.default;
                });
            });
        </script>

        <!-- Submit and Reset Buttons -->
        <div class="cus-boxed">
        <button id="submit-button" class="btn" disabled>Submit</button>
            <button id="reset-button">Reset</button>
        </div>
    </div>

    <!-- Print Modal -->
    <div id="print-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Customization Receipt</h2>
            <div id="modal-preview" class="receipt-content"></div>
            <button id="modal-ok-button">OK</button>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Confirm Submission</h2>
            <p>Are you sure you want to submit this customization?</p>
            <button id="confirm-ok-button">OK</button>
            <!-- <button id="confirm-cancel-button">Cancel</button> -->
        </div>
    </div>

    <!-- Modal for ID Verification Warning -->
    <div id="idVerificationModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal" id="closeModal">&times;</span>
            <h2>Action Restricted</h2>
            <p>Your ID verification status is either <strong>Unverified</strong> or <strong>Invalid</strong>. Please verify your ID to proceed with customization.</p>
            <p>Go to your <a href="../profile/profile.php">Profile</a> to check your ID verification status.</p>
            <button id="closeModalButton" class="btn">Close</button>
        </div>
    </div>
</main>
<footer class="footer">
    </footer>
    <script src="../static/Javascript-files/script.js"></script>
    <script src="../static/Javascript-files/customization.js"></script>
    <script src="../static/Javascript-files/gallery.js"></script>
    <script src="../static/Javascript-files/jQuery.js"></script>
    <script>
        const idVerificationStatus = "<?php
            $stmt = $pdo->prepare('SELECT ID_Verification_Status FROM tbl_user_info WHERE User_ID = :user_id');
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo $user['ID_Verification_Status'];
        ?>";
    </script>
</body>
</html>
