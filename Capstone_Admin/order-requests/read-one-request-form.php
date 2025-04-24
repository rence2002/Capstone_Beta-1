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
        // Fetch order request details
        // Removed orq.Order_Status, added orq.Payment_Status, orq.Processed
        $query = "
            SELECT
                orq.Request_ID,
                orq.User_ID,
                orq.Product_ID,
                orq.Quantity,
                orq.Order_Type,
                orq.Total_Price,
                orq.Payment_Status, -- Added
                orq.Request_Date,
                orq.Processed,      -- Added
                u.First_Name,
                u.Last_Name,
                p.Product_Name,
                p.Category AS Furniture_Type, -- Use Category as Furniture Type for consistency
                p.GLB_File_URL
                -- Removed orq.Order_Status AS Request_Status
            FROM tbl_order_request orq
            JOIN tbl_user_info u ON orq.User_ID = u.User_ID
            LEFT JOIN tbl_prod_info p ON orq.Product_ID = p.Product_ID -- Use LEFT JOIN in case product was deleted
            WHERE orq.Request_ID = :requestID
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
        $stmt->execute();
        $orderRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($orderRequest) {
            // Assign fetched data to variables with htmlspecialchars
            $userName = htmlspecialchars($orderRequest['First_Name'] . ' ' . $orderRequest['Last_Name']);
            $productName = htmlspecialchars($orderRequest['Product_Name'] ?? 'N/A'); // Handle potential missing product
            $furnitureType = htmlspecialchars($orderRequest['Furniture_Type'] ?? 'N/A');
            $quantity = htmlspecialchars($orderRequest['Quantity']);
            $orderType = htmlspecialchars($orderRequest['Order_Type']);
            $paymentStatus = htmlspecialchars($orderRequest['Payment_Status']); // Get actual payment status
            $processedStatus = $orderRequest['Processed'] == 0 ? 'Pending Confirmation' : 'Processed'; // Determine processing status text
            $totalPrice = number_format((float)($orderRequest['Total_Price'] ?? 0), 2); // Format price
            $requestDate = htmlspecialchars(date('F j, Y, g:i a', strtotime($orderRequest['Request_Date']))); // Format date
            $glbFileURL = !empty($orderRequest['GLB_File_URL']) ? htmlspecialchars($orderRequest['GLB_File_URL']) : null; // Check if URL exists

        } else {
            echo "Order request not found.";
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

// REMOVED $statusLabels array - It was incorrect for this context.
// The $productStatusLabels provided are for tbl_progress, not tbl_order_request.
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Request Details</title> <!-- Specific Title -->
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
                <span class="dashboard">Order Request Details</span> <!-- Updated title -->
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
            <h4>ORDER REQUEST DETAILS (ID: <?= htmlspecialchars($requestID) ?>)</h4>
            <table class="table table-bordered table-striped"> <!-- Use Bootstrap classes -->
                <tr><th>User Name</th><td><?= $userName ?></td></tr>
                <tr><th>Product Name</th><td><?= $productName ?></td></tr>
                <tr>
                    <th>3D Model</th>
                    <td>
                        <?php if ($glbFileURL): ?>
                            <model-viewer src="<?= $glbFileURL ?>" alt="3D model of <?= $productName ?>" auto-rotate camera-controls></model-viewer>
                        <?php else: ?>
                            No 3D model available.
                        <?php endif; ?>
                    </td>
                </tr>
                <tr><th>Furniture Type</th><td><?= $furnitureType ?></td></tr>
                <tr><th>Quantity</th><td><?= $quantity ?></td></tr>
                <tr><th>Order Type</th><td><?= ucwords(str_replace('_', ' ', $orderType)) ?></td></tr>
                <!-- Updated Status Display -->
                <tr><th>Processing Status</th><td><?= $processedStatus ?></td></tr>
                <tr><th>Payment Status</th><td><?= ucwords(str_replace('_', ' ', $paymentStatus)) ?></td></tr>
                <!-- Removed old incorrect status row -->
                <!-- <tr><th>STATUS</th><td><?php // echo $statusLabels[$status] ?? 'Unknown'; ?></td></tr> -->
                <tr><th>Total Price</th><td>₱ <?= $totalPrice ?></td></tr>
                <tr><th>Request Date</th><td><?= $requestDate ?></td></tr>
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
    <!-- Ensure model-viewer script is loaded -->
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
</body>
</html>
