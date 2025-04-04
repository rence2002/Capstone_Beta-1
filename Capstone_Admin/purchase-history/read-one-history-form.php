<?php
session_start();

// Include the database connection
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /Capstone/login.php");
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

// Get the Purchase_ID from the URL
$purchaseID = $_GET['id'] ?? null;

if (!$purchaseID) {
    echo "Error: Purchase ID is not specified.";
    exit();
}

// Fetch purchase history details
$query = "SELECT 
            ph.Purchase_ID, ph.User_ID, ph.Product_ID, ph.Product_Name, ph.Quantity, 
            ph.Total_Price, ph.Order_Type, ph.Purchase_Date, ph.Order_Status, ph.Product_Status,
            CONCAT(ui.First_Name, ' ', ui.Last_Name) AS User_Name
          FROM tbl_purchase_history ph
          JOIN tbl_user_info ui ON ph.User_ID = ui.User_ID
          WHERE ph.Purchase_ID = :purchase_id";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':purchase_id', $purchaseID, PDO::PARAM_INT);
$stmt->execute();
$purchase = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$purchase) {
    echo "Error: Purchase record not found.";
    exit();
}

// Map order status to descriptive text
$orderStatusMap = [
    0 => 'Order Received',
    10 => 'Order Confirmed',
    20 => 'Design Finalization',
    30 => 'Material Preparation',
    40 => 'Production Started',
    50 => 'Mid-Production',
    60 => 'Finishing Process',
    70 => 'Quality Check',
    80 => 'Final Assembly',
    90 => 'Ready for Delivery',
    100 => 'Delivered / Completed'
];

$orderStatusText = $orderStatusMap[$purchase['Order_Status']] ?? 'Unknown Status';

// Product Status Map
$productStatusLabels = [
    0   => 'Concept Stage',
    10  => 'Design Approved',
    20  => 'Material Sourcing',
    30  => 'Cutting & Shaping',
    40  => 'Structural Assembly',
    50  => 'Detailing & Refinements',
    60  => 'Sanding & Pre-Finishing',
    70  => 'Final Coating',
    80  => 'Assembly & Testing',
    90  => 'Ready for Sale',
    100 => 'Sold / Installed'
];
$productStatusText = $productStatusLabels[$purchase['Product_Status']] ?? 'Unknown Status';
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
                <img src="../<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button"></i>

                <div class="dropdown" id="profileDropdown">
                    <!-- Modified link here -->
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>

<!-- Link to External JS -->
<script src="../static/js/dashboard.js"></script>


 </nav>
 
 <br><br><br>

        <div class="container_boxes">
            <h4>Purchase History Details</h4>
            <table class="table">
                <tr><th>Purchase ID:</th><td><?php echo htmlspecialchars($purchase['Purchase_ID']); ?></td></tr>
                <tr><th>User:</th><td><?php echo htmlspecialchars($purchase['User_Name']); ?> (<?php echo htmlspecialchars($purchase['User_ID']); ?>)</td></tr>
                <tr><th>Product:</th><td><?php echo htmlspecialchars($purchase['Product_Name']); ?> (ID: <?php echo htmlspecialchars($purchase['Product_ID']); ?>)</td></tr>
                <tr><th>Quantity:</th><td><?php echo htmlspecialchars($purchase['Quantity']); ?></td></tr>
                <tr><th>Total Price:</th><td><?php echo number_format($purchase['Total_Price'], 2); ?></td></tr>
                <tr><th>Order Type:</th><td><?php echo htmlspecialchars($purchase['Order_Type']); ?></td></tr>
                <tr><th>Purchase Date:</th><td><?php echo htmlspecialchars($purchase['Purchase_Date']); ?></td></tr>
                <tr><th>Order Status:</th><td><?php echo htmlspecialchars($orderStatusText); ?></td></tr>
                 <tr><th>Product Status:</th><td><?php echo htmlspecialchars($productStatusText); ?></td></tr>
            </table>
            <div class="button-container">
                <a href="read-all-history-form.php" class="btn btn-primary">Back to List</a>
                <!-- <a href="update-history-form.php?id=<?php echo htmlspecialchars($purchase['Purchase_ID']); ?>" target="_parent" class="buttonUpdate">Update Record</a> -->

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
