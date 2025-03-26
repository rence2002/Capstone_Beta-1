<?php
session_start();

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

// Check if admin data is fetched
if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Get the Progress_ID and order type from the URL
if (!isset($_GET['id']) || !isset($_GET['order_type'])) {
    echo "Progress ID and Order Type not specified.";
    exit();
}

$id = $_GET['id'];
$orderType = $_GET['order_type'];
// Determine the correct query based on the order type
switch ($orderType) {
    case 'custom':
        $query = "
            SELECT
                c.Customization_ID AS ID,
                c.Furniture_Type AS Product_Name,
                u.User_ID,
                CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
                c.Order_Status,
                c.Product_Status,
                0.00 as Total_Price,
                c.Request_Date,
                c.Last_Update
            FROM tbl_customizations c
            JOIN tbl_user_info u ON c.User_ID = u.User_ID
            WHERE c.Customization_ID = :id
        ";
        break;
    case 'pre_order':
        $query = "
            SELECT
                po.Preorder_ID AS ID,
                pr.Product_Name,
                u.User_ID,
                CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
                po.Preorder_Status AS Order_Status,
                po.Product_Status,
                po.Total_Price,
                po.Order_Date as Request_Date,
                po.Order_Date AS Last_Update
            FROM tbl_preorder po
            JOIN tbl_user_info u ON po.User_ID = u.User_ID
            JOIN tbl_prod_info pr ON po.Product_ID = pr.Product_ID
            WHERE po.Preorder_ID = :id
        ";
        break;
    case 'ready_made':
        $query = "
            SELECT
                rmo.ReadyMadeOrder_ID AS ID,
                pr.Product_Name,
                u.User_ID,
                CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
                rmo.Order_Status,
                rmo.Product_Status,
                rmo.Total_Price,
                rmo.Order_Date as Request_Date,
                rmo.Order_Date AS Last_Update
            FROM tbl_ready_made_orders rmo
            JOIN tbl_user_info u ON rmo.User_ID = u.User_ID
            JOIN tbl_prod_info pr ON rmo.Product_ID = pr.Product_ID
            WHERE rmo.ReadyMadeOrder_ID = :id
        ";
        break;
    default:
        echo "Invalid order type.";
        exit();
}

$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$progress = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$progress) {
    echo "Progress record not found.";
    exit();
}
// Order Status Map
$orderStatusLabels = [
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
    100 => 'Delivered / Completed',
];

// Product Status Map
$productStatusLabels = [
    0 => 'Concept Stage',
    10 => 'Design Approved',
    20 => 'Material Sourcing',
    30 => 'Cutting & Shaping',
    40 => 'Structural Assembly',
    50 => 'Detailing & Refinements',
    60 => 'Sanding & Pre-Finishing',
    70 => 'Final Coating',
    80 => 'Assembly & Testing',
    90 => 'Ready for Delivery',
    100 => 'Sold / Installed',
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
                <a href="../dashboard/dashboard.php" class="active">
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
        <div class="container mt-5">
            <h2>Update Progress Record</h2>
            <form action="update-progress-rec.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="Progress_ID" value="<?= htmlspecialchars($progress['ID']) ?>">
                <input type="hidden" name="Order_Type" value="<?= htmlspecialchars($orderType) ?>">
                <input type="hidden" name="Product_Name" value="<?= htmlspecialchars($progress['Product_Name']) ?>">

                <div class="form-group">
                    <label >User:</label>
                   <input type="text" class="form-control" value="<?= htmlspecialchars($progress['User_Name']) ?>" readonly>
                    <input type="hidden" name="User_ID" value="<?= htmlspecialchars($progress['User_ID']) ?>">
                </div>
                <div class="form-group">
                    <label >Product Name:</label>
                   <input type="text" class="form-control" value="<?= htmlspecialchars($progress['Product_Name']) ?>" readonly>
                    <input type="hidden" name="Product_ID" value="<?= htmlspecialchars($progress['Product_ID'] ?? '') ?>">
                </div>
               <div class="form-group">
                    <label >Order Type:</label>
                   <input type="text" class="form-control" value="<?= htmlspecialchars($orderType) ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="Order_Status">Order Status</label>
                    <select name="Order_Status" id="Order_Status" class="form-control" required>
                        <?php foreach ($orderStatusLabels as $key => $value): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= $progress['Order_Status'] == $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($key . '% - ' . $value) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="Product_Status">Product Status</label>
                    <select name="Product_Status" id="Product_Status" class="form-control" required>
                        <?php foreach ($productStatusLabels as $key => $value): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= $progress['Product_Status'] == $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($key . '% - ' . $value) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="Total_Price">Total Price</label>
                    <input type="text" name="Total_Price" id="Total_Price" class="form-control" value="<?= htmlspecialchars(number_format((float)$progress['Total_Price'], 2, '.', '')) ?>" readonly>
                </div>

                <div class="form-group" id="progress-pic-10">
                    <label for="Progress_Pic_10">Progress Picture 10%</label>
                    <input type="file" name="Progress_Pic_10" id="Progress_Pic_10" class="form-control">
                </div>
                <div class="form-group" id="progress-pic-20">
                    <label for="Progress_Pic_20">Progress Picture 20%</label>
                    <input type="file" name="Progress_Pic_20" id="Progress_Pic_20" class="form-control">
                </div>
                <div class="form-group" id="progress-pic-30">
                    <label for="Progress_Pic_30">Progress Picture 30%</label>
                    <input type="file" name="Progress_Pic_30" id="Progress_Pic_30" class="form-control">
                </div>
                <div class="form-group" id="progress-pic-40">
                    <label for="Progress_Pic_40">Progress Picture 40%</label>
                    <input type="file" name="Progress_Pic_40" id="Progress_Pic_40" class="form-control">
                </div>
                <div class="form-group" id="progress-pic-50">
                    <label for="Progress_Pic_50">Progress Picture 50%</label>
                    <input type="file" name="Progress_Pic_50" id="Progress_Pic_50" class="form-control">
                </div>
                <div class="form-group" id="progress-pic-60">
                    <label for="Progress_Pic_60">Progress Picture 60%</label>
                    <input type="file" name="Progress_Pic_60" id="Progress_Pic_60" class="form-control">
                </div>
                <div class="form-group" id="progress-pic-70">
                    <label for="Progress_Pic_70">Progress Picture 70%</label>
                    <input type="file" name="Progress_Pic_70" id="Progress_Pic_70" class="form-control">
                </div>
                <div class="form-group" id="progress-pic-80">
                    <label for="Progress_Pic_80">Progress Picture 80%</label>
                    <input type="file" name="Progress_Pic_80" id="Progress_Pic_80" class="form-control">
                </div>
                <div class="form-group" id="progress-pic-90">
                    <label for="Progress_Pic_90">Progress Picture 90%</label>
                    <input type="file" name="Progress_Pic_90" id="Progress_Pic_90" class="form-control">
                </div>
                <div class="form-group" id="progress-pic-100">
                    <label for="Progress_Pic_100">Progress Picture 100%</label>
                    <input type="file" name="Progress_Pic_100" id="Progress_Pic_100" class="form-control">
                </div>

                <div class="form-group">
                    <label for="Stop_Reason">Stop Progress Reason</label>
                    <select name="Stop_Reason" id="Stop_Reason" class="form-control">
                        <option value="">Select Reason</option>
                        <option value="none">None</option>
                        <option value="fire">Fire</option>
                        <option value="flood">Flood</option>
                        <option value="typhoon">Typhoon</option>
                        <option value="earthquake">Earthquake</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="read-all-progress-form.php" class="btn btn-secondary">Back to List</a>
            </form>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const productStatusSelect = document.getElementById('Product_Status');
        const progressPicFields = {
            10: document.getElementById('progress-pic-10'),
            20: document.getElementById('progress-pic-20'),
            30: document.getElementById('progress-pic-30'),
            40: document.getElementById('progress-pic-40'),
            50: document.getElementById('progress-pic-50'),
            60: document.getElementById('progress-pic-60'),
            70: document.getElementById('progress-pic-70'),
            80: document.getElementById('progress-pic-80'),
            90: document.getElementById('progress-pic-90'),
            100: document.getElementById('progress-pic-100')
        };

        function updateProgressPicFields() {
            const selectedStatus = parseInt(productStatusSelect.value);
            for (const [status, field] of Object.entries(progressPicFields)) {
                if (parseInt(status) === selectedStatus) {
                    field.style.display = 'block';
                } else {
                    field.style.display = 'none';
                }
            }
        }

        productStatusSelect.addEventListener('change', updateProgressPicFields);
        updateProgressPicFields(); // Initial call to set the correct visibility on page load
    });
</script>
</body>
</html>
