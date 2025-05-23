<?php
session_start(); // Start the session
// Include the database connection
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

// Fetch order details
$orderId = $_GET['id'];
$stmt = $pdo->prepare("
    SELECT 
        r.ReadyMadeOrder_ID, 
        r.Product_ID, 
        p.Product_Name, 
        p.Price, 
        p.GLB_File_URL,
        r.User_ID, 
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name, 
        r.Quantity, 
        r.Total_Price, 
        pr.Product_Status, 
        r.Order_Date,
        p.Image_URLs
    FROM tbl_ready_made_orders r
    JOIN tbl_prod_info p ON r.Product_ID = p.Product_ID
    JOIN tbl_user_info u ON r.User_ID = u.User_ID
    LEFT JOIN tbl_progress pr ON r.Product_ID = pr.Product_ID AND pr.Order_Type = 'ready_made'
    WHERE r.ReadyMadeOrder_ID = ?
");
$stmt->bindValue(1, $orderId);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    echo "Order not found.";
    exit();
}

// Assign fetched data
$readyMadeOrderID = $order["ReadyMadeOrder_ID"];
$productID = $order["Product_ID"];
$productName = $order["Product_Name"];
$productPrice = $order["Price"];
$glbFileURL = $order["GLB_File_URL"];
$userID = $order["User_ID"];
$userName = $order["User_Name"];
$quantity = $order["Quantity"];
$totalPrice = $order["Total_Price"];
$productStatus = $order["Product_Status"] ?? 0; // Default to 0 if no progress record exists
$orderDate = $order["Order_Date"];
$imageURLs = $order["Image_URLs"];

// Product status mapping
$productStatusLabels = [
    0   => 'Request Approved', // 0% - Order placed by the customer
    10  => 'Design Approved', // 10% - Finalized by customer
    20  => 'Payment Processing', // 20% - Or Material Sourcing if applicable
    30  => 'Cutting & Shaping', // 30% - Preparing materials
    40  => 'Structural Assembly / Preparing for Shipment', // 40% - Base framework built / Prep for ship
    50  => 'Shipped / Detailing & Refinements', // 50% - Carvings, elements added / Shipped
    60  => 'Out for Delivery / Sanding & Pre-Finishing', // 60% - Smoothening / Out for delivery
    70  => 'Delivered / Varnishing/Painting', // 70% - Applying the final finish / Delivered
    80  => 'Installed / Drying & Curing', // 80% - Final coating sets in / Installed
    90  => 'Final Inspection & Packaging', // 90% - Quality control before handover
    95  => 'Ready for Shipment', // 95% - Ready for handover/shipment
    98  => 'Order Delivered', // 98% - Confirmed delivery by logistics/customer
    100 => 'Order Received / Complete', // 100% - Final confirmation by customer / Order cycle complete
];

// Convert product status to text
$productStatusText = $productStatusLabels[$productStatus] ?? 'Unknown Status';
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
    <script>
        // Function to calculate total price dynamically
        function calculateTotalPrice() {
            const quantityInput = document.getElementById('quantity');
            const totalPriceInput = document.getElementById('totalPrice');
            const unitPrice = <?php echo json_encode($productPrice); ?>; // Get the product's unit price from PHP

            // Calculate total price
            const quantity = parseInt(quantityInput.value, 10);
            if (!isNaN(quantity) && quantity > 0) {
                const totalPrice = quantity * unitPrice;
                totalPriceInput.value = totalPrice.toFixed(2); // Format to 2 decimal places
            } else {
                totalPriceInput.value = ''; // Clear total price if quantity is invalid
            }
        }

        // Attach event listener to quantity input
        document.addEventListener('DOMContentLoaded', () => {
            const quantityInput = document.getElementById('quantity');
            if (quantityInput) {
                quantityInput.addEventListener('input', calculateTotalPrice);
            }
        });
    </script>
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
            <form name="frmReadyMadeOrder" method="POST" action="update-readymade-rec.php">
                <h4>Update Ready-Made Order</h4>
                <table>
                    <tr>
                        <td>Order ID:</td>
                        <td><input type="hidden" name="txtReadyMadeOrderID_hidden" value="<?php echo htmlspecialchars($readyMadeOrderID); ?>"></td>
                    </tr>
                    <tr>
                        <td>Product Name:</td>
                        <td><input type="text" name="txtProductName" value="<?php echo htmlspecialchars($productName); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>3D Model:</td>
                        <td>
                            <?php if ($glbFileURL): ?>
                                <model-viewer src="/Capstone_Beta/<?php echo $glbFileURL; ?>" auto-rotate camera-controls style="width: 300px; height: 300px;"></model-viewer>
                            <?php else: ?>
                                No 3D model available.
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>User Name:</td>
                        <td><input type="text" name="txtUserName" value="<?php echo htmlspecialchars($userName); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Quantity:</td>
                        <td><input class="form-control" type="number" id="quantity" name="txtQuantity" value="<?php echo htmlspecialchars($quantity); ?>" min="1" required></td>
                    </tr>
                    <tr>
                        <td>Total Price:</td>
                        <td><input type="text" id="totalPrice" name="txtTotalPrice" value="<?php echo htmlspecialchars($totalPrice); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Product Images:</td>
                        <td>
                            <?php 
                            if (!empty($imageURLs)) {
                                $imageURLs = explode(',', $imageURLs);
                                foreach ($imageURLs as $imageURL): 
                            ?>
                                <img src="/Capstone_Beta/<?php echo trim($imageURL); ?>" alt="Product Image" style="width:100px;height:auto; margin-right: 10px;">
                            <?php 
                                endforeach;
                            } else {
                                echo "No images available.";
                            }
                            ?>
                        </td>
                    </tr>
                </table>
                <div class="button-container">
                    <a href="read-all-readymade-form.php" class="buttonBack">Back to List</a>
                    <input type="submit" value="Update" class="buttonUpdate">
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