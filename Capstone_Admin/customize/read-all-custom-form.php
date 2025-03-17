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

// Fetch customization records from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "
    SELECT 
        c.Customization_ID, 
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name, 
        c.Furniture_Type, 
        c.Order_Status 
    FROM tbl_customizations c
    JOIN tbl_user_info u ON c.User_ID = u.User_ID
    WHERE u.First_Name LIKE :search 
    OR u.Last_Name LIKE :search 
    OR c.Furniture_Type LIKE :search
    OR c.Order_Status LIKE :search
";
$stmt = $pdo->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
$stmt->execute();
$customizations = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    90  => 'Product Finished',     // 90% - Completed and awaiting delivery/pickup
    100 => 'Delivered / Completed' // 100% - Customer has received the furniture
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
 
        <br><br><br>
        <div class="container_boxes">
            <form name="frmCustomizations" method="POST" action="">
                <h4>CUSTOMIZATION LIST <a href="create-custom-form.php">Create New Customization</a></h4>
                 <!-- Add Back to Dashboard button -->
                <div class="button-container">
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                </div>
                <table>
                    <tr>
                        <th>Customization ID</th>
                        <th>User Name</th>
                        <th>Furniture Type</th>
                        <th>Order Status</th>
                        <th colspan="3" style="text-align: center;">ACTIONS</th>
                    </tr>
                    <?php
                    error_reporting(0);
                    foreach ($customizations as $customization) { 
                        $customizationID = htmlspecialchars($customization["Customization_ID"]);
                        $userName = htmlspecialchars($customization["User_Name"]);
                        $furnitureType = htmlspecialchars($customization["Furniture_Type"]);
                        $orderStatus = $customization["Order_Status"];
                        $progressPercentage = $orderStatus;

                        echo '
                        <tr>
                            <td>'.$customizationID.'</td>
                            <td>'.$userName.'</td>
                            <td>'.$furnitureType.'</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-bar-inner" style="width: '.$progressPercentage.'%; background-color: '.getColorForStatus($orderStatus).';">
                                        '.$progressPercentage.'%
                                    </div>
                                </div>
                            </td>

                            <td style="text-align: center;"><a class="buttonView" href="read-one-custom-form.php?id='.$customizationID.'" target="_parent">View</a></td>
                            <td style="text-align: center;"><a class="buttonEdit" href="update-custom-form.php?id='.$customizationID.'" target="_parent">Edit</a></td>
                            <td style="text-align: center;"><a class="buttonDelete" href="delete-custom-form.php?id='.$customizationID.'" target="_parent">Delete</a></td>
                        </tr>';
                    }

                    function getColorForStatus($status) {
                        if ($status < 30) {
                            return '#ff0000'; // Red for initial stages
                        } elseif ($status < 60) {
                            return '#ffa500'; // Orange for mid stages
                        } elseif ($status < 90) {
                            return '#ffff00'; // Yellow for nearing completion
                        } else {
                            return '#008000'; // Green for completed
                        }
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
