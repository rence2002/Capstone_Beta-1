<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
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
// Construct the correct path relative to the web root if PicPath doesn't start with '../' or '/'
$profilePicPath = $admin['PicPath'];
if (!preg_match('/^(\.\.\/|\/)/', $profilePicPath)) {
    // Assuming PicPath is relative to the Capstone_Admin directory
    $profilePicPath = '../' . $profilePicPath;
}
$profilePicPath = htmlspecialchars($profilePicPath);


// Get the Progress_ID from the URL
if (!isset($_GET['id'])) {
    echo "Progress ID not specified.";
    exit();
}

$id = $_GET['id'];

// Query to fetch progress record directly from tbl_progress
// Removed Order_Status, removed join to tbl_prod_info, added Quantity, selected p.Product_Name
$query = "
    SELECT
        p.Progress_ID AS ID,
        p.Product_Name, -- Fetched directly from tbl_progress
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        p.Order_Type,
        -- p.Order_Status, -- REMOVED
        p.Product_Status,
        p.Quantity, -- Added Quantity
        p.Total_Price,
        p.Date_Added AS Request_Date,
        p.LastUpdate AS Last_Update,
        p.Progress_Pic_10,
        p.Progress_Pic_20,
        p.Progress_Pic_30,
        p.Progress_Pic_40,
        p.Progress_Pic_50,
        p.Progress_Pic_60,
        p.Progress_Pic_70,
        p.Progress_Pic_80,
        p.Progress_Pic_90,
        p.Progress_Pic_100,
        p.Stop_Reason,
        p.Tracking_Number
    FROM tbl_progress p
    JOIN tbl_user_info u ON p.User_ID = u.User_ID
    -- JOIN tbl_prod_info pr ON p.Product_ID = pr.Product_ID -- REMOVED JOIN
    WHERE p.Progress_ID = :id
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "Record not found.";
    exit();
}

// REMOVED Order Status Map
// $orderStatusLabels = [ ... ];

// Updated Product Status Map (using the new one from the prompt)
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
    100 => 'Order Recieved', 
];
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Progress Details</title> <!-- More specific title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle for Popper -->
    <!-- Removed dashboard.js as it might conflict or be redundant -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        /* Add some spacing for better readability */
        .table th { width: 25%; }
        .table td { width: 75%; }
        .progress-img { max-width: 150px; height: auto; } /* Slightly larger images */
        /* Style for completed status */
        td.completed-action { color: #777; font-style: italic; }
    </style>
</head>

<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE" class="logo_image"> <!-- Use a class for logo image -->
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
                  <i class="bx bx-history"></i> <!-- Changed icon to history -->
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
                <span class="dashboard">Progress Details</span> <!-- Updated title -->
            </div>
            <!-- Removed search box as it's not typical for a 'read one' page -->
            <div class="profile-details" id="profile-details-container"> <!-- Added ID for JS -->
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
            <h4>PROGRESS DETAILS FOR ID: <?= htmlspecialchars($row['ID']) ?></h4>

            <table class="table table-bordered table-striped"> <!-- Added striped class -->
                <tr><th>User Name</th><td><?= htmlspecialchars($row['User_Name']) ?></td></tr>
                <tr><th>Product Name</th><td><?= htmlspecialchars($row['Product_Name']) ?></td></tr>
                <tr><th>Order Type</th><td><?= htmlspecialchars($row['Order_Type']) ?></td></tr>
                <tr><th>Quantity</th><td><?= htmlspecialchars($row['Quantity']) ?></td></tr> <!-- Display Quantity -->
                <!-- <tr><th>Order Status</th><td><?= $row['Order_Status'] ?>% - <?= $orderStatusLabels[$row['Order_Status']] ?? 'Unknown Status' ?></td></tr> --> <!-- REMOVED Order Status Row -->
                <?php if (!empty($row['Tracking_Number'])): ?>
                    <tr><th>Tracking Number</th><td><?= htmlspecialchars($row['Tracking_Number']) ?></td></tr>
                <?php endif; ?>
                <tr><th>Product Status</th><td><?= $row['Product_Status'] ?>% - <?= $productStatusLabels[$row['Product_Status']] ?? 'Unknown Status' ?></td></tr> <!-- Uses NEW labels -->
                <tr><th>Total Price</th><td>₱ <?= number_format((float)$row['Total_Price'], 2, '.', ',') ?></td></tr> <!-- Formatted Price -->
                <tr><th>Request Date</th><td><?= htmlspecialchars(date('F j, Y, g:i a', strtotime($row['Request_Date']))) ?></td></tr> <!-- Formatted Date -->
                <tr><th>Last Update</th><td><?= htmlspecialchars(date('F j, Y, g:i a', strtotime($row['Last_Update']))) ?></td></tr> <!-- Formatted Date -->
                <tr><th>Stop Reason</th><td><?= htmlspecialchars($row['Stop_Reason'] ?? 'N/A') ?></td></tr>
            </table>

            <h3>Progress Pictures</h3>
            <?php
                $hasPictures = false;
                foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $percentage) {
                    if (!empty($row["Progress_Pic_$percentage"])) {
                        $hasPictures = true;
                        break;
                    }
                }
            ?>

            <?php if ($hasPictures): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Progress</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $percentage): ?>
                            <?php $picKey = "Progress_Pic_$percentage"; ?>
                            <?php if (!empty($row[$picKey])): ?>
                                <?php
                                    // Construct the correct image path relative to the web root
                                    $imagePath = $row[$picKey];
                                    // Assuming paths are stored like '../uploads/progress_pics/...'
                                    // If the script is in Capstone_Admin/progress/, then '../' goes up to Capstone_Admin/
                                    // So the path should be correct relative to the script location.
                                    // If paths are stored differently (e.g., absolute from web root), adjust here.
                                    $imageDisplayPath = htmlspecialchars($imagePath);
                                ?>
                                <tr>
                                    <td><strong><?= $percentage ?>%</strong></td>
                                    <td><img src="<?= $imageDisplayPath ?>" alt="Progress <?= $percentage ?>%" class="progress-img img-thumbnail"></td> <!-- Added img-thumbnail -->
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No progress pictures have been uploaded yet.</p>
            <?php endif; ?>

            <div class="button-container mt-3"> <!-- Added margin top -->
                <a href="read-all-progress-form.php" class="buttonBack btn btn-secondary">Back to List</a>
                <?php // Only show Edit button if the product status is not completed (status < 100)
                if ($row['Product_Status'] < 100): ?>
                    <a class="buttonEdit btn btn-warning" href="update-progress-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>">Edit</a>
                <?php else: ?>
                    <span class="completed-action ms-2">(Order Completed)</span> <!-- Indicate completed status if edit is hidden -->
                <?php endif; ?>
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

        // Profile Dropdown Toggle
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

        // Note: Removed showProgressPic function as it wasn't used in the final HTML structure.
        // Note: Removed old dropdown-toggle related JS.
    </script>
</body>
</html>
