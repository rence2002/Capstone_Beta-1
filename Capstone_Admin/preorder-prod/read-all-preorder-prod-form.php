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

// Check if admin data is fetched
if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Fetch preorder records from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "
    SELECT 
        po.Preorder_ID,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        p.Product_Name,
        po.Total_Price,
        po.Preorder_Status,
        po.Order_Date
    FROM tbl_preorder po
    JOIN tbl_user_info u ON po.User_ID = u.User_ID
    JOIN tbl_prod_info p ON po.Product_ID = p.Product_ID
    WHERE u.First_Name LIKE :search 
    OR u.Last_Name LIKE :search 
    OR p.Product_Name LIKE :search
    OR po.Preorder_Status LIKE :search
";
$stmt = $pdo->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preorder Status mapping
$statusLabels = [
    0 => 'Pending',
    10 => 'Order Placed',
    20 => 'Payment Processing',
    30 => 'Order Confirmed',
    40 => 'Preparing for Shipment',
    50 => 'Shipped',
    60 => 'Out for Delivery',
    70 => 'Delivered',
    80 => 'Installed',
    100 => 'Complete'
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
<br>
<br>
<br>
<div class="container_boxes">
    <form name="frmPreorderRec" method="POST" action="">
        <h4>PREORDER LIST <a href="create-preorder-prod-form.php">Create New Preorder</a></h4>
        <!-- Add Back to Dashboard button -->
        <div class="button-container">
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                </div>
                <table>
        <table>
            <tr>
                <th>User Name</th>
                <th>Product Name</th>
                <th>Total Price</th>
                <th>Preorder Status</th>
                <th colspan="3" style="text-align: center;">ACTIONS</th>
            </tr>
            <?php
            error_reporting(0);
            foreach ($rows as $row) { 
                $preorderID = htmlspecialchars($row["Preorder_ID"]);
                $userName = htmlspecialchars($row["User_Name"]);
                $productName = htmlspecialchars($row["Product_Name"]);
                $quantity = htmlspecialchars($row["Quantity"]);
                $totalPrice = number_format((float)$row["Total_Price"], 2, '.', '');
                $preorderStatus = htmlspecialchars($statusLabels[$row["Preorder_Status"]] ?? 'Unknown');
                $progressPercent = $row["Preorder_Status"]; // Preorder status as progress

                echo '
                <tr>
                    <td>'.$userName.'</td>
                    <td>'.$productName.'</td>
                    <td>₱'.$totalPrice.'</td> <!-- Correctly displaying Total Price -->
                    <td>
                        <div class="progress" style="height: 20px; width: 150px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                style="width: '.$progressPercent.'%;" 
                                aria-valuenow="'.$progressPercent.'" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                '.$progressPercent.'% <!-- Display percentage only -->
                            </div>
                        </div>
                    </td>

                    <td style="text-align: center;"><a class="buttonView" href="read-one-preorder-prod-form.php?id='.$preorderID.'" target="_parent">View</a></td>
                    <td style="text-align: center;"><a class="buttonEdit" href="update-preorder-prod-form.php?id='.$preorderID.'" target="_parent">Edit</a></td>
                    <td style="text-align: center;"><a class="buttonDelete" href="delete-preorder-prod-form.php?id='.$preorderID.'" target="_parent">Delete</a></td>
                </tr>';
            }
            ?>
        </table>

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
        } else sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
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
