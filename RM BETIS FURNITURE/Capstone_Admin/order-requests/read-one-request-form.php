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
                p.Product_Name, 
                p.Category AS Furniture_Type,
                p.GLB_File_URL,
                orq.Order_Status AS Request_Status
            FROM tbl_order_request orq
            JOIN tbl_user_info u ON orq.User_ID = u.User_ID
            LEFT JOIN tbl_prod_info p ON orq.Product_ID = p.Product_ID
            WHERE orq.Request_ID = :requestID
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
        $stmt->execute();
        $orderRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($orderRequest) {
            $userName = htmlspecialchars($orderRequest['First_Name'] . ' ' . $orderRequest['Last_Name']);
            $productName = htmlspecialchars($orderRequest['Product_Name']);
            $furnitureType = htmlspecialchars($orderRequest['Furniture_Type']);
            $quantity = htmlspecialchars($orderRequest['Quantity']);
            $orderType = htmlspecialchars($orderRequest['Order_Type']);
            $status = htmlspecialchars($orderRequest['Request_Status']); // Use the alias
            $totalPrice = htmlspecialchars($orderRequest['Total_Price']);
            $requestDate = htmlspecialchars($orderRequest['Request_Date']);
            $glbFileURL = htmlspecialchars($orderRequest['GLB_File_URL']);
        } else {
            echo "Order request not found.";
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

// Order Status mapping
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
    <h4>ORDER REQUEST DETAILS</h4>
    <table width="100%" border="1" cellspacing="5">
        <tr>
            <th>USER NAME</th>
            <td><?php echo $userName; ?></td>
        </tr>
        <tr>
            <th>PRODUCT NAME</th>
            <td><?php echo $productName; ?></td>
        </tr>
        <tr>
            <th>3D MODEL</th>
            <td>
                <?php if ($glbFileURL): ?>
                    <model-viewer src="<?php echo $glbFileURL; ?>" auto-rotate camera-controls style="width: 300px; height: 300px;"></model-viewer>
                <?php else: ?>
                    No 3D model available.
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>FURNITURE TYPE</th>
            <td><?php echo $furnitureType; ?></td>
        </tr>
        <tr>
            <th>QUANTITY</th>
            <td><?php echo $quantity; ?></td>
        </tr>
        <tr>
            <th>ORDER TYPE</th>
            <td><?php echo $orderType; ?></td>
        </tr>
        <tr>
            <th>STATUS</th>
            <td><?php echo $statusLabels[$status] ?? 'Unknown'; ?></td>
        </tr>
        <tr>
            <th>TOTAL PRICE</th>
            <td><?php echo $totalPrice; ?></td>
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
<script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
</body>
</html>