<?php
session_start();

// Include database connection
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin data for profile display
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
// Construct the correct path relative to the web root if PicPath doesn't start with '../' or '/'
$profilePicPath = $admin['PicPath'];
if (!preg_match('/^(\.\.\/|\/)/', $profilePicPath)) {
    // Assuming PicPath is relative to the Capstone_Admin directory
    $profilePicPath = '../' . $profilePicPath;
}
$profilePicPath = htmlspecialchars($profilePicPath);


// Get Progress_ID from URL (passed as 'id')
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid or missing Progress ID.";
    exit();
}
$progressID = (int)$_GET['id'];

// Query to select progress record details from tbl_progress for a pre-order
// Removed fields no longer needed for display (Stop_Reason, Tracking_Number, Progress_Pics)
$query = "
    SELECT
        p.Progress_ID,
        p.Quantity,
        p.Total_Price,
        p.Product_Status, -- Still needed to know current status, even if not editable here
        p.Product_ID,
        p.Product_Name,
        pi.Price AS Base_Price, -- Fetch base price for calculation
        pi.GLB_File_URL,
        p.User_ID,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name
    FROM tbl_progress AS p
    JOIN tbl_prod_info AS pi ON p.Product_ID = pi.Product_ID
    JOIN tbl_user_info AS u ON p.User_ID = u.User_ID
    WHERE p.Progress_ID = :progress_id
      AND p.Order_Type = 'pre_order'
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':progress_id', $progressID, PDO::PARAM_INT);
$stmt->execute();
$progressRecord = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$progressRecord) {
    echo "Pre-order progress record not found (ID: $progressID).";
    exit();
}

// Product status mapping
$productStatusLabels = [
    0   => 'Request Approved', // 0% - Order placed by the customer
    10  => 'Design Approved', // 10% - Finalized by customer
    20  => 'Material Sourcing', // 20% - Materials being gathered
    30  => 'Cutting & Shaping', // 30% - Preparing materials
    40  => 'Structural Assembly', // 40% - Base framework built
    50  => 'Detailing & Refinements', // 50% - Carvings, elements added
    60  => 'Sanding & Pre-Finishing', // 60% - Smoothening
    70  => 'Varnishing/Painting', // 70% - Applying the final finish
    80  => 'Drying & Curing', // 80% - Final coating sets in
    90  => 'Final Inspection & Packaging', // 90% - Quality control before handover
    95  => 'Ready for Shipment', // 95% - Ready for handover/shipment
    98  => 'Order Delivered', // 98% - Confirmed delivery by logistics/customer
    100 => 'Order Received / Complete', // 100% - Final confirmation by customer / Order cycle complete
];



?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Update Pre-Order Details</title> <!-- Updated Title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        /* Consistent styling */
        .table th { width: 25%; }
        .table td { width: 75%; }
        model-viewer { width: 100%; max-width: 300px; height: 300px; border: 1px solid #ccc; }
    </style>
</head>

<body>
    <!-- Sidebar and Nav remain the same -->
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE" class="logo_image">
        </span>
      </div>
      <ul class="nav-links">
          <li><a href="../dashboard/dashboard.php"><i class="bx bx-grid-alt"></i><span class="links_name">Dashboard</span></a></li>
          <li><a href="../purchase-history/read-all-history-form.php"><i class="bx bx-history"></i><span class="links_name">Purchase History</span></a></li>
          <li><a href="../reviews/read-all-reviews-form.php"><i class="bx bx-message-dots"></i><span class="links_name">All Reviews</span></a></li>
          <!-- Add other relevant links here -->
      </ul>
    </div>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Update Pre-Order Details</span> <!-- Updated title -->
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
            <h4>UPDATE PRE-ORDER DETAILS (ID: <?= htmlspecialchars($progressID) ?>)</h4>
            <!-- Form action still points to local processing script -->
            <!-- This form now primarily updates Quantity/Total Price -->
            <form name="frmPreorderRec" method="POST" action="update-preorder-prod-rec.php">
                <!-- Hidden field for Progress_ID -->
                <input type="hidden" name="Progress_ID" value="<?= htmlspecialchars($progressID) ?>">
                <!-- Hidden input for base price -->
                <input type="hidden" id="basePrice" value="<?= htmlspecialchars($progressRecord['Base_Price'] ?? 0) ?>">
                 <!-- Hidden field for Order_Type (needed by processing script if it handles multiple types) -->
                <input type="hidden" name="Order_Type" value="pre_order">
                <!-- Hidden field to pass current Product_Status if needed by processing script, though not editable here -->
                <input type="hidden" name="Product_Status" value="<?= htmlspecialchars($progressRecord['Product_Status']) ?>">


                <table class="table table-bordered">
                    <!-- Use $progressRecord array -->
                    <tr><th>User</th><td><input type="text" class="form-control" value="<?= htmlspecialchars($progressRecord['User_Name']) ?>" readonly></td></tr>
                    <tr><th>Product Name</th><td><input type="text" class="form-control" value="<?= htmlspecialchars($progressRecord['Product_Name']) ?>" readonly></td></tr>
                    <tr>
                        <th>3D Model</th>
                        <td>
                            <?php if ($progressRecord['GLB_File_URL']): ?>
                                <model-viewer src="/Capstone_Beta/<?= htmlspecialchars($progressRecord['GLB_File_URL']) ?>" auto-rotate camera-controls></model-viewer>
                            <?php else: ?>
                                No 3D model available.
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Quantity</th>
                        <td><input type="number" id="quantity" name="Quantity" class="form-control" value="<?= htmlspecialchars($progressRecord['Quantity']) ?>" min="1" required oninput="calculateTotalPrice()"></td>
                    </tr>
                    <tr>
                        <th>Total Price</th>
                        <td><input type="text" id="totalPrice" name="Total_Price" class="form-control" value="<?= htmlspecialchars(number_format((float)$progressRecord['Total_Price'], 2, '.', '')) ?>" readonly required></td>
                    </tr>
                 
                </table>

               
                <div class="button-container mt-3">
                    <!-- Back link points to the pre-order list -->
                    <a href="read-all-preorder-prod-form.php" class="buttonBack btn btn-secondary">Back to List</a>
                    <button type="submit" class="buttonUpdate btn btn-primary">Update Pre-Order Details</button>
                    <!-- Added Button to Update Full Progress -->
                    <a href="../preorder-prod/update-preorder-prod-form.php?id=<?= htmlspecialchars($progressID) ?>&order_type=pre_order" class="buttonEdit btn btn-info" style="margin-left: 10px;">Update Order Progress</a>
                </div>
            </form>
        </div>
    </section>

    <!-- JavaScript for Sidebar, Profile Dropdown, and Price Calculation -->
    <script>
        // Sidebar Toggle
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        if (sidebar && sidebarBtn) {
            sidebarBtn.onclick = function () {
                sidebar.classList.toggle("active");
                sidebarBtn.classList.toggle("bx-menu-alt-right");
            };
        }

        // Profile Dropdown Toggle (Consistent version)
        const profileDetailsContainer = document.querySelector('.profile-details');
        const profileDropdown = document.getElementById('profileDropdown');
        const dropdownIcon = document.querySelector('.dropdown-button');

        if (profileDetailsContainer && profileDropdown && dropdownIcon) {
            profileDetailsContainer.addEventListener('click', function(event) {
                if (!profileDropdown.contains(event.target)) {
                     profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
                     dropdownIcon.classList.toggle('bx-chevron-up');
                }
            });
            document.addEventListener('click', function(event) {
                if (!profileDetailsContainer.contains(event.target)) {
                    profileDropdown.style.display = 'none';
                    dropdownIcon.classList.remove('bx-chevron-up');
                }
            });
        }

        // Function to calculate total price
        function calculateTotalPrice() {
            const quantityInput = document.getElementById('quantity');
            const basePriceInput = document.getElementById('basePrice'); // Use hidden input for base price
            const totalPriceInput = document.getElementById('totalPrice');

            if (quantityInput && basePriceInput && totalPriceInput) {
                const quantity = parseInt(quantityInput.value) || 0;
                const price = parseFloat(basePriceInput.value) || 0;
                const total = quantity * price;
                totalPriceInput.value = total.toFixed(2);
            }
        }

        // REMOVED toggleTrackingNumber function and its listeners

        // Call calculateTotalPrice on page load
        document.addEventListener('DOMContentLoaded', () => {
             calculateTotalPrice(); // Calculate initial price
             // REMOVED toggleTrackingNumber call
        });

    </script>
    <!-- Ensure model-viewer script is loaded -->
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
</body>
</html>
