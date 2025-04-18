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
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Get the ID and order type from the URL
if (!isset($_GET['id']) || !isset($_GET['order_type'])) {
    echo "Progress ID and Order Type not specified.";
    exit();
}

$id = $_GET['id'];
$orderType = $_GET['order_type'];

// Determine the correct query based on the order type
$query = "";
switch ($orderType) {
    case 'custom':
        $query = "
    SELECT
        p.Progress_ID AS ID,
        pr.Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        p.Order_Status,
        p.Product_Status,
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
        p.User_ID,
        p.Product_ID
    FROM tbl_progress p
    JOIN tbl_user_info u ON p.User_ID = u.User_ID
    LEFT JOIN tbl_prod_info pr ON p.Product_ID = pr.Product_ID
    WHERE p.Progress_ID = :id
";
        break;

    case 'pre_order':
        $query = "
            SELECT
                p.Progress_ID AS ID,
                pr.Product_Name,
                CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
                p.Order_Status,
                p.Product_Status,
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
                p.User_ID,
                p.Product_ID
            FROM tbl_progress p
            JOIN tbl_user_info u ON p.User_ID = u.User_ID
            JOIN tbl_prod_info pr ON p.Product_ID = pr.Product_ID
            WHERE p.Progress_ID = :id AND p.Order_Type = 'pre_order'
        ";
        break;

    case 'ready_made':
        $query = "
            SELECT
                p.Progress_ID AS ID,
                pr.Product_Name,
                CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
                p.Order_Status,
                p.Product_Status,
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
                p.User_ID,
                p.Product_ID
            FROM tbl_progress p
            JOIN tbl_user_info u ON p.User_ID = u.User_ID
            JOIN tbl_prod_info pr ON p.Product_ID = pr.Product_ID
            WHERE p.Progress_ID = :id AND p.Order_Type = 'ready_made'
        ";
        break;
    default:
        echo "Invalid order type.";
        exit;
}

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "Progress record not found.";
    exit();
}

// Map order status to descriptive text
$orderStatusMap = [
    0   => 'Order Received',       // 0% - Order placed by the customer
    10  => 'Order Confirmed',      // 10% - Down payment received
    20  => 'Design Finalization',  // 20% - Final design confirmed
    30  => 'Material Preparation', // 30% - Sourcing and cutting materials
    40  => 'Production Started',   // 40% - Carpentry/assembly in progress
    50  => 'Mid-Production',       // 50% - Major structural work completed
    60  => 'Finishing Process',    // 60% - Upholstery, varnishing, detailing
    70  => 'Quality Check',        // 70% - Inspection for defects
    80  => 'Final Assembly',       // 80% - Last touches, packaging
    90  => 'Ready for Delivery',   // 90% - Scheduled for transport
    100 => 'Delivered / Completed' // 100% - Customer has received the furniture
];

// Map product status to descriptive text
$productStatusLabels = [
    0   => 'Concept Stage',         // 0% - Idea or design submitted
    10  => 'Design Approved',       // 10% - Finalized by customer
    20  => 'Material Sourcing',     // 20% - Gathering necessary materials
    30  => 'Cutting & Shaping',     // 30% - Preparing materials
    40  => 'Structural Assembly',   // 40% - Base framework built
    50  => 'Detailing & Refinements', // 50% - Carvings, upholstery, elements added
    60  => 'Sanding & Pre-Finishing', // 60% - Smoothening, preparing for final coat
    70  => 'Varnishing/Painting',   // 70% - Applying the final finish
    80  => 'Drying & Curing',       // 80% - Final coating sets in
    90  => 'Final Inspection & Packaging', // 90% - Quality control before handover
    100 => 'Completed'              // 100% - Ready for pickup/delivery
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




</nav>
<br><br><br>
<div class="container_boxes">
    <h4>UPDATE PROGRESS</h4>
    <form action="update-progress-rec.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="Progress_ID" value="<?= htmlspecialchars($row['ID']) ?>">
        <input type="hidden" name="Order_Type" value="<?= htmlspecialchars($orderType) ?>">
        <input type="hidden" name="Product_ID" value="<?= htmlspecialchars($row['Product_ID']) ?>">
        <table class="table table-bordered">
            <tr><th>User</th><td><input type="text" class="form-control" value="<?= htmlspecialchars($row['User_Name']) ?>" readonly></td></tr>
            <tr><th>Product Name</th><td><input type="text" class="form-control" value="<?= htmlspecialchars($row['Product_Name']) ?>" readonly></td></tr>
            <tr>
                <th>Order Status</th>
                <td>
                    <select name="Order_Status" class="form-control" required>
                        <?php foreach ($orderStatusMap as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $row['Order_Status'] == $key ? 'selected' : '' ?>>
                                <?= "$key% - $value" ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr id="tracking-number-row" style="display: none;">
    <th>Tracking Number</th>
    <td colspan="2">
        <input type="text" name="Tracking_Number" id="tracking-number" class="form-control" placeholder="Enter Tracking Number">
    </td>
</tr>
            <tr>
                <th>Product Status</th>
                <td>
                    <select name="Product_Status" class="form-control" required>
                        <?php foreach ($productStatusLabels as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $row['Product_Status'] == $key ? 'selected' : '' ?>>
                                <?= "$key% - $value" ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Stop Reason</th>
                <td>
                    <select name="Stop_Reason" class="form-control">
                        <option value="">None</option>
                        <option value="fire" <?= $row['Stop_Reason'] === 'fire' ? 'selected' : '' ?>>Fire</option>
                        <option value="flood" <?= $row['Stop_Reason'] === 'flood' ? 'selected' : '' ?>>Flood</option>
                        <option value="typhoon" <?= $row['Stop_Reason'] === 'typhoon' ? 'selected' : '' ?>>Typhoon</option>
                        <option value="earthquake" <?= $row['Stop_Reason'] === 'earthquake' ? 'selected' : '' ?>>Earthquake</option>
                    </select>
                </td>
            </tr>
        </table>
        
        <h3>Progress Pictures</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Progress</th>
                    <th>Upload Image</th>
                    <th>Current Image</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $percentage): ?>
                    <?php $picKey = "Progress_Pic_$percentage"; ?>
                    <tr>
                        <td><strong><?= $percentage ?>%</strong></td>
                        <td><input type="file" name="Progress_Pic_<?= $percentage ?>" class="form-control-file"></td>
                        <td>
                            <?php if (!empty($row[$picKey])): ?>
                                <img src="../uploads/progress_pics/<?= htmlspecialchars(basename($row[$picKey])) ?>" 
                                     alt="Progress <?= $percentage ?>%" 
                                     class="img-thumbnail" 
                                     style="max-width: 100px;">
                            <?php else: ?>
                                <span>No image uploaded</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

            
        </table>
        
        <div class="button-container">
        <a href="read-all-progress-form.php" class="buttonBack">Back to List</a>
            <button type="submit" class="buttonUpdate">Update Progress</button>
        </div>
    </form>
</div>

    <script>
        function showProgressPic(picUrl) {
            const picRow = document.getElementById('progress-pic-row');
            const picImg = document.getElementById('progress-pic');
            if (picUrl) {
                picImg.src = picUrl;
                picRow.style.display = 'table-row';
            } else {
                picRow.style.display = 'none';
            }
        }

        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        sidebarBtn.onclick = function () {
            sidebar.classList.toggle("active");
            if (sidebar.classList.contains("active")) {
                sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
        };

        document.querySelectorAll('.dropdown-toggle').forEach((toggle) => {
            toggle.addEventListener('click', function () {
                const parent = this.parentElement;
                const dropdownMenu = parent.querySelector('.dropdown-menu');
                parent.classList.toggle('active');

                const chevron = this.querySelector('i');
                if (parent.classList.contains('active')) {
                    chevron.classList.remove('bx-chevron-down');
                    chevron.classList.add('bx-chevron-up');
                } else {
                    chevron.classList.remove('bx-chevron-up');
                    chevron.classList.add('bx-chevron-down');
                }

                dropdownMenu.style.display = parent.classList.contains('active') ? 'block' : 'none';
            });
        });
    </script>

<script>
    // Function to toggle the tracking number input field
    function toggleTrackingNumber() {
        const orderStatusSelect = document.querySelector('select[name="Order_Status"]');
        const trackingNumberRow = document.getElementById('tracking-number-row');
        const trackingNumberInput = document.getElementById('tracking-number');

        // Show the tracking number input if the selected status is "Ready for Delivery" (90%)
        if (orderStatusSelect.value == 90) {
            trackingNumberRow.style.display = 'table-row';
            trackingNumberInput.required = true; // Make it required
        } else {
            trackingNumberRow.style.display = 'none';
            trackingNumberInput.required = false; // Remove the required attribute
        }
    }

    // Attach the function to the Order Status dropdown
    document.querySelector('select[name="Order_Status"]').addEventListener('change', toggleTrackingNumber);

    // Call the function on page load to handle pre-selected values
    toggleTrackingNumber();
</script>
</body>
</html>
