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

// Check if ID and Order Type are set
if (!isset($_GET['id']) || !isset($_GET['order_type'])) {
    echo "Invalid request.";
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
                CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
                c.Order_Status,
                c.Product_Status,
                COALESCE(pr.Price, 0.00) as Total_Price,
                c.Request_Date,
                c.Last_Update,
                c.ProgressPic_10,
                c.ProgressPic_20,
                c.ProgressPic_30,
                c.ProgressPic_40,
                c.ProgressPic_50,
                c.ProgressPic_60,
                c.ProgressPic_70,
                c.ProgressPic_80,
                c.ProgressPic_90,
                c.ProgressPic_100,
                c.Stop_Reason
            FROM tbl_customizations c
            JOIN tbl_user_info u ON c.User_ID = u.User_ID
            LEFT JOIN tbl_prod_info pr ON c.Product_ID = pr.Product_ID
            WHERE c.Customization_ID = :id
        ";
        break;
    case 'pre_order':
        $query = "
            SELECT
                po.Preorder_ID AS ID,
                pr.Product_Name,
                CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
                po.Preorder_Status AS Order_Status,
                po.Product_Status,
                po.Total_Price,
                po.Order_Date as Request_Date,
                po.Order_Date AS Last_Update,
                po.ProgressPic_10,
                po.ProgressPic_20,
                po.ProgressPic_30,
                po.ProgressPic_40,
                po.ProgressPic_50,
                po.ProgressPic_60,
                po.ProgressPic_70,
                po.ProgressPic_80,
                po.ProgressPic_90,
                po.ProgressPic_100,
                po.Stop_Reason
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
                CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
                rmo.Order_Status,
                rmo.Product_Status,
                rmo.Total_Price,
                rmo.Order_Date as Request_Date,
                rmo.Order_Date AS Last_Update,
                rmo.ProgressPic_10,
                rmo.ProgressPic_20,
                rmo.ProgressPic_30,
                rmo.ProgressPic_40,
                rmo.ProgressPic_50,
                rmo.ProgressPic_60,
                rmo.ProgressPic_70,
                rmo.ProgressPic_80,
                rmo.ProgressPic_90,
                rmo.ProgressPic_100,
                rmo.Stop_Reason
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
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "Record not found.";
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
    90 => 'Ready for Sale',
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
        <div class="container_boxes">
            <form name="frmProgressRec" method="POST" action="">
                <h4>View Record</h4>
                <table>
                    <tr>
                        <td>ID:</td>
                        <td><?= htmlspecialchars($row["ID"]) ?></td>
                    </tr>
                    <tr>
                        <td>Order Type:</td>
                        <td><?= htmlspecialchars($orderType) ?></td>
                    </tr>
                    <tr>
                        <td>User Name:</td>
                        <td><?= htmlspecialchars($row["User_Name"]) ?></td>
                    </tr>
                    <tr>
                        <td>Product Name:</td>
                        <td><?= htmlspecialchars($row["Product_Name"]) ?></td>
                    </tr>
                    <tr>
                        <td>Order Status:</td>
                        <td><?= htmlspecialchars($orderStatusLabels[$row["Order_Status"]] ?? "Unknown") ?></td>
                    </tr>
                    <tr>
                        <td>Product Status:</td>
                        <td><?= htmlspecialchars($productStatusLabels[$row["Product_Status"]] ?? "Unknown") ?></td>
                    </tr>
                    <tr>
                        <td>Total Price:</td>
                        <td><?= number_format((float)$row["Total_Price"], 2, '.', '') ?></td>
                    </tr>
                    <tr>
                        <td>Request Date:</td>
                        <td><?= htmlspecialchars($row["Request_Date"]) ?></td>
                    </tr>
                    <tr>
                        <td>Last Update:</td>
                        <td><?= htmlspecialchars($row["Last_Update"]) ?></td>
                    </tr>
                    <tr>
                        <td>Progress Pictures:</td>
                        <td>
                            <select id="progress-pic-dropdown" onchange="showProgressPic(this.value)">
                                <option value="">Select Progress Percentage</option>
                                <?php foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $percentage): ?>
                                    <?php $picKey = "Progress_Pic_$percentage"; ?>
                                    <?php if (!empty($row[$picKey])): ?>
                                        <option value="<?= htmlspecialchars($row[$picKey]) ?>"><?= $percentage ?>%</option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr id="progress-pic-row" style="display: none;">
                        <td colspan="2">
                            <img id="progress-pic" src="" alt="Progress Picture" style="max-width: 100%; height: auto;">
                        </td>
                        </tr>
                     <!-- New Stop Reason Display -->
                     <?php if (!empty($row["Stop_Reason"])): ?>
                        <tr id="stop-reason-row">
                            <td>Stop Reason:</td>
                            <td><?= htmlspecialchars($row["Stop_Reason"]) ?></td>
                        </tr>
                    <?php endif; ?>
                </table>

                <div class="button-container">
                    <br>
                    <a href="read-all-progress-form.php" target="_parent" class="buttonBack">Back to List</a>
                    <a href="update-progress-form.php?id=<?= htmlspecialchars($row["ID"]) ?>&order_type=<?=htmlspecialchars($orderType)?>" target="_parent" class="buttonUpdate">Update Record</a>
                </div>
            </form>
        </div>
    </section>

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
</body>
</html>
