<?php
session_start(); // Start the session

// Include the database connection
include './config/database.php'; 

// Check if the admin's ID is stored in the session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: /Capstone/login.php");
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
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="/Capstone/static/css/bootstrap.min.css" rel="stylesheet">
    <script src="/Capstone/static/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <link href="/Capstone/static/css-files/dashboard.css" rel="stylesheet">
    <link href="/Capstone/static/css-files/button.css" rel="stylesheet">
    <link href="/Capstone/static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="/Capstone/static/js/admin_home.js" rel="">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>
    <div class="sidebar">
        <div class="logo-details">
            <span class="logo_name">
                <img src="/Capstone/static/images/rm raw png.png" alt="RM BETIS FURNITURE" class="logo_name">
            </span>
        </div>
        <ul class="nav-links">
            <li>
                <a href="/Capstone/dashboard/dashboard.php" class="">
                    <i class="bx bx-grid-alt"></i>
                    <span class="links_name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/Capstone/admin/read-all-admin-form.php" class="">
                    <i class="bx bx-box"></i>
                    <span class="links_name">Admin List</span>
                </a>
            </li>
            <li>
                <a href="/Capstone/user/read-all-user-form.php" class="">
                    <i class="bx bx-box"></i>
                    <span class="links_name">User List</span>
                </a>
            </li>
            <li>
                <a href="/Capstone/product/read-all-product-form.php" class="">
                    <i class="bx bx-box"></i>
                    <span class="links_name">Product</span>
                </a>
            </li>
            <li>
                <a href="/Capstone/order-requests/read-all-request-form.php" class="">
                    <i class="bx bx-box"></i>
                    <span class="links_name">Order Requests</span>
                </a>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropdown-toggle">
                    <i class="bx bx-chevron-down"></i> <!-- Only one icon for the dropdown indicator -->
                    <span class="links_name">Orders</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="/Capstone/preorder-prod/read-all-preorder-prod-form.php">Pre Orders</a></li>
                    <li><a href="/Capstone/readymade/read-all-readymade-form.php">Ready Made Orders</a></li>
                    <li><a href="/Capstone/customize/read-all-custom-form.php">Customize</a></li>
                </ul>
            </li>

            <li>
                <a href="/Capstone/cart/read-all-cart-form.php" class="">
                    <i class="bx bx-cart"></i>
                    <span class="links_name">Add to Cart</span>
                </a>
            </li>
            <li>
                <a href="/Capstone/progress/read-all-progress-form.php" class="">
                    <i class="bx bx-box"></i>
                    <span class="links_name">Progress</span>
                </a>
            </li>
            <li>
                <a href="/Capstone/purchase-history/read-all-history-form.php" class="">
                    <i class="bx bx-comment-detail"></i>
                    <span class="links_name">All Purchase History</span>
                </a>
            </li>
            <li>
                <a href="/Capstone/reviews/read-all-reviews-form.php" class="">
                    <i class="bx bx-comment-detail"></i>
                    <span class="links_name">All Reviews</span>
                </a>
            </li>
            <li class="log_out">
                <a href="../admin/logout.php">
                    <i class="bx bx-log-out"></i>
                    <span class="links_name">Log out</span>
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
            <div class="profile-details">
                <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
            </div>
 </nav>

        <br><br><br>



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
</body>
</html>




