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
// Construct the correct path relative to the web root if PicPath doesn't start with '../' or '/'
$profilePicPath = $admin['PicPath'];
if (!preg_match('/^(\.\.\/|\/)/', $profilePicPath)) {
    // Assuming PicPath is relative to the Capstone_Admin directory
    $profilePicPath = '../' . $profilePicPath;
}
$profilePicPath = htmlspecialchars($profilePicPath);


// Get the Purchase_ID and Order_Type from the URL
$purchaseID = $_GET['id'] ?? null;
$orderType = $_GET['order_type'] ?? null; // Get order type to potentially adjust query/display later if needed

if (!$purchaseID || !$orderType) {
    // Redirect back with an error if ID or type is missing
    header("Location: read-all-history-form.php?error=" . urlencode("Missing ID or Order Type."));
    exit();
}

$purchase = null; // Initialize purchase data variable

try {
    // --- Determine which table to query based on order_type ---
    // Although this page was originally for tbl_purchase_history,
    // the link from read-all-history-form passes different IDs.
    // We need to fetch from the correct source table.

    $query = "";
    $idParam = ':id'; // Parameter name for the ID

    switch ($orderType) {
        case 'custom':
            // Fetch from tbl_customizations where Product_Status = 100
            $query = "SELECT
                        c.Customization_ID AS ID,
                        c.User_ID,
                        c.Furniture_Type AS Product_Name,
                        0 AS Quantity, -- Quantity not directly stored here, maybe fetch from progress? Defaulting to 0
                        p.Price AS Total_Price, -- Price might be 0.00, consider calculated price if available
                        'custom' AS Order_Type,
                        c.Last_Update AS Purchase_Date, -- Use Last_Update as the date
                        c.Product_Status,
                        CONCAT(ui.First_Name, ' ', ui.Last_Name) AS User_Name,
                        c.Product_ID
                      FROM tbl_customizations c
                      JOIN tbl_user_info ui ON c.User_ID = ui.User_ID
                      LEFT JOIN tbl_prod_info p ON c.Product_ID = p.Product_ID
                      WHERE c.Customization_ID = $idParam AND c.Product_Status = 100"; // Ensure it's completed
            break;

        case 'pre_order':
            // Fetch from tbl_preorder where Product_Status = 100
            $query = "SELECT
                        po.Preorder_ID AS ID,
                        po.User_ID,
                        pr.Product_Name,
                        po.Quantity,
                        po.Total_Price,
                        'pre_order' AS Order_Type,
                        po.Order_Date AS Purchase_Date, -- Use Order_Date
                        po.Product_Status,
                        CONCAT(ui.First_Name, ' ', ui.Last_Name) AS User_Name,
                        po.Product_ID
                      FROM tbl_preorder po
                      JOIN tbl_user_info ui ON po.User_ID = ui.User_ID
                      JOIN tbl_prod_info pr ON po.Product_ID = pr.Product_ID
                      WHERE po.Preorder_ID = $idParam AND po.Product_Status = 100"; // Ensure it's completed
            break;

        case 'ready_made':
             // Fetch from tbl_ready_made_orders where Product_Status = 100
             // NOTE: tbl_purchase_history seems redundant if tbl_ready_made_orders holds the final state.
             // We'll query tbl_ready_made_orders for consistency with the other types.
            $query = "SELECT
                        rmo.ReadyMadeOrder_ID AS ID,
                        rmo.User_ID,
                        pr.Product_Name,
                        rmo.Quantity,
                        rmo.Total_Price,
                        'ready_made' AS Order_Type,
                        rmo.Order_Date AS Purchase_Date, -- Use Order_Date
                        rmo.Product_Status,
                        CONCAT(ui.First_Name, ' ', ui.Last_Name) AS User_Name,
                        rmo.Product_ID
                      FROM tbl_ready_made_orders rmo
                      JOIN tbl_user_info ui ON rmo.User_ID = ui.User_ID
                      JOIN tbl_prod_info pr ON rmo.Product_ID = pr.Product_ID
                      WHERE rmo.ReadyMadeOrder_ID = $idParam AND rmo.Product_Status = 100"; // Ensure it's completed
            break;

        default:
            // Invalid order type
            header("Location: read-all-history-form.php?error=" . urlencode("Invalid Order Type specified."));
            exit();
    }


    $stmt = $pdo->prepare($query);
    // Bind the ID based on the parameter name used in the query
    $stmt->bindParam($idParam, $purchaseID, PDO::PARAM_INT);
    $stmt->execute();
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$purchase) {
        // Redirect back with an error if record not found or not completed
        header("Location: read-all-history-form.php?error=" . urlencode("Completed record not found for the specified ID and Type."));
        exit();
    }

} catch (PDOException $e) {
    // Handle database errors
    echo "Database Error: " . $e->getMessage();
    // Log error: error_log("DB Error fetching history details: " . $e->getMessage());
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
            <div class="profile-details" id="profile-details-container">
                <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button" id="dropdown-icon"></i>
                <!-- Dropdown Menu -->
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
        const profileDetailsContainer = document.getElementById('profile-details-container');
        const profileDropdown = document.getElementById('profileDropdown');
        const dropdownIcon = document.getElementById('dropdown-icon'); // Assuming you have an icon element

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
