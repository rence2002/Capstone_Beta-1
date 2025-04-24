<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin's ID is stored in the session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

// Fetch admin data from the database
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


// Check if Request_ID is passed in the URL
if (isset($_GET['id'])) {
    $requestID = $_GET['id'];

    try {
        // Fetch order request details along with customization details
        // Removed orq.Order_Status, added orq.Payment_Status, orq.Processed
        $query = "
            SELECT
                orq.Request_ID,
                orq.User_ID,
                orq.Quantity,
                orq.Order_Type,
                orq.Total_Price,
                orq.Payment_Status, -- Added
                orq.Request_Date,
                orq.Processed,      -- Added
                u.First_Name,
                u.Last_Name,
                c.Furniture_Type,
                c.Furniture_Type_Additional_Info,
                c.Standard_Size,
                c.Desired_Size,
                c.Color,
                c.Color_Image_URL,
                c.Color_Additional_Info,
                c.Texture,
                c.Texture_Image_URL,
                c.Texture_Additional_Info,
                c.Wood_Type,
                c.Wood_Image_URL,
                c.Wood_Additional_Info,
                c.Foam_Type,
                c.Foam_Image_URL,
                c.Foam_Additional_Info,
                c.Cover_Type,
                c.Cover_Image_URL,
                c.Cover_Additional_Info,
                c.Design,
                c.Design_Image_URL,
                c.Design_Additional_Info,
                c.Tile_Type,
                c.Tile_Image_URL,
                c.Tile_Additional_Info,
                c.Metal_Type,
                c.Metal_Image_URL,
                c.Metal_Additional_Info
                -- Removed orq.Order_Status AS Request_Status
            FROM tbl_order_request orq
            JOIN tbl_user_info u ON orq.User_ID = u.User_ID
            LEFT JOIN tbl_customizations_temp c ON orq.Customization_ID = c.Temp_Customization_ID
            WHERE orq.Request_ID = :requestID
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
        $stmt->execute();
        $orderRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($orderRequest) {
            // Assign fetched data to variables with htmlspecialchars
            $userName = htmlspecialchars($orderRequest['First_Name'] . ' ' . $orderRequest['Last_Name']);
            $requestDate = htmlspecialchars(date('F j, Y, g:i a', strtotime($orderRequest['Request_Date']))); // Format date
            $paymentStatus = htmlspecialchars($orderRequest['Payment_Status']);
            $processedStatus = $orderRequest['Processed'] == 0 ? 'Pending Confirmation' : 'Processed'; // Determine processing status text

            $furnitureType = htmlspecialchars($orderRequest['Furniture_Type'] ?? 'N/A');
            $furnitureTypeAdditionalInfo = htmlspecialchars($orderRequest['Furniture_Type_Additional_Info'] ?? '');
            $standardSize = htmlspecialchars($orderRequest['Standard_Size'] ?? 'N/A');
            $desiredSize = htmlspecialchars($orderRequest['Desired_Size'] ?? 'N/A');

            // Function to safely get and escape customization details
            function getCustomizationDetail($data, $key, $default = 'N/A') {
                return htmlspecialchars($data[$key] ?? $default);
            }
            function getCustomizationImage($data, $key) {
                 return !empty($data[$key]) ? htmlspecialchars($data[$key]) : null;
            }
            function getCustomizationInfo($data, $key) {
                 return !empty($data[$key]) ? htmlspecialchars($data[$key]) : null;
            }

            $color = getCustomizationDetail($orderRequest, 'Color');
            $colorImageURL = getCustomizationImage($orderRequest, 'Color_Image_URL');
            $colorAdditionalInfo = getCustomizationInfo($orderRequest, 'Color_Additional_Info');

            $texture = getCustomizationDetail($orderRequest, 'Texture');
            $textureImageURL = getCustomizationImage($orderRequest, 'Texture_Image_URL');
            $textureAdditionalInfo = getCustomizationInfo($orderRequest, 'Texture_Additional_Info');

            $woodType = getCustomizationDetail($orderRequest, 'Wood_Type');
            $woodImageURL = getCustomizationImage($orderRequest, 'Wood_Image_URL');
            $woodAdditionalInfo = getCustomizationInfo($orderRequest, 'Wood_Additional_Info');

            $foamType = getCustomizationDetail($orderRequest, 'Foam_Type');
            $foamImageURL = getCustomizationImage($orderRequest, 'Foam_Image_URL');
            $foamAdditionalInfo = getCustomizationInfo($orderRequest, 'Foam_Additional_Info');

            $coverType = getCustomizationDetail($orderRequest, 'Cover_Type');
            $coverImageURL = getCustomizationImage($orderRequest, 'Cover_Image_URL');
            $coverAdditionalInfo = getCustomizationInfo($orderRequest, 'Cover_Additional_Info');

            $design = getCustomizationDetail($orderRequest, 'Design');
            $designImageURL = getCustomizationImage($orderRequest, 'Design_Image_URL');
            $designAdditionalInfo = getCustomizationInfo($orderRequest, 'Design_Additional_Info');

            $tileType = getCustomizationDetail($orderRequest, 'Tile_Type');
            $tileImageURL = getCustomizationImage($orderRequest, 'Tile_Image_URL');
            $tileAdditionalInfo = getCustomizationInfo($orderRequest, 'Tile_Additional_Info');

            $metalType = getCustomizationDetail($orderRequest, 'Metal_Type');
            $metalImageURL = getCustomizationImage($orderRequest, 'Metal_Image_URL');
            $metalAdditionalInfo = getCustomizationInfo($orderRequest, 'Metal_Additional_Info');

        } else {
            echo "Customization request not found or details missing.";
            exit();
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
        exit();
    }
} else {
    echo "No request ID provided.";
    exit();
}

// Removed old $statusLabels array as it's not relevant here

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Custom Request Details</title> <!-- Specific Title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <!-- <script src="../static/js/dashboard.js"></script> --> <!-- Removed -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <!-- <link href="../static/js/admin_home.js" rel=""> --> <!-- Incorrect link type -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        .table th { width: 25%; background-color: #f8f9fa; }
        .table td { width: 75%; }
        .customization-img { max-width: 200px; height: auto; display: block; margin-top: 5px; }
        .additional-info { margin-top: 5px; font-style: italic; color: #555; }
    </style>
</head>

<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE" class="logo_image"> <!-- Use class -->
        </span>
      </div>
      <ul class="nav-links">
          <li>
              <a href="../dashboard/dashboard.php">
                  <i class="bx bx-grid-alt"></i>
                  <span class="links_name">Dashboard</span>
              </a>
          </li>
          <li>
              <a href="../purchase-history/read-all-history-form.php">
                  <i class="bx bx-history"></i> <!-- Changed icon -->
                  <span class="links_name">Purchase History</span>
              </a>
          </li>
          <li>
              <a href="../reviews/read-all-reviews-form.php">
                  <i class="bx bx-message-dots"></i>
                  <span class="links_name">All Reviews</span>
              </a>
          </li>
          <!-- Add other relevant links here -->
      </ul>
    </div>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Custom Request Details</span> <!-- Updated title -->
            </div>
            <!-- Removed search box -->
            <div class="profile-details" id="profile-details-container"> <!-- Added ID -->
                <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button" id="dropdown-icon"></i> <!-- Added ID -->
                <div class="dropdown" id="profileDropdown">
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>
        </nav>
        <br><br><br>

        <div class="container_boxes">
            <h4>CUSTOMIZATION REQUEST DETAILS (ID: <?= htmlspecialchars($requestID) ?>)</h4>

            <table class="table table-bordered table-striped"> <!-- Bootstrap table classes -->
                <tr><th>User Name</th><td><?= $userName ?></td></tr>
                <tr><th>Request Date</th><td><?= $requestDate ?></td></tr>
                <tr><th>Processing Status</th><td><?= $processedStatus ?></td></tr>
                <tr><th>Payment Status</th><td><?= ucwords(str_replace('_', ' ', $paymentStatus)) ?></td></tr>
                <tr><th>Total Price</th><td>₱ <?= number_format((float)($orderRequest['Total_Price'] ?? 0), 2) ?></td></tr>
                <tr><th>Quantity</th><td><?= htmlspecialchars($orderRequest['Quantity'] ?? 1) ?></td></tr>

                <tr><th colspan="2" class="text-center bg-secondary text-white">Furniture Details</th></tr>
                <tr><th>Furniture Type</th><td><?= $furnitureType ?></td></tr>
                <?php if ($furnitureTypeAdditionalInfo): ?>
                    <tr><th>Type Additional Info</th><td><?= $furnitureTypeAdditionalInfo ?></td></tr>
                <?php endif; ?>
                <tr><th>Standard Size</th><td><?= $standardSize ?></td></tr>
                <tr><th>Desired Size</th><td><?= $desiredSize ?></td></tr>

                <tr><th colspan="2" class="text-center bg-secondary text-white">Material & Appearance</th></tr>
                <tr>
                    <th>Color</th>
                    <td>
                        <?= $color ?>
                        <?php if ($colorImageURL): ?>
                            <img src="<?= $colorImageURL ?>" alt="Color Sample" class="customization-img">
                        <?php endif; ?>
                        <?php if ($colorAdditionalInfo): ?>
                            <div class="additional-info"><?= $colorAdditionalInfo ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Texture</th>
                    <td>
                        <?= $texture ?>
                        <?php if ($textureImageURL): ?>
                            <img src="<?= $textureImageURL ?>" alt="Texture Sample" class="customization-img">
                        <?php endif; ?>
                        <?php if ($textureAdditionalInfo): ?>
                            <div class="additional-info"><?= $textureAdditionalInfo ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                 <tr>
                    <th>Wood Type</th>
                    <td>
                        <?= $woodType ?>
                        <?php if ($woodImageURL): ?>
                            <img src="<?= $woodImageURL ?>" alt="Wood Sample" class="customization-img">
                        <?php endif; ?>
                        <?php if ($woodAdditionalInfo): ?>
                            <div class="additional-info"><?= $woodAdditionalInfo ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                 <tr>
                    <th>Foam Type</th>
                    <td>
                        <?= $foamType ?>
                        <?php if ($foamImageURL): ?>
                            <img src="<?= $foamImageURL ?>" alt="Foam Sample" class="customization-img">
                        <?php endif; ?>
                        <?php if ($foamAdditionalInfo): ?>
                            <div class="additional-info"><?= $foamAdditionalInfo ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                 <tr>
                    <th>Cover Type</th>
                    <td>
                        <?= $coverType ?>
                        <?php if ($coverImageURL): ?>
                            <img src="<?= $coverImageURL ?>" alt="Cover Sample" class="customization-img">
                        <?php endif; ?>
                        <?php if ($coverAdditionalInfo): ?>
                            <div class="additional-info"><?= $coverAdditionalInfo ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                 <tr>
                    <th>Design</th>
                    <td>
                        <?= $design ?>
                        <?php if ($designImageURL): ?>
                            <img src="<?= $designImageURL ?>" alt="Design Sample" class="customization-img">
                        <?php endif; ?>
                        <?php if ($designAdditionalInfo): ?>
                            <div class="additional-info"><?= $designAdditionalInfo ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                 <tr>
                    <th>Tile Type</th>
                    <td>
                        <?= $tileType ?>
                        <?php if ($tileImageURL): ?>
                            <img src="<?= $tileImageURL ?>" alt="Tile Sample" class="customization-img">
                        <?php endif; ?>
                        <?php if ($tileAdditionalInfo): ?>
                            <div class="additional-info"><?= $tileAdditionalInfo ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                 <tr>
                    <th>Metal Type</th>
                    <td>
                        <?= $metalType ?>
                        <?php if ($metalImageURL): ?>
                            <img src="<?= $metalImageURL ?>" alt="Metal Sample" class="customization-img">
                        <?php endif; ?>
                        <?php if ($metalAdditionalInfo): ?>
                            <div class="additional-info"><?= $metalAdditionalInfo ?></div>
                        <?php endif; ?>
                    </td>
                </tr>

            </table>
            <br>
            <a href="read-all-request-form.php" class="buttonBack btn btn-secondary">Back to Order Requests</a>
        </div>
    </section>

    <script>
        // Sidebar Toggle
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        if (sidebar && sidebarBtn) {
            sidebarBtn.onclick = function () {
                sidebar.classList.toggle("active");
                if (sidebar.classList.contains("active")) {
                    sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
                } else {
                    sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
                }
            };
        }

        // Profile Dropdown Toggle (Consistent version)
        const profileDetailsContainer = document.getElementById('profile-details-container');
        const profileDropdown = document.getElementById('profileDropdown');
        const dropdownIcon = document.getElementById('dropdown-icon');

        if (profileDetailsContainer && profileDropdown && dropdownIcon) {
            profileDetailsContainer.addEventListener('click', function(event) {
                // Prevent dropdown from closing if click is inside dropdown
                if (!profileDropdown.contains(event.target)) {
                     profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
                     dropdownIcon.classList.toggle('bx-chevron-up'); // Toggle icon class
                }
            });

            // Close dropdown if clicked outside
            document.addEventListener('click', function(event) {
                if (!profileDetailsContainer.contains(event.target)) {
                    profileDropdown.style.display = 'none';
                    dropdownIcon.classList.remove('bx-chevron-up'); // Ensure icon is down
                }
            });
        }

        // Removed old dropdown toggle JS

    </script>
</body>
</html>
