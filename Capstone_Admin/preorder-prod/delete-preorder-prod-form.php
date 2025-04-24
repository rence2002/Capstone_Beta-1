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


// Check if the ID (Progress_ID) is provided via GET and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $progressID = (int)$_GET['id'];

    try {
        // Fetch the specific progress record to confirm deletion
        $query = "
            SELECT
                p.Progress_ID,
                CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
                p.Product_Name,
                p.Order_Type,
                p.Product_Status,
                p.Total_Price,
                p.Date_Added
            FROM tbl_progress p
            JOIN tbl_user_info u ON p.User_ID = u.User_ID
            WHERE p.Progress_ID = :progressID
              AND p.Order_Type = 'pre_order' -- Ensure it's actually a pre-order record
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':progressID', $progressID, PDO::PARAM_INT);
        $stmt->execute();
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            // Record not found or not a pre-order
            header("Location: read-all-preorder-prod-form.php?warning=notfound");
            exit();
        }

        // Use the NEW Product Status labels for display
        $productStatusLabels = [
            0   => 'Request Approved', 10  => 'Design Approved', 20  => 'Material Sourcing',
            30  => 'Cutting & Shaping', 40  => 'Structural Assembly', 50  => 'Detailing & Refinements',
            60  => 'Sanding & Pre-Finishing', 70  => 'Varnishing/Painting', 80  => 'Drying & Curing',
            90  => 'Final Inspection & Packaging', 95  => 'Ready for Shipment', 98  => 'Order Delivered',
            100 => 'Order Recieved',
        ];
        $productStatusText = htmlspecialchars($productStatusLabels[$record['Product_Status']] ?? 'Unknown');


    } catch (PDOException $e) {
        error_log("Database Error fetching record for deletion (ID: $progressID): " . $e->getMessage());
        die("Database Error: Could not retrieve record details. Please check logs.");
    }
} else {
    // Invalid or missing ID
    header("Location: read-all-preorder-prod-form.php?error=invalid_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Confirm Delete Pre-Order</title> <!-- Specific Title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        .confirmation-box {
            border: 1px solid #dc3545;
            padding: 20px;
            border-radius: 5px;
            background-color: #f8d7da;
        }
        .table th { width: 30%; }
        .table td { width: 70%; }
    </style>
</head>

<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE" class="logo_image">
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
                <span class="dashboard">Confirm Delete Pre-Order</span> <!-- Updated title -->
            </div>
            <div class="profile-details" id="profile-details-container">
                <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button" id="dropdown-icon"></i>
                <div class="dropdown" id="profileDropdown">
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>
        </nav>
        <br><br><br>

        <div class="container_boxes">
            <div class="confirmation-box">
                <h4 class="text-danger">Confirm Deletion</h4>
                <p>Are you sure you want to permanently delete the following pre-order progress record?</p>

                <table class="table table-bordered bg-white">
                    <tr><th>Progress ID</th><td><?= htmlspecialchars($record['Progress_ID']) ?></td></tr>
                    <tr><th>User Name</th><td><?= htmlspecialchars($record['User_Name']) ?></td></tr>
                    <tr><th>Product Name</th><td><?= htmlspecialchars($record['Product_Name']) ?></td></tr>
                    <tr><th>Total Price</th><td>₱<?= number_format((float)$record['Total_Price'], 2) ?></td></tr>
                    <tr><th>Current Status</th><td><?= $record['Product_Status'] ?>% - <?= $productStatusText ?></td></tr>
                    <tr><th>Date Added</th><td><?= htmlspecialchars(date('F j, Y, g:i a', strtotime($record['Date_Added']))) ?></td></tr>
                </table>

                <form action="delete-preorder-prod-rec.php" method="POST" style="display: inline;">
                    <!-- Pass the Progress_ID to the processing script -->
                    <input type="hidden" name="Progress_ID" value="<?= htmlspecialchars($record['Progress_ID']) ?>">
                    <button type="submit" class="buttonDelete btn btn-danger">Yes, Delete Record</button>
                </form>
                <a href="read-all-preorder-prod-form.php" class="buttonBack btn btn-secondary" style="margin-left: 10px;">No, Cancel</a>
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
                sidebarBtn.classList.toggle("bx-menu-alt-right");
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
    </script>
</body>
</html>
