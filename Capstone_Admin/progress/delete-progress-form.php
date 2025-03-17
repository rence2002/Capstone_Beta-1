<?php
session_start();

// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
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

// Get the progress record to be deleted
if (!isset($_GET['id']) || !isset($_GET['order_type'])) {
    echo "Progress ID and Order Type not specified.";
    exit();
}

$progressID = $_GET['id'];
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
                COALESCE(pr.Price, 0.00) as Total_Price,
                c.Request_Date,
                c.Last_Update,
                c.Product_ID
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
$stmt->bindParam(':id', $progressID);
$stmt->execute();
$progress = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$progress) {
    echo "Progress record not found.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Delete Progress</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../static/js/dashboard.js"></script>
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
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
                <i class="bx bx-message-dots"></i>
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
        <h2>Delete Progress Record</h2>
        <p>Are you sure you want to delete this progress record?</p>
        <table class="table">
            <tr>
                <th>ID</th>
                <td><?= htmlspecialchars($progress['ID']) ?></td>
            </tr>
            <tr>
                <th>User Name</th>
                <td><?= htmlspecialchars($progress['User_Name']) ?></td>
            </tr>
            <tr>
                <th>Product Name</th>
                <td><?= htmlspecialchars($progress['Product_Name']) ?></td>
            </tr>
             <tr>
                <th>Total Price</th>
                <td><?= htmlspecialchars($progress['Total_Price']) ?></td>
            </tr>

        </table>
        <form action="delete-progress-rec.php" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($progress['ID']) ?>">
            <input type="hidden" name="order_type" value="<?= htmlspecialchars($orderType) ?>">
            <button type="submit" class="btn btn-danger">Delete</button>
            <a href="read-all-progress-form.php" class="btn btn-secondary">Cancel</a>
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
