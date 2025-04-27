<?php
session_start();
// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin data for profile display from the database
$adminId = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT First_Name, PicPath FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmt->bindParam(':admin_id', $adminId);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
// Construct the correct path relative to the web root if PicPath doesn't start with '../' or '/'
$profilePicPath = $admin['PicPath'];
if (!preg_match('/^(\.\.\/|\/)/', $profilePicPath)) {
    // Assuming PicPath is relative to the Capstone_Admin directory
    $profilePicPath = '../' . $profilePicPath;
}
$profilePicPath = htmlspecialchars($profilePicPath);


// Check if Customization_ID is provided
if (!isset($_GET['id'])) {
    echo "No customization ID provided.";
    exit();
}

$customizationID = $_GET['id'];
$progressID = null; // Initialize progress ID

try {
    // Fetch customization details
    $query = "
        SELECT c.*, u.First_Name, u.Last_Name, pr.Product_Status
        FROM tbl_customizations c
        JOIN tbl_user_info u ON c.User_ID = u.User_ID
        LEFT JOIN tbl_progress pr ON c.Product_ID = pr.Product_ID AND pr.Order_Type = 'custom'
        WHERE c.Customization_ID = :customizationID
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':customizationID', $customizationID, PDO::PARAM_INT);
    $stmt->execute();
    $customization = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customization) {
        echo "Customization record not found.";
        exit();
    }

    // Fetch corresponding Progress_ID if Product_ID exists
    if (!empty($customization['Product_ID'])) {
        $progressQuery = "SELECT Progress_ID FROM tbl_progress WHERE Product_ID = :productID AND Order_Type = 'custom' LIMIT 1";
        $progressStmt = $pdo->prepare($progressQuery);
        $progressStmt->bindParam(':productID', $customization['Product_ID'], PDO::PARAM_INT);
        $progressStmt->execute();
        $progressResult = $progressStmt->fetch(PDO::FETCH_ASSOC);
        if ($progressResult) {
            $progressID = $progressResult['Progress_ID'];
        }
    }


    // Function to display data or "" if empty (for input values)
    function displayInputData($data) {
        return !empty($data) ? htmlspecialchars($data) : '';
    }

    // Extract data for form fields
    $userName = displayInputData($customization['First_Name'] . ' ' . $customization['Last_Name']);
    $furnitureType = displayInputData($customization['Furniture_Type']);
    $furnitureTypeAdditionalInfo = displayInputData($customization['Furniture_Type_Additional_Info']);
    $standardSize = displayInputData($customization['Standard_Size']);
    $desiredSize = displayInputData($customization['Desired_Size']);
    $color = displayInputData($customization['Color']);
    $colorAdditionalInfo = displayInputData($customization['Color_Additional_Info']);
    $texture = displayInputData($customization['Texture']);
    $textureAdditionalInfo = displayInputData($customization['Texture_Additional_Info']);
    $woodType = displayInputData($customization['Wood_Type']);
    $woodAdditionalInfo = displayInputData($customization['Wood_Additional_Info']);
    $foamType = displayInputData($customization['Foam_Type']);
    $foamAdditionalInfo = displayInputData($customization['Foam_Additional_Info']);
    $coverType = displayInputData($customization['Cover_Type']);
    $coverAdditionalInfo = displayInputData($customization['Cover_Additional_Info']);
    $design = displayInputData($customization['Design']);
    $designAdditionalInfo = displayInputData($customization['Design_Additional_Info']);
    $tileType = displayInputData($customization['Tile_Type']);
    $tileAdditionalInfo = displayInputData($customization['Tile_Additional_Info']);
    $metalType = displayInputData($customization['Metal_Type']);
    $metalAdditionalInfo = displayInputData($customization['Metal_Additional_Info']);
    $productStatus = $customization['Product_Status'] ?? 0; // Default to 0 if no progress record exists
    $requestDate = displayInputData($customization['Request_Date']);
    $lastUpdate = displayInputData($customization['Last_Update']);
    $productID = displayInputData($customization['Product_ID']);

    // --- Get RAW image paths ---
    $rawColorImageURL = $customization['Color_Image_URL'];
    $rawTextureImageURL = $customization['Texture_Image_URL'];
    $rawWoodImageURL = $customization['Wood_Image_URL'];
    $rawFoamImageURL = $customization['Foam_Image_URL'];
    $rawCoverImageURL = $customization['Cover_Image_URL'];
    $rawDesignImageURL = $customization['Design_Image_URL'];
    $rawTileImageURL = $customization['Tile_Image_URL'];
    $rawMetalImageURL = $customization['Metal_Image_URL'];

    // Product status mapping
    $productStatusLabels = [
        0   => 'Request Approved', // 0% - Order placed by the customer
        10  => 'Design Approved', // 10% - Finalized by customer
        20  => 'Material Sourcing', // 20% - Materials being gathered
        30  => 'Cutting & Shaping', // 30% - Preparing materials
        40  => 'Structural Assembly', // 40% - Base framework built
        50  => 'Detailing & Refinements', // 50% - Carvings, elements added
        60  => 'Sanding & Pre-Finishing', // 60% - Smoothening
        70  => 'Varnishing/Painting', // 70% - Applying the final finish
        80  => 'Drying & Curing', // 80% - Final coating sets in
        90  => 'Final Inspection & Packaging', // 90% - Quality control before handover
        95  => 'Ready for Shipment', // 95% - Ready for handover/shipment
        98  => 'Order Delivered', // 98% - Confirmed delivery by logistics/customer
        100 => 'Order Received / Complete', // 100% - Final confirmation by customer / Order cycle complete
    ];

    // Convert product status to text
    $productStatusText = $productStatusLabels[$productStatus] ?? 'Unknown Status';

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Helper function to display image preview
function displayImagePreview($rawImagePath, $altText) {
    // Construct the server file path relative to the current script's directory
    $serverFilePath = __DIR__ . '/' . $rawImagePath;
    // The web path is the raw path itself (relative to the script's parent dir)
    $webPath = htmlspecialchars($rawImagePath);

    if (!empty($rawImagePath) && file_exists($serverFilePath)) {
        return "<img src='{$webPath}' alt='{$altText}' width='100' class='img-thumbnail mt-2'>";
    } else if (!empty($rawImagePath)) {
        return "<small class='text-danger mt-2 d-block'>Image not found at: {$webPath}</small>";
    } else {
        return "<small class='text-muted mt-2 d-block'>No image uploaded.</small>";
    }
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Update Customization</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        /* Adjust table layout for better form readability */
        .container_boxes table td { padding: 8px; vertical-align: top; }
        .container_boxes table td:first-child { width: 30%; font-weight: bold; }
        .container_boxes input[type="text"],
        .container_boxes textarea,
        .container_boxes select {
            width: 100%;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .container_boxes textarea { min-height: 80px; }
        .container_boxes input[readonly] { background-color: #e9ecef; }
        .img-thumbnail { border: 1px solid #dee2e6; padding: 0.25rem; background-color: #fff; }
    </style>
</head>
<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE" class="logo_image">
        </span>
      </div>
      <ul class="nav-links">
          <li><a href="../dashboard/dashboard.php"><i class="bx bx-grid-alt"></i><span class="links_name">Dashboard</span></a></li>
          <li><a href="../purchase-history/read-all-history-form.php"><i class="bx bx-history"></i><span class="links_name">Purchase History</span></a></li>
          <li><a href="../reviews/read-all-reviews-form.php"><i class="bx bx-message-dots"></i><span class="links_name">All Reviews</span></a></li>
          <!-- Add other relevant links here -->
      </ul>
    </div>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Update Customization</span>
            </div>
            <div class="profile-details" id="profile-details-container">
                <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button" id="dropdown-icon"></i>
                <div class="dropdown" id="profileDropdown">
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>
        </nav>
        <br><br><br>
        <div class="container_boxes">
            <h4>Update Customization Details (ID: <?= htmlspecialchars($customizationID) ?>)</h4>
            <!-- Ensure the action points to the correct processing script -->
            <form action="update-custom-rec.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="txtCustomizationID" value="<?= htmlspecialchars($customizationID) ?>">
                <table class="table table-bordered">
                    <tr>
                        <td>User Name:</td>
                        <td><input type="text" class="form-control" value="<?= $userName ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Furniture Type:</td>
                        <td><input type="text" name="txtFurnitureType" class="form-control" value="<?= $furnitureType ?>"></td>
                    </tr>
                    <tr>
                        <td>Furniture Type Additional Info:</td>
                        <td><textarea name="txtFurnitureTypeAdditionalInfo" class="form-control"><?= $furnitureTypeAdditionalInfo ?></textarea></td>
                    </tr>
                    <tr>
                        <td>Standard Size:</td>
                        <td><input type="text" name="txtStandardSize" class="form-control" value="<?= $standardSize ?>"></td>
                    </tr>
                    <tr>
                        <td>Desired Size:</td>
                        <td><input type="text" name="txtDesiredSize" class="form-control" value="<?= $desiredSize ?>"></td>
                    </tr>
                    <tr>
                        <td>Color:</td>
                        <td><input type="text" name="txtColor" class="form-control" value="<?= $color ?>"></td>
                    </tr>
                    <tr>
                        <td>Color Image:</td>
                        <td>
                            <input type="file" name="colorImage" class="form-control">
                            <?= displayImagePreview($rawColorImageURL, "Current Color Image") ?>
                            <!-- Hidden field to track if image should be removed -->
                            <input type="checkbox" name="remove_colorImage" value="1"> Remove Current Image
                        </td>
                    </tr>
                    <tr>
                        <td>Color Additional Info:</td>
                        <td><textarea name="txtColorAdditionalInfo" class="form-control"><?= $colorAdditionalInfo ?></textarea></td>
                    </tr>
                    <tr>
                        <td>Texture:</td>
                        <td><input type="text" name="txtTexture" class="form-control" value="<?= $texture ?>"></td>
                    </tr>
                    <tr>
                        <td>Texture Image:</td>
                        <td>
                            <input type="file" name="textureImage" class="form-control">
                            <?= displayImagePreview($rawTextureImageURL, "Current Texture Image") ?>
                             <input type="checkbox" name="remove_textureImage" value="1"> Remove Current Image
                        </td>
                    </tr>
                    <tr>
                        <td>Texture Additional Info:</td>
                        <td><textarea name="txtTextureAdditionalInfo" class="form-control"><?= $textureAdditionalInfo ?></textarea></td>
                    </tr>
                    <tr>
                        <td>Wood Type:</td>
                        <td><input type="text" name="txtWoodType" class="form-control" value="<?= $woodType ?>"></td>
                    </tr>
                    <tr>
                        <td>Wood Image:</td>
                        <td>
                            <input type="file" name="woodImage" class="form-control">
                            <?= displayImagePreview($rawWoodImageURL, "Current Wood Image") ?>
                             <input type="checkbox" name="remove_woodImage" value="1"> Remove Current Image
                        </td>
                    </tr>
                    <tr>
                        <td>Wood Additional Info:</td>
                        <td><textarea name="txtWoodAdditionalInfo" class="form-control"><?= $woodAdditionalInfo ?></textarea></td>
                    </tr>
                    <tr>
                        <td>Foam Type:</td>
                        <td><input type="text" name="txtFoamType" class="form-control" value="<?= $foamType ?>"></td>
                    </tr>
                    <tr>
                        <td>Foam Image:</td>
                        <td>
                            <input type="file" name="foamImage" class="form-control">
                            <?= displayImagePreview($rawFoamImageURL, "Current Foam Image") ?>
                             <input type="checkbox" name="remove_foamImage" value="1"> Remove Current Image
                        </td>
                    </tr>
                    <tr>
                        <td>Foam Additional Info:</td>
                        <td><textarea name="txtFoamAdditionalInfo" class="form-control"><?= $foamAdditionalInfo ?></textarea></td>
                    </tr>
                    <tr>
                        <td>Cover Type:</td>
                        <td><input type="text" name="txtCoverType" class="form-control" value="<?= $coverType ?>"></td>
                    </tr>
                    <tr>
                        <td>Cover Image:</td>
                        <td>
                            <input type="file" name="coverImage" class="form-control">
                            <?= displayImagePreview($rawCoverImageURL, "Current Cover Image") ?>
                             <input type="checkbox" name="remove_coverImage" value="1"> Remove Current Image
                        </td>
                    </tr>
                    <tr>
                        <td>Cover Additional Info:</td>
                        <td><textarea name="txtCoverAdditionalInfo" class="form-control"><?= $coverAdditionalInfo ?></textarea></td>
                    </tr>
                    <tr>
                        <td>Design:</td>
                        <td><input type="text" name="txtDesign" class="form-control" value="<?= $design ?>"></td>
                    </tr>
                    <tr>
                        <td>Design Image:</td>
                        <td>
                            <input type="file" name="designImage" class="form-control">
                            <?= displayImagePreview($rawDesignImageURL, "Current Design Image") ?>
                             <input type="checkbox" name="remove_designImage" value="1"> Remove Current Image
                        </td>
                    </tr>
                    <tr>
                        <td>Design Additional Info:</td>
                        <td><textarea name="txtDesignAdditionalInfo" class="form-control"><?= $designAdditionalInfo ?></textarea></td>
                    </tr>
                    <tr>
                        <td>Tile Type:</td>
                        <td><input type="text" name="txtTileType" class="form-control" value="<?= $tileType ?>"></td>
                    </tr>
                    <tr>
                        <td>Tile Image:</td>
                        <td>
                            <input type="file" name="tileImage" class="form-control">
                            <?= displayImagePreview($rawTileImageURL, "Current Tile Image") ?>
                             <input type="checkbox" name="remove_tileImage" value="1"> Remove Current Image
                        </td>
                    </tr>
                    <tr>
                        <td>Tile Additional Info:</td>
                        <td><textarea name="txtTileAdditionalInfo" class="form-control"><?= $tileAdditionalInfo ?></textarea></td>
                    </tr>
                    <tr>
                        <td>Metal Type:</td>
                        <td><input type="text" name="txtMetalType" class="form-control" value="<?= $metalType ?>"></td>
                    </tr>
                    <tr>
                        <td>Metal Image:</td>
                        <td>
                            <input type="file" name="metalImage" class="form-control">
                            <?= displayImagePreview($rawMetalImageURL, "Current Metal Image") ?>
                             <input type="checkbox" name="remove_metalImage" value="1"> Remove Current Image
                        </td>
                    </tr>
                    <tr>
                        <td>Metal Additional Info:</td>
                        <td><textarea name="txtMetalAdditionalInfo" class="form-control"><?= $metalAdditionalInfo ?></textarea></td>
                    </tr>
                     <!-- Product Status - Consider if this should be editable here or only via progress update -->
                     <!-- If editable here, use a dropdown -->
                  
                    <tr><td>Request Date:</td><td><input type="text" class="form-control" value="<?= $requestDate ?>" readonly></td></tr>
                    <tr><td>Last Update:</td><td><input type="text" class="form-control" value="<?= $lastUpdate ?>" readonly></td></tr>
                    <tr><td>Associated Product ID:</td><td><input type="text" class="form-control" value="<?= $productID ?>" readonly></td></tr>
                    <tr><td>Associated Progress ID:</td><td><input type="text" class="form-control" value="<?= $progressID ? htmlspecialchars($progressID) : 'N/A' ?>" readonly></td></tr>

                </table>
                <div class="button-container mt-3">
                    <a href="read-all-custom-form.php" class="buttonBack btn btn-secondary">Back to List</a>
                    <button type="submit" class="buttonUpdate btn btn-primary">Update Details</button>
                    
                    
                </div>
            </form>
        </div>
    </section>

    <script>
        // Sidebar Toggle
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        if (sidebar && sidebarBtn) {
            sidebarBtn.onclick = function () {
                sidebar.classList.toggle("active");
                sidebarBtn.classList.toggle("bx-menu-alt-right");
            };
        }

        // Profile Dropdown Toggle (Consistent version)
        const profileDetailsContainer = document.getElementById('profile-details-container');
        const profileDropdown = document.getElementById('profileDropdown');
        const dropdownIcon = document.getElementById('dropdown-icon');

        if (profileDetailsContainer && profileDropdown && dropdownIcon) {
            profileDetailsContainer.addEventListener('click', function(event) {
                // Prevent dropdown from closing if clicking inside it
                if (!profileDropdown.contains(event.target)) {
                     profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
                     // Toggle chevron icon based on display state
                     dropdownIcon.classList.toggle('bx-chevron-up', profileDropdown.style.display === 'block');
                }
            });
            // Close dropdown if clicking outside
            document.addEventListener('click', function(event) {
                if (!profileDetailsContainer.contains(event.target)) {
                    profileDropdown.style.display = 'none';
                    dropdownIcon.classList.remove('bx-chevron-up');
                }
            });
        }
    </script>
</body>
</html>
