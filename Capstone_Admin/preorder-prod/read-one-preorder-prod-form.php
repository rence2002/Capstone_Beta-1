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


// Check if ID (Progress_ID) is passed in the URL
if (isset($_GET['id'])) {
    $progressID = $_GET['id']; // Expecting Progress_ID now

    try {
        // Fetch progress details for the specific pre-order
        // Querying tbl_progress now
        $query = "
            SELECT
                p.Progress_ID,
                p.User_ID,
                p.Product_ID,
                p.Product_Name,
                p.Quantity,
                p.Order_Type,
                p.Total_Price,
                p.Product_Status, -- Get Product_Status from tbl_progress
                p.Date_Added,     -- Use Date_Added from tbl_progress
                u.First_Name,
                u.Last_Name,
                pi.GLB_File_URL   -- Get GLB from tbl_prod_info
            FROM tbl_progress p
            JOIN tbl_user_info u ON p.User_ID = u.User_ID
            LEFT JOIN tbl_prod_info pi ON p.Product_ID = pi.Product_ID -- Join to get GLB
            WHERE p.Progress_ID = :progressID
              AND p.Order_Type = 'pre_order' -- Ensure it's a pre-order
        ";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':progressID', $progressID, PDO::PARAM_INT);
        $stmt->execute();
        $progressRecord = $stmt->fetch(PDO::FETCH_ASSOC); // Renamed variable

        if ($progressRecord) {
            // Assign data from $progressRecord
            $productID = htmlspecialchars($progressRecord['Product_ID']);
            $productName = htmlspecialchars($progressRecord['Product_Name']);
            $productStatusValue = $progressRecord['Product_Status']; // Get the status value
            $userID = htmlspecialchars($progressRecord['User_ID']);
            $userName = htmlspecialchars($progressRecord['First_Name'] . ' ' . $progressRecord['Last_Name']);
            $quantity = htmlspecialchars($progressRecord['Quantity']);
            $totalPrice = number_format((float)$progressRecord['Total_Price'], 2); // Format price
            $dateAdded = htmlspecialchars(date('F j, Y, g:i a', strtotime($progressRecord['Date_Added']))); // Format date
            $glbFileURL = !empty($progressRecord['GLB_File_URL']) ? htmlspecialchars($progressRecord['GLB_File_URL']) : null;

        } else {
            echo "Pre-order progress record not found.";
            exit();
        }
    } catch (PDOException $e) {
        error_log("Database Error fetching progress ID $progressID: " . $e->getMessage());
        echo "Database Error: Could not retrieve record details.";
        exit();
    }
} else {
    echo "No Progress ID provided.";
    exit();
}

// REMOVED the irrelevant $orderStatusMap

// Use the NEW Product Status labels provided in the prompt
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
    100 => 'Order Recieved', // Note: Typo 'Recieved' in provided map, kept as is. Should likely be 'Received'
];

// Get product status text using the new labels and the fetched status value
$productStatusText = $productStatusLabels[$productStatusValue] ?? 'Unknown Status';

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Pre-Order Details</title> <!-- Specific Title -->
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
        /* Minor adjustments for table readability */
        .table th { width: 25%; background-color: #f8f9fa; }
        .table td { width: 75%; }
        model-viewer { width: 100%; max-width: 400px; height: 300px; border: 1px solid #ccc; }
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
                <span class="dashboard">Pre-Order Details</span> <!-- Updated title -->
            </div>
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
            <!-- Updated Title -->
            <h4>PRE-ORDER DETAILS (Progress ID: <?= htmlspecialchars($progressID) ?>)</h4>
            <table class="table table-bordered table-striped"> <!-- Use Bootstrap classes -->
                <!-- Updated ID Label -->
                <tr><th>Progress ID:</th><td><?= htmlspecialchars($progressID) ?></td></tr>
                <tr><th>Product ID:</th><td><?= $productID ?></td></tr>
                <tr>
                    <th>3D Model:</th>
                    <td>
                        <?php if ($glbFileURL): ?>
                            <model-viewer src="<?= $glbFileURL ?>" alt="3D model of <?= $productName ?>" auto-rotate camera-controls></model-viewer>
                        <?php else: ?>
                            No 3D model available.
                        <?php endif; ?>
                    </td>
                </tr>
                <tr><th>Product Name:</th><td><?= $productName ?></td></tr>
                <tr><th>User ID:</th><td><?= $userID ?></td></tr>
                <tr><th>User Name:</th><td><?= $userName ?></td></tr>
                <tr><th>Quantity:</th><td><?= $quantity ?></td></tr>
                <tr><th>Total Price:</th><td>₱<?= $totalPrice ?></td></tr>
                <!-- Removed Preorder Status Row -->
                <!-- <tr><th>Preorder Status:</th><td><?= ''//$orderStatusText ?></td></tr> -->
                <tr><th>Product Status:</th><td><?= $productStatusValue ?>% - <?= $productStatusText ?></td></tr>
                <!-- Updated Date Label -->
                <tr><th>Date Added:</th><td><?= $dateAdded ?></td></tr>
            </table>

            <div class="button-container mt-3">
                <!-- Updated Back Link -->
                <a href="read-all-preorder-prod-form.php" class="buttonBack btn btn-secondary">Back to List</a>
                <!-- Updated Edit Link to point to preoder update form -->
                <a class="buttonEdit btn btn-warning" href="update-preorder-prod-form.php?id=<?php echo $progressID; ?>&order_type=pre_order">Edit Preorder</a>
            </div>
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
                if (!profileDropdown.contains(event.target)) {
                     profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
                     dropdownIcon.classList.toggle('bx-chevron-up');
                }
            });
            document.addEventListener('click', function(event) {
                if (!profileDetailsContainer.contains(event.target)) {
                    profileDropdown.style.display = 'none';
                    dropdownIcon.classList.remove('bx-chevron-up');
                }
            });
        }

        // Removed old dropdown toggle JS

    </script>
    <!-- Ensure model-viewer script is loaded -->
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
</body>
</html>
