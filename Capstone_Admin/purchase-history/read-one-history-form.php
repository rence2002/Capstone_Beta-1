<?php
session_start();

// Include the database connection
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Use a more consistent relative path
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
$baseUrl = 'http://localhost/Capstone_Beta/';
$profilePicPath = $admin['PicPath'];
// Remove any leading slashes or duplicate directories
$profilePicPath = preg_replace('/^[\/]*(Capstone_Beta\/)?(Capstone_Admin\/)?(admin\/)?/', '', $profilePicPath);
$profilePicPath = htmlspecialchars($profilePicPath);


// Get the Purchase_ID and Order_Type from the URL
$purchaseID = $_GET['id'] ?? null;
$orderType = $_GET['order_type'] ?? null;

if (!$purchaseID || !$orderType) {
    header("Location: read-all-history-form.php?error=" . urlencode("Missing ID or Order Type."));
    exit();
}

$purchase = null; // Initialize purchase data variable

try {
    // Query the purchase history table
    $query = "SELECT
                ph.Purchase_ID AS ID,
                ph.User_ID,
                ph.Product_Name,
                ph.Quantity,
                ph.Total_Price,
                ph.Order_Type,
                ph.Purchase_Date,
                ph.Product_Status,
                CONCAT(ui.First_Name, ' ', ui.Last_Name) AS User_Name,
                ph.Product_ID
              FROM tbl_purchase_history ph
              JOIN tbl_user_info ui ON ph.User_ID = ui.User_ID
              WHERE ph.Purchase_ID = :id 
              AND ph.Order_Type = :order_type
              AND ph.Product_Status = 100";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $purchaseID, PDO::PARAM_INT);
    $stmt->bindParam(':order_type', $orderType, PDO::PARAM_STR);
    $stmt->execute();
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$purchase) {
        header("Location: read-all-history-form.php?error=" . urlencode("Completed record not found for the specified ID and Type."));
        exit();
    }

} catch (PDOException $e) {
    error_log("DB Error fetching history details: " . $e->getMessage());
    header("Location: read-all-history-form.php?error=" . urlencode("Database error occurred."));
    exit();
}


// Product Status Map (Consistent labels)
$productStatusLabels = [
    0   => 'Request Approved / Concept',
    10  => 'Design Approved',
    20  => 'Material Sourcing / Prep',
    30  => 'Cutting & Shaping',
    40  => 'Structural Assembly',
    50  => 'Detailing & Refinements / Mid-Prod',
    60  => 'Sanding & Pre-Finishing',
    70  => 'Varnishing/Painting / Final Coat',
    80  => 'Drying & Curing / Assembly & Test',
    90  => 'Final Inspection / Ready',
    95  => 'Ready for Shipment',
    98  => 'Order Delivered',
    100 => 'Completed / Received / Sold' // Unified completed status label
];
// Get the status text based only on Product_Status
$productStatusText = $productStatusLabels[$purchase['Product_Status']] ?? 'Unknown Status (' . htmlspecialchars($purchase['Product_Status']) . ')';

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Purchase History Details</title> <!-- More specific title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        /* Style for profile dropdown */
        .profile-details .dropdown {
            display: none; /* Hidden by default */
            position: absolute;
            right: 0;
            top: 100%; /* Position below the profile details */
            background-color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 4px;
            overflow: hidden; /* Ensures border-radius applies to children */
            z-index: 1000; /* Ensure it's above other content */
            min-width: 120px; /* Optional: set a minimum width */
        }
        .profile-details .dropdown a {
            display: block;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            font-size: 0.9em;
        }
        .profile-details .dropdown a:hover {
            background-color: #f2f2f2;
        }
        .profile-details {
            position: relative; /* Needed for absolute positioning of dropdown */
            cursor: pointer; /* Indicate it's clickable */
        }
        .container_boxes table td:first-child { font-weight: bold; width: 25%; }
    </style>
</head>

<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE"  class="logo_image"> <!-- Use logo_image class -->
        </span>
    </div>
        <ul class="nav-links">
            <li><a href="../dashboard/dashboard.php"><i class="bx bx-grid-alt"></i><span class="links_name">Dashboard</span></a></li>
            <li><a href="../purchase-history/read-all-history-form.php" class="active"><i class="bx bx-history"></i><span class="links_name">All Purchase History</span></a></li>
            <li><a href="../reviews/read-all-reviews-form.php"><i class="bx bx-message-dots"></i><span class="links_name">All Reviews</span></a></li>
             <!-- Add other nav links as needed -->
        </ul>
    </div>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Purchase History Details</span> <!-- Updated title -->
            </div>
            <!-- Search box might not be needed on a 'read one' page -->
            <!-- <div class="search-box"> ... </div> -->

            <!-- Profile Details Container - Updated -->
            <div class="profile-details" onclick="toggleDropdown()">
                <img src="<?php echo $baseUrl . $profilePicPath; ?>" alt="Profile Picture" />
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
            <h4>Purchase History Details</h4>
            <?php if ($purchase): ?>
                <table class="table table-bordered table-striped"> <!-- Added Bootstrap table classes -->
                    <tr><td>Record ID:</td><td><?php echo htmlspecialchars($purchase['ID']); ?></td></tr>
                    <tr><td>Order Type:</td><td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $purchase['Order_Type']))); ?></td></tr>
                    <tr><td>User:</td><td><?php echo htmlspecialchars($purchase['User_Name']); ?> (ID: <?php echo htmlspecialchars($purchase['User_ID']); ?>)</td></tr>
                    <tr><td>Product:</td><td><?php echo htmlspecialchars($purchase['Product_Name']); ?> (Product ID: <?php echo htmlspecialchars($purchase['Product_ID'] ?? 'N/A'); ?>)</td></tr>
                    <?php if ($purchase['Order_Type'] !== 'custom'): // Show quantity only if not custom (where it might be irrelevant) ?>
                        <tr><td>Quantity:</td><td><?php echo htmlspecialchars($purchase['Quantity']); ?></td></tr>
                    <?php endif; ?>
                    <tr><td>Total Price:</td><td>₱ <?php echo number_format((float)$purchase['Total_Price'], 2); ?></td></tr>
                    <tr><td>Date Completed/Ordered:</td><td><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($purchase['Purchase_Date']))); ?></td></tr>
                    <tr><td>Status:</td><td><?php echo htmlspecialchars($productStatusText); ?></td></tr>
                    <!-- Removed the non-existent Order Status row -->
                </table>
            <?php else: ?>
                <div class="alert alert-warning">Could not load purchase details.</div>
            <?php endif; ?>

            <div class="button-container mt-3"> <!-- Added margin-top -->
                <a href="read-all-history-form.php" class="buttonBack btn btn-secondary">Back to History List</a>
                <!-- Update button is commented out as per original, but could link to relevant update form if needed -->
                <!-- <a href="update-history-form.php?id=<?php echo htmlspecialchars($purchase['ID']); ?>&order_type=<?php echo htmlspecialchars($purchase['Order_Type']); ?>" class="buttonUpdate btn btn-primary">Update Record</a> -->
                 <a href="delete-history-form.php?id=<?php echo htmlspecialchars($purchase['ID']); ?>&order_type=<?php echo htmlspecialchars($purchase['Order_Type']); ?>" class="buttonDelete btn btn-danger" style="margin-left: 10px;">Delete Record</a>
            </div>
        </div>
    </section>

    <script>
        // Sidebar Toggle (Consistent version)
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        if (sidebar && sidebarBtn) {
            sidebarBtn.onclick = function () {
                sidebar.classList.toggle("active");
                // Toggle icon class based on sidebar state
                sidebarBtn.classList.toggle("bx-menu-alt-right", sidebar.classList.contains("active"));
            };
        }

        // Profile Dropdown Toggle (Consistent version)
        const profileDetailsContainer = document.querySelector(".profile-details");
        const profileDropdown = document.getElementById('profileDropdown');
        const dropdownIcon = document.querySelector(".dropdown-button"); // Assuming you have an icon element

        if (profileDetailsContainer && profileDropdown) {
            profileDetailsContainer.addEventListener('click', function(event) {
                // Prevent dropdown from closing if clicking inside it, unless clicking a link
                if (!event.target.closest('a')) {
                     profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
                     // Toggle chevron icon if it exists
                     if (dropdownIcon) {
                         dropdownIcon.classList.toggle('bx-chevron-up', profileDropdown.style.display === 'block');
                     }
                }
                // Allow clicks on links within the dropdown to proceed
            });

            // Close dropdown if clicking outside
            document.addEventListener('click', function(event) {
                if (!profileDetailsContainer.contains(event.target)) {
                    profileDropdown.style.display = 'none';
                    // Reset chevron icon if it exists
                    if (dropdownIcon) {
                        dropdownIcon.classList.remove('bx-chevron-up');
                    }
                }
            });
        }
    </script>
</body>
</html>
