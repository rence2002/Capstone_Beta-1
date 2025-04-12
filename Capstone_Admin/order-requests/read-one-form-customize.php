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
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Check if Request_ID is passed in the URL
if (isset($_GET['id'])) {
    $requestID = $_GET['id'];
    
    try {
        // Fetch order request details
        $query = "
            SELECT 
                orq.*, 
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
                c.Metal_Additional_Info,
                orq.Order_Status AS Request_Status
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
            $userName = htmlspecialchars($orderRequest['First_Name'] . ' ' . $orderRequest['Last_Name']);
            $furnitureType = htmlspecialchars($orderRequest['Furniture_Type']);
            $furnitureTypeAdditionalInfo = htmlspecialchars($orderRequest['Furniture_Type_Additional_Info']);
            $standardSize = htmlspecialchars($orderRequest['Standard_Size']);
            $desiredSize = htmlspecialchars($orderRequest['Desired_Size']);
            $color = htmlspecialchars($orderRequest['Color']);
            $colorImageURL = htmlspecialchars($orderRequest['Color_Image_URL']);
            $colorAdditionalInfo = htmlspecialchars($orderRequest['Color_Additional_Info']);
            $texture = htmlspecialchars($orderRequest['Texture']);
            $textureImageURL = htmlspecialchars($orderRequest['Texture_Image_URL']);
            $textureAdditionalInfo = htmlspecialchars($orderRequest['Texture_Additional_Info']);
            $woodType = htmlspecialchars($orderRequest['Wood_Type']);
            $woodImageURL = htmlspecialchars($orderRequest['Wood_Image_URL']);
            $woodAdditionalInfo = htmlspecialchars($orderRequest['Wood_Additional_Info']);
            $foamType = htmlspecialchars($orderRequest['Foam_Type']);
            $foamImageURL = htmlspecialchars($orderRequest['Foam_Image_URL']);
            $foamAdditionalInfo = htmlspecialchars($orderRequest['Foam_Additional_Info']);
            $coverType = htmlspecialchars($orderRequest['Cover_Type']);
            $coverImageURL = htmlspecialchars($orderRequest['Cover_Image_URL']);
            $coverAdditionalInfo = htmlspecialchars($orderRequest['Cover_Additional_Info']);
            $design = htmlspecialchars($orderRequest['Design']);
            $designImageURL = htmlspecialchars($orderRequest['Design_Image_URL']);
            $designAdditionalInfo = htmlspecialchars($orderRequest['Design_Additional_Info']);
            $tileType = htmlspecialchars($orderRequest['Tile_Type']);
            $tileImageURL = htmlspecialchars($orderRequest['Tile_Image_URL']);
            $tileAdditionalInfo = htmlspecialchars($orderRequest['Tile_Additional_Info']);
            $metalType = htmlspecialchars($orderRequest['Metal_Type']);
            $metalImageURL = htmlspecialchars($orderRequest['Metal_Image_URL']);
            $metalAdditionalInfo = htmlspecialchars($orderRequest['Metal_Additional_Info']);
            $status = htmlspecialchars($orderRequest['Request_Status']); // Use the alias
            $requestDate = htmlspecialchars($orderRequest['Request_Date']);
        } else {
            echo "Customization request not found.";
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
} else {
    echo "No request ID provided.";
    exit();
}

// Status mapping
$statusLabels = [
    0 => 'Pending',
    10 => 'Order Placed',
    20 => 'Payment Processing',
    30 => 'Order Confirmed',
    40 => 'Preparing for Shipment',
    50 => 'Shipped',
    60 => 'Out for Delivery',
    70 => 'Delivered',
    80 => 'Installed',
    100 => 'Complete'
];
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
    <!-- <link href="../static/css-files/dashboard.css" rel="stylesheet"> -->
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="../static/js/admin_home.js" rel="">
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
        <i class="bx bx-message-dots"></i> <!-- Changed to a more appropriate message icon -->
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
            <div class="search-box">
                <input type="text" placeholder="Search..." />
                <i class="bx bx-search"></i>
            </div>


            <div class="profile-details" onclick="toggleDropdown()">
    <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" />
    <span class="admin_name"><?php echo $adminName; ?></span>
    <i class="bx bx-chevron-down dropdown-button"></i>

    <div class="dropdown" id="profileDropdown">
        <a href="../admin/read-one-admin-form.php">Settings</a>
        <a href="../admin/logout.php">Logout</a>
    </div>
</div>

<!-- Link to External JS -->
<script src="dashboard.js"></script>


 </nav>

<br><br><br>

<div class="container_boxes">
    <h4>CUSTOMIZATION REQUEST DETAILS</h4>
    <table width="100%" border="1" cellspacing="5">
        <tr>
            <th>USER NAME</th>
            <td><?php echo $userName; ?></td>
        </tr>
        <tr>
            <th>FURNITURE TYPE</th>
            <td><?php echo $furnitureType; ?></td>
        </tr>
        <tr>
            <th>FURNITURE TYPE ADDITIONAL INFO</th>
            <td><?php echo $furnitureTypeAdditionalInfo; ?></td>
        </tr>
        <tr>
            <th>STANDARD SIZE</th>
            <td><?php echo $standardSize; ?></td>
        </tr>
        <tr>
            <th>DESIRED SIZE</th>
            <td><?php echo $desiredSize; ?></td>
        </tr>
        <tr>
            <th>COLOR</th>
            <td><?php echo $color; ?></td>
        </tr>
        <tr>
            <th>COLOR IMAGE</th>
            <td>
                <?php if ($colorImageURL): ?>
                    <img src="<?php echo $colorImageURL; ?>" alt="Color Image" style="width: 300px; height: 300px;">
                <?php else: ?>
                    No color image available.
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>COLOR ADDITIONAL INFO</th>
            <td><?php echo $colorAdditionalInfo; ?></td>
        </tr>
        <tr>
            <th>TEXTURE</th>
            <td><?php echo $texture; ?></td>
        </tr>
        <tr>
            <th>TEXTURE IMAGE</th>
            <td>
                <?php if ($textureImageURL): ?>
                    <img src="<?php echo $textureImageURL; ?>" alt="Texture Image" style="width: 300px; height: 300px;">
                <?php else: ?>
                    No texture image available.
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>TEXTURE ADDITIONAL INFO</th>
            <td><?php echo $textureAdditionalInfo; ?></td>
        </tr>
        <tr>
            <th>WOOD TYPE</th>
            <td><?php echo $woodType; ?></td>
        </tr>
        <tr>
            <th>WOOD IMAGE</th>
            <td>
                <?php if ($woodImageURL): ?>
                    <img src="<?php echo $woodImageURL; ?>" alt="Wood Image" style="width: 300px; height: 300px;">
                <?php else: ?>
                    No wood image available.
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>WOOD ADDITIONAL INFO</th>
            <td><?php echo $woodAdditionalInfo; ?></td>
        </tr>
        <tr>
            <th>FOAM TYPE</th>
            <td><?php echo $foamType; ?></td>
        </tr>
        <tr>
            <th>FOAM IMAGE</th>
            <td>
                <?php if ($foamImageURL): ?>
                    <img src="<?php echo $foamImageURL; ?>" alt="Foam Image" style="width: 300px; height: 300px;">
                <?php else: ?>
                    No foam image available.
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>FOAM ADDITIONAL INFO</th>
            <td><?php echo $foamAdditionalInfo; ?></td>
        </tr>
        <tr>
            <th>COVER TYPE</th>
            <td><?php echo $coverType; ?></td>
        </tr>
        <tr>
            <th>COVER IMAGE</th>
            <td>
                <?php if ($coverImageURL): ?>
                    <img src="<?php echo $coverImageURL; ?>" alt="Cover Image" style="width: 300px; height: 300px;">
                <?php else: ?>
                    No cover image available.
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>COVER ADDITIONAL INFO</th>
            <td><?php echo $coverAdditionalInfo; ?></td>
        </tr>
        <tr>
            <th>DESIGN</th>
            <td><?php echo $design; ?></td>
        </tr>
        <tr>
            <th>DESIGN IMAGE</th>
            <td>
                <?php if ($designImageURL): ?>
                    <img src="<?php echo $designImageURL; ?>" alt="Design Image" style="width: 300px; height: 300px;">
                <?php else: ?>
                    No design image available.
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>DESIGN ADDITIONAL INFO</th>
            <td><?php echo $designAdditionalInfo; ?></td>
        </tr>
        <tr>
            <th>TILE TYPE</th>
            <td><?php echo $tileType; ?></td>
        </tr>
        <tr>
            <th>TILE IMAGE</th>
            <td>
                <?php if ($tileImageURL): ?>
                    <img src="<?php echo $tileImageURL; ?>" alt="Tile Image" style="width: 300px; height: 300px;">
                <?php else: ?>
                    No tile image available.
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>TILE ADDITIONAL INFO</th>
            <td><?php echo $tileAdditionalInfo; ?></td>
        </tr>
        <tr>
            <th>METAL TYPE</th>
            <td><?php echo $metalType; ?></td>
        </tr>
        <tr>
            <th>METAL IMAGE</th>
            <td>
                <?php if ($metalImageURL): ?>
                    <img src="<?php echo $metalImageURL; ?>" alt="Metal Image" style="width: 300px; height: 300px;">
                <?php else: ?>
                    No metal image available.
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>METAL ADDITIONAL INFO</th>
            <td><?php echo $metalAdditionalInfo; ?></td>
        </tr>
        <tr>
            <th>STATUS</th>
            <td><?php echo $statusLabels[$status] ?? 'Unknown'; ?></td>
        </tr>
        <tr>
            <th>REQUEST DATE</th>
            <td><?php echo $requestDate; ?></td>
        </tr>
    </table>
    <br>
    <a href="read-all-request-form.php" class="btn btn-primary">Back to Order Requests</a>
</div>
</section>

<script>
    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".sidebarBtn");
    sidebarBtn.onclick = function () {
        sidebar.classList.toggle("active");
        if (sidebar.classList.contains("active")) {
            sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
        } else {
            sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
        }
    };

    document.querySelectorAll('.dropdown-toggle').forEach((toggle) => {
    toggle.addEventListener('click', function () {
        const parent = this.parentElement; // Get the parent <li> of the toggle
        const dropdownMenu = parent.querySelector('.dropdown-menu'); // Get the <ul> of the dropdown menu
        parent.classList.toggle('active'); // Toggle the 'active' class on the parent <li>

        // Toggle the chevron icon rotation
        const chevron = this.querySelector('i'); // Find the chevron icon inside the toggle
        if (parent.classList.contains('active')) {
            chevron.classList.remove('bx-chevron-down');
            chevron.classList.add('bx-chevron-up'); // Change to up when menu is open
        } else {
            chevron.classList.remove('bx-chevron-up');
            chevron.classList.add('bx-chevron-down'); // Change to down when menu is closed
        }
        
        // Toggle the display of the dropdown menu
        dropdownMenu.style.display = parent.classList.contains('active') ? 'block' : 'none';
    });
});
</script>
</body>
</html>
