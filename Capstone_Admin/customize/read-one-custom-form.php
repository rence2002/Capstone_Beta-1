<?php
session_start();
// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin data
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
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Check if Customization_ID is provided
if (!isset($_GET['id'])) {
    echo "No customization ID provided.";
    exit();
}

$customizationID = $_GET['id'];

try {
    // Fetch customization details with progress data
    $query = "
        SELECT 
            c.*, 
            u.First_Name, 
            u.Last_Name, 
            p.Product_Name,
            pr.Product_Status,
            pr.Progress_ID
        FROM tbl_customizations c
        JOIN tbl_user_info u ON c.User_ID = u.User_ID
        LEFT JOIN tbl_prod_info p ON c.Product_ID = p.Product_ID
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

    // Function to display data or "N/A" if empty
    function displayData($data) {
        return !empty($data) ? htmlspecialchars($data) : 'N/A';
    }

    // Function to display image or "N/A" if empty
    function displayImage($imageURL) {
        if (!empty($imageURL)) {
            // Construct absolute path for file existence check
            $absolutePath = 'C:/xampp/htdocs/Capstone_Beta/' . $imageURL;
            if (file_exists($absolutePath)) {
                return "<img src='/" . htmlspecialchars($imageURL) . "' alt='Image' width='100'>";
            }
        }
        return 'N/A';
    }

    // Extract data and use displayData function
    $userName = displayData($customization['First_Name'] . ' ' . $customization['Last_Name']);
    $furnitureType = displayData($customization['Furniture_Type']);
    $furnitureTypeAdditionalInfo = displayData($customization['Furniture_Type_Additional_Info']);
    $standardSize = displayData($customization['Standard_Size']);
    $desiredSize = displayData($customization['Desired_Size']);
    $color = displayData($customization['Color']);
    $colorImage = displayImage($customization['Color_Image_URL']);
    $colorAdditionalInfo = displayData($customization['Color_Additional_Info']);
    $texture = displayData($customization['Texture']);
    $textureImage = displayImage($customization['Texture_Image_URL']);
    $textureAdditionalInfo = displayData($customization['Texture_Additional_Info']);
    $woodType = displayData($customization['Wood_Type']);
    $woodImage = displayImage($customization['Wood_Image_URL']);
    $woodAdditionalInfo = displayData($customization['Wood_Additional_Info']);
    $foamType = displayData($customization['Foam_Type']);
    $foamImage = displayImage($customization['Foam_Image_URL']);
    $foamAdditionalInfo = displayData($customization['Foam_Additional_Info']);
    $coverType = displayData($customization['Cover_Type']);
    $coverImage = displayImage($customization['Cover_Image_URL']);
    $coverAdditionalInfo = displayData($customization['Cover_Additional_Info']);
    $design = displayData($customization['Design']);
    $designImage = displayImage($customization['Design_Image_URL']);
    $designAdditionalInfo = displayData($customization['Design_Additional_Info']);
    $tileType = displayData($customization['Tile_Type']);
    $tileImage = displayImage($customization['Tile_Image_URL']);
    $tileAdditionalInfo = displayData($customization['Tile_Additional_Info']);
    $metalType = displayData($customization['Metal_Type']);
    $metalImage = displayImage($customization['Metal_Image_URL']);
    $metalAdditionalInfo = displayData($customization['Metal_Additional_Info']);

    // Get product status from progress table
    $productStatus = $customization['Product_Status'] ?? 0;
    $requestDate = displayData($customization['Request_Date']);
    $lastUpdate = displayData($customization['Last_Update']);
    $productID = displayData($customization['Product_ID']);

    // Product status labels
    $productStatusLabels = [
        0   => 'Request Approved',         // 0% - Order placed by the customer
        10  => 'Design Approved',        // 10% - Finalized by customer
        20  => 'Material Sourcing',      // 20% - Gathering necessary materials
        30  => 'Cutting & Shaping',      // 30% - Preparing materials
        40  => 'Structural Assembly',    // 40% - Base framework built
        50  => 'Detailing & Refinements',// 50% - Carvings, upholstery, elements added
        60  => 'Sanding & Pre-Finishing',// 60% - Smoothening, preparing for final coat
        70  => 'Varnishing/Painting',    // 70% - Applying the final finish
        80  => 'Drying & Curing',        // 80% - Final coating sets in
        90  => 'Final Inspection & Packaging', // 90% - Quality control before handover
        95  => 'Ready for Shipment',
        98  => 'Order Delivered',
        100 => 'Order Received'          // Note: Fixed typo from 'Recieved' to 'Received'
    ];

    $productStatusText = $productStatusLabels[$productStatus] ?? 'Unknown Status';
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../static/js/dashboard.js"></script>
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
</head>
<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE"  class="logo_name">
        </span>
    </div>
        <ul class="nav-links">
            <li>
                <a href="../dashboard/dashboard.php" class="">
                    <i class="bx bx-grid-alt"></i>
                    <span class="links_name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="../purchase-history/read-all-history-form.php" class="">
                    <i class="bx bx-comment-detail"></i>
                    <span class="links_name">All Purchase History</span>
                </a>
            </li>
            <li>
                <a href="../reviews/read-all-reviews-form.php">
                    <i class="bx bx-message-dots"></i>
                    <span class="links_name">All Reviews</span>
                </a>
            </li>
        </ul>
    </div>
    <section class="home-section">
    <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Dashboard</span>
            </div>
            <div class="profile-details" onclick="toggleDropdown()">
                <img src="http://localhost/Capstone_Beta/<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button"></i>

                <div class="dropdown" id="profileDropdown">
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>
    </nav>
        <br><br><br>
        <div class="container_boxes">
            <h4>Customization Details</h4>
            <table>
                <tr><td>Customization ID:</td><td><?= $customizationID ?></td></tr>
                <tr><td>User Name:</td><td><?= $userName ?></td></tr>
                <tr><td>Furniture Type:</td><td><?= $furnitureType ?></td></tr>
                <tr><td>Furniture Type Additional Info:</td><td><?= $furnitureTypeAdditionalInfo ?></td></tr>
                <tr><td>Standard Size:</td><td><?= $standardSize ?></td></tr>
                <tr><td>Desired Size:</td><td><?= $desiredSize ?></td></tr>
                <tr><td>Color:</td><td><?= $color ?></td></tr>
                <tr><td>Color Image:</td><td><?= $colorImage ?></td></tr>
                <tr><td>Color Additional Info:</td><td><?= $colorAdditionalInfo ?></td></tr>
                <tr><td>Texture:</td><td><?= $texture ?></td></tr>
                <tr><td>Texture Image:</td><td><?= $textureImage ?></td></tr>
                <tr><td>Texture Additional Info:</td><td><?= $textureAdditionalInfo ?></td></tr>
                <tr><td>Wood Type:</td><td><?= $woodType ?></td></tr>
                <tr><td>Wood Image:</td><td><?= $woodImage ?></td></tr>
                <tr><td>Wood Additional Info:</td><td><?= $woodAdditionalInfo ?></td></tr>
                <tr><td>Foam Type:</td><td><?= $foamType ?></td></tr>
                <tr><td>Foam Image:</td><td><?= $foamImage ?></td></tr>
                <tr><td>Foam Additional Info:</td><td><?= $foamAdditionalInfo ?></td></tr>
                <tr><td>Cover Type:</td><td><?= $coverType ?></td></tr>
                <tr><td>Cover Image:</td><td><?= $coverImage ?></td></tr>
                <tr><td>Cover Additional Info:</td><td><?= $coverAdditionalInfo ?></td></tr>
                <tr><td>Design:</td><td><?= $design ?></td></tr>
                <tr><td>Design Image:</td><td><?= $designImage ?></td></tr>
                <tr><td>Design Additional Info:</td><td><?= $designAdditionalInfo ?></td></tr>
                <tr><td>Tile Type:</td><td><?= $tileType ?></td></tr>
                <tr><td>Tile Image:</td><td><?= $tileImage ?></td></tr>
                <tr><td>Tile Additional Info:</td><td><?= $tileAdditionalInfo ?></td></tr>
                <tr><td>Metal Type:</td><td><?= $metalType ?></td></tr>
                <tr><td>Metal Image:</td><td><?= $metalImage ?></td></tr>
                <tr><td>Metal Additional Info:</td><td><?= $metalAdditionalInfo ?></td></tr>
                <!-- Corrected: Display Product Status -->
             
                <tr><td>Request Date:</td><td><?= $requestDate ?></td></tr>
                <tr><td>Last Update:</td><td><?= $lastUpdate ?></td></tr>
                <tr><td>Product ID:</td><td><?= $productID ?></td></tr>
            </table>
            <div class="button-container">
                <a href="read-all-custom-form.php" class="buttonBack">Back to List</a>
                <a class="buttonEdit" href="update-custom-form.php?id=<?= $customizationID ?>">Edit</a>
            </div>
        </div>
    </section>
    <script>
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        sidebarBtn.onclick = function () {
            sidebar.classList.toggle("active");
            if (sidebar.classList.contains("active")) {
                sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
        };
        document.querySelectorAll('.dropdown-toggle').forEach((toggle) => {
            toggle.addEventListener('click', function () {
                const parent = this.parentElement;
                const dropdownMenu = parent.querySelector('.dropdown-menu');
                parent.classList.toggle('active');
                const chevron = this.querySelector('i');
                if (parent.classList.contains('active')) {
                    chevron.classList.remove('bx-chevron-down');
                    chevron.classList.add('bx-chevron-up');
                } else {
                    chevron.classList.remove('bx-chevron-up');
                    chevron.classList.add('bx-chevron-down');
                }
                dropdownMenu.style.display = parent.classList.contains('active') ? 'block' : 'none';
            });
        });
    </script>
    <style>
        /* Progress bar styles */
        .status-bar {
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            height: 20px;
            position: relative;
            width: 100%;
            margin-bottom: 5px;
        }
        .status-bar-fill {
            background-color: #4CAF50;
            height: 100%;
            text-align: center;
            color: white;
            line-height: 20px;
            font-size: 12px;
            transition: width 0.5s ease-in-out;
            white-space: nowrap;
        }
        .product-status-bar { background-color: #2196F3; }
        .status-text {
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</body>
</html>