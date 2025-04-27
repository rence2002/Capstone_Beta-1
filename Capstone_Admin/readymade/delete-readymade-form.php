<?php
session_start();

// Include database connection
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin data for profile display
$adminId = $_SESSION['admin_id'];
if ($pdo) {
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
}

// Fetch ready-made order record based on the provided ID, including the product name and user name
$query = "
    SELECT 
        r.ReadyMadeOrder_ID, 
        r.Product_ID, 
        pi.Product_Name, 
        r.User_ID, 
        ui.First_Name AS User_First_Name, 
        ui.Last_Name AS User_Last_Name, 
        r.Quantity, 
        r.Total_Price, 
        r.Product_Status, 
        r.Payment_Status, 
        r.Order_Date
    FROM tbl_ready_made_orders r
    JOIN tbl_prod_info pi ON r.Product_ID = pi.Product_ID
    JOIN tbl_user_info ui ON r.User_ID = ui.User_ID
    WHERE r.ReadyMadeOrder_ID = :orderID
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':orderID', $_GET['id'], PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Error: Ready-made order not found.");
}

// Store data records to variables
$orderID = $row["ReadyMadeOrder_ID"];
$productID = $row["Product_ID"];
$productName = $row["Product_Name"];
$userID = $row["User_ID"];
$userName = $row["User_First_Name"] . ' ' . $row["User_Last_Name"];
$quantity = $row["Quantity"];
$totalPrice = $row["Total_Price"];
$productStatus = $row["Product_Status"]; // Correct column name
$paymentStatus = $row["Payment_Status"];
$orderDate = $row["Order_Date"];
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
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>
    <!-- Sidebar and Navbar -->
    <div class="sidebar">
        <div class="logo-details">
            <span class="logo_name">
                <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE" class="logo_name">
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
            <div class="profile-details" onclick="toggleDropdown()">
                <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button"></i>

                <div class="dropdown" id="profileDropdown">
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>
        </nav>

        <br><br><br>
        <div class="container_boxes">
            <form method="POST" action="delete-readymade-rec.php">
                <h2>Delete Ready-Made Order</h2>
                <p>Are you sure you want to delete the following ready-made order?</p>
                <table>
                    <?php
                    echo '
                    <tr>
                        <td>Order ID:</td>
                        <td>' . htmlspecialchars($orderID) . '</td>
                    </tr>
                    <tr>
                        <td>Product ID:</td>
                        <td>' . htmlspecialchars($productID) . '</td>
                    </tr>
                    <tr>
                        <td>Product Name:</td>
                        <td>' . htmlspecialchars($productName) . '</td>
                    </tr>
                    <tr>
                        <td>User ID:</td>
                        <td>' . htmlspecialchars($userID) . '</td>
                    </tr>
                    <tr>
                        <td>User Name:</td>
                        <td>' . htmlspecialchars($userName) . '</td>
                    </tr>
                    <tr>
                        <td>Quantity:</td>
                        <td>' . htmlspecialchars($quantity) . '</td>
                    </tr>
                    <tr>
                        <td>Total Price:</td>
                        <td>' . htmlspecialchars($totalPrice) . '</td>
                    </tr>
                    <tr>
                        <td>Product Status:</td>
                        <td>' . htmlspecialchars($productStatus) . '</td>
                    </tr>
                    <tr>
                        <td>Payment Status:</td>
                        <td>' . htmlspecialchars($paymentStatus) . '</td>
                    </tr>
                    <tr>
                        <td>Order Date:</td>
                        <td>' . htmlspecialchars($orderDate) . '</td>
                    </tr>';
                    ?>
                </table>

                <!-- Separated buttons -->
                <div class="button-container">
                    <a href="read-all-readymade-form.php" target="_parent" class="buttonBack">Back to List</a>
                    <a href="delete-readymade-rec.php?id=<?php echo htmlspecialchars($orderID); ?>" target="_parent" class="buttonDelete">Delete Record</a>
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
    </script>
</body>
</html>