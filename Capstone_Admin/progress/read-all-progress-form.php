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

// Fetch all related records, excluding those with Order_Status = 100
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "
    SELECT
        'custom' AS Order_Type,
        c.Customization_ID AS ID,
        c.Furniture_Type AS Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        c.Order_Status,
        c.Product_Status,
        0.00 as Total_Price,
        c.Last_Update AS Last_Update
    FROM tbl_customizations c
    JOIN tbl_user_info u ON c.User_ID = u.User_ID
    WHERE c.Order_Status != 100 AND
    (u.First_Name LIKE :search
    OR u.Last_Name LIKE :search
    OR c.Furniture_Type LIKE :search)
    UNION
    SELECT
        'pre_order' AS Order_Type,
        po.Preorder_ID AS ID,
        pr.Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        po.Preorder_Status AS Order_Status,
        po.Product_Status,
        po.Total_Price,
        po.Order_Date AS Last_Update
    FROM tbl_preorder po
    JOIN tbl_user_info u ON po.User_ID = u.User_ID
    JOIN tbl_prod_info pr ON po.Product_ID = pr.Product_ID
    WHERE po.Preorder_Status != 100 AND
    (u.First_Name LIKE :search
    OR u.Last_Name LIKE :search
    OR pr.Product_Name LIKE :search)
    UNION
    SELECT
        'ready_made' AS Order_Type,
        rmo.ReadyMadeOrder_ID AS ID,
        pr.Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        rmo.Order_Status,
        rmo.Product_Status,
        rmo.Total_Price,
        rmo.Order_Date AS Last_Update
    FROM tbl_ready_made_orders rmo
    JOIN tbl_user_info u ON rmo.User_ID = u.User_ID
    JOIN tbl_prod_info pr ON rmo.Product_ID = pr.Product_ID
     WHERE rmo.Order_Status != 100 AND
    (u.First_Name LIKE :search
    OR u.Last_Name LIKE :search
    OR pr.Product_Name LIKE :search)
    ORDER BY Last_Update DESC
";

$stmt = $pdo->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Function to calculate percentage
function calculatePercentage($status) {
    return ($status / 100) * 100;
}
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
    <link href="../static/css-files/progress.css" rel="stylesheet">
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
            <h4>PROGRESS LIST <a href="create-progress-form.php">Create New Progress</a></h4>
            <!-- Add Back to Dashboard button -->
            <div class="button-container">
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                </div>
                <table>
            <table width="100%" border="1" cellspacing="5">
                <tr>
                    <th>ORDER TYPE</th>
                    <th>USER NAME</th>
                    <th>PRODUCT NAME</th>
                    <th>ORDER STATUS</th>
                    <th>PRODUCT STATUS</th>
                    <th>TOTAL PRICE</th>
                    <th colspan="3" style="text-align: center;">ACTIONS</th>
                </tr>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($row['Order_Type']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['User_Name']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['Product_Name']) ?>
                        </td>
                        <td>
                            <div class="status-bar">
                                <div class="status-bar-fill order-status-bar" style="width: <?= calculatePercentage($row['Order_Status']) ?>%;">
                                    <?= calculatePercentage($row['Order_Status']) ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="status-bar">
                                <div class="status-bar-fill product-status-bar" style="width: <?= calculatePercentage($row['Product_Status']) ?>%;">
                                    <?= calculatePercentage($row['Product_Status']) ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <?= number_format((float) $row['Total_Price'], 2, '.', '') ?>
                        </td>
                        <td style="text-align: center;"><a class="buttonView" href="read-one-progress-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>" target="_parent">View</a></td>
                        <?php if($row['Order_Status'] != 100): ?>
                            <td style="text-align: center;"><a class="buttonEdit" href="update-progress-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>" target="_parent">Edit</a></td>
                            <td style="text-align: center;"><a class="buttonDelete" href="delete-progress-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>" target="_parent">Delete</a></td>
                        <?php else: ?>
                            <td style="text-align: center;"></td>
                            <td style="text-align: center;"></td>

                        <?php endif; ?>
                        
                    </tr>
                <?php endforeach; ?>
            </table>
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
