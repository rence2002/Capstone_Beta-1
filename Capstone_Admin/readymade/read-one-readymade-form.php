<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Assuming the admin's ID is stored in session after login
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

try {
    // Query to fetch order details along with product name, 3D model URL, and user full name
    $query = "
        SELECT
            r.ReadyMadeOrder_ID,
            r.Product_ID,
            p.Product_Name,
            p.GLB_File_URL,
            r.User_ID,
            CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
            r.Quantity,
            r.Total_Price,
            pr.Product_Status,
            r.Order_Date
        FROM tbl_ready_made_orders r
        JOIN tbl_prod_info p ON r.Product_ID = p.Product_ID
        JOIN tbl_user_info u ON r.User_ID = u.User_ID
        LEFT JOIN tbl_progress pr ON r.Product_ID = pr.Product_ID AND pr.Order_Type = 'ready_made'
        WHERE r.ReadyMadeOrder_ID = ?";

    // Prepare query and bind the order ID
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(1, $_GET['id']);
    $stmt->execute();

    // Fetch the record
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo "Ready-made order not found.";
        exit();
    }

    // Assign fetched data to variables
    $readyMadeOrderID = $row["ReadyMadeOrder_ID"];
    $productID = $row["Product_ID"];
    $productName = $row["Product_Name"];
    $glbFileURL = $row["GLB_File_URL"];
    $userID = $row["User_ID"];
    $userName = $row["User_Name"];
    $quantity = $row["Quantity"];
    $totalPrice = $row["Total_Price"];
    $productStatus = $row["Product_Status"] ?? 0;
    $orderDate = $row["Order_Date"];

    // Product status labels
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
        100 => 'Order Received'          // Note: Fixed typo from 'Recieved' to 'Received'
    ];

    $productStatusText = $productStatusLabels[$productStatus] ?? 'Unknown Status';
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
    exit();
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

<!-- Link to External JS -->
<script src="../static/js/dashboard.js"></script>


 </nav>

        <br><br><br>
        <div class="container_boxes">
            <form name="frmReadyMadeRec" method="POST" action="">
                <h4>View Ready-Made Order Record</h4>
                <table>
                    <tr><td>Order ID:</td><td><?php echo htmlspecialchars($readyMadeOrderID); ?></td></tr>
                    <tr><td>Product Name:</td><td><?php echo htmlspecialchars($productName); ?></td></tr>
                    <tr>
                        <td>3D Model:</td>
                        <td>
                            <?php if ($glbFileURL): ?>
                                <model-viewer src="<?php echo htmlspecialchars($glbFileURL); ?>" auto-rotate camera-controls style="width: 300px; height: 300px; background-color: #f0f0f0; border-radius: 5px;"></model-viewer>
                            <?php else: ?>
                                No 3D model available.
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr><td>User Name:</td><td><?php echo htmlspecialchars($userName); ?></td></tr>
                    <tr><td>Quantity:</td><td><?php echo htmlspecialchars($quantity); ?></td></tr>
                    <tr><td>Total Price:</td><td><?php echo number_format($totalPrice, 2); ?></td></tr>
                    <!-- Removed Order Status display as Product Status is more relevant here -->
                    <!-- <tr><td>Order Status:</td><td><?php // echo htmlspecialchars($orderStatusText); ?></td></tr> -->
                   
                    <tr><td>Order Date:</td><td><?php echo htmlspecialchars($orderDate); ?></td></tr>
                </table>
                <div class="button-container">
                    <br>
                    <a href="read-all-readymade-form.php" class="buttonBack">Back to List</a>
                    <a href="update-readymade-form.php?id=<?php echo $readyMadeOrderID; ?>" class="buttonEdit">Edit</a>
                </div>
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

        // Simple dropdown toggle for profile details
        function toggleDropdown() {
            document.getElementById("profileDropdown").classList.toggle("show");
        }

        // Close the dropdown if the user clicks outside of it
        window.onclick = function(event) {
          if (!event.target.matches('.profile-details') && !event.target.matches('.profile-details *')) {
            var dropdowns = document.getElementsByClassName("dropdown");
            for (var i = 0; i < dropdowns.length; i++) {
              var openDropdown = dropdowns[i];
              if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
              }
            }
          }
        }

        // Sidebar dropdowns (if any were added)
        document.querySelectorAll('.dropdown-toggle').forEach((toggle) => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault(); // Prevent default link behavior
            const parentLi = this.closest('li'); // Find the parent <li> element
            const subMenu = parentLi.querySelector('.sub-menu'); // Find the sub-menu within this <li>

            // Close other open sub-menus
            document.querySelectorAll('.nav-links li .sub-menu').forEach((menu) => {
                if (menu !== subMenu) {
                    menu.style.display = 'none';
                    menu.closest('li').classList.remove('active'); // Remove active class from others
                }
            });

             // Toggle the current sub-menu
            if (subMenu) {
                const isActive = parentLi.classList.toggle('active');
                subMenu.style.display = isActive ? 'block' : 'none';

                 // Toggle chevron icon
                const chevron = this.querySelector('.bx-chevron-down, .bx-chevron-up');
                if (chevron) {
                    if (isActive) {
                        chevron.classList.remove('bx-chevron-down');
                        chevron.classList.add('bx-chevron-up');
                    } else {
                        chevron.classList.remove('bx-chevron-up');
                        chevron.classList.add('bx-chevron-down');
                    }
                }
            }
        });
    });
    </script>
    <!-- Add model-viewer script -->
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>

    <style>
        /* Progress bar styles */
        .status-bar {
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            height: 20px;
            position: relative;
            width: 100%;
            margin-bottom: 5px;
        }
        .status-bar-fill {
            background-color: #4CAF50;
            height: 100%;
            text-align: center;
            color: white;
            line-height: 20px;
            font-size: 12px;
            transition: width 0.5s ease-in-out;
            white-space: nowrap;
        }
        .product-status-bar { background-color: #2196F3; }
        .status-text {
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</body>
</html>
