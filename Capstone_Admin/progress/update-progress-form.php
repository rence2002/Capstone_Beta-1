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
$baseUrl = 'http://localhost/Capstone_Beta/';
$profilePicPath = $admin['PicPath'];
// Remove any leading slashes or duplicate directories
$profilePicPath = preg_replace('/^[\/]*(Capstone_Beta\/)?(Capstone_Admin\/)?(admin\/)?/', '', $profilePicPath);
$profilePicPath = htmlspecialchars($profilePicPath);


// Get the ID and order type from the URL
if (!isset($_GET['id']) || !isset($_GET['order_type'])) {
    $_SESSION['error'] = "Progress ID and Order Type are required.";
    header("Location: read-all-progress-form.php");
    exit();
}

$id = $_GET['id'];
$orderType = $_GET['order_type'];

// Validate order type
$validOrderTypes = ['ready_made', 'pre_order', 'custom'];
if (!in_array($orderType, $validOrderTypes)) {
    $_SESSION['error'] = "Invalid Order Type specified.";
    header("Location: read-all-progress-form.php");
    exit();
}

// Simplified query to fetch directly from tbl_progress and join user info
$query = "
    SELECT
        p.Progress_ID AS ID,
        p.Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        p.Order_Type,
        p.Product_Status,
        p.Quantity,
        p.Total_Price,
        p.Date_Added AS Request_Date,
        p.LastUpdate AS Last_Update,
        p.Progress_Pic_10,
        p.Progress_Pic_20,
        p.Progress_Pic_30,
        p.Progress_Pic_40,
        p.Progress_Pic_50,
        p.Progress_Pic_60,
        p.Progress_Pic_70,
        p.Progress_Pic_80,
        p.Progress_Pic_90,
        p.Progress_Pic_100,
        p.Stop_Reason,
        p.Tracking_Number,
        p.User_ID,
        p.Product_ID,
        pi.Price AS Base_Price,
        c.Total_Price AS Custom_Price
    FROM tbl_progress p
    JOIN tbl_user_info u ON p.User_ID = u.User_ID
    JOIN tbl_prod_info pi ON p.Product_ID = pi.Product_ID
    LEFT JOIN tbl_customizations c ON p.Product_ID = c.Product_ID
    WHERE p.Progress_ID = :id AND p.Order_Type = :order_type
";

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':order_type', $orderType, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $_SESSION['error'] = "Progress record not found for ID: " . htmlspecialchars($id);
    header("Location: read-all-progress-form.php");
    exit();
}

// REMOVED Order Status labels array
// $orderStatusLabels = [ ... ];

// Updated Product Status labels array (using the new one from prompt)
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
    100 => 'Order Recieved', // Note: Typo 'Recieved' in provided map, kept as is. Should likely be 'Received'
];

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Update Progress</title> <!-- More specific title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <!-- Removed dashboard.js as it might conflict or be redundant -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        .img-thumbnail { max-width: 100px; height: auto; }
        .table th { width: 25%; }
        .table td { width: 75%; }
    </style>
</head>

<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE" class="logo_image"> <!-- Use class -->
        </span>
      </div>
      <ul class="nav-links">
          <li>
              <a href="../dashboard/dashboard.php">
                  <i class="bx bx-grid-alt"></i>
                  <span class="links_name">Dashboard</span>
              </a>
          </li>
          <li>
              <a href="../purchase-history/read-all-history-form.php">
                  <i class="bx bx-history"></i> <!-- Changed icon -->
                  <span class="links_name">Purchase History</span>
              </a>
          </li>
          <li>
              <a href="../reviews/read-all-reviews-form.php">
                  <i class="bx bx-message-dots"></i>
                  <span class="links_name">All Reviews</span>
              </a>
          </li>
          <!-- Add other relevant links here -->
      </ul>
    </div>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Update Progress</span> <!-- Updated title -->
            </div>
            <div class="profile-details" onclick="toggleDropdown()">
                <img src="<?php echo $baseUrl . $profilePicPath; ?>" alt="Profile Picture" />
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
            <h4>UPDATE PROGRESS FOR ID: <?= htmlspecialchars($row['ID']) ?></h4>
            <form action="update-progress-rec.php" method="POST" enctype="multipart/form-data">
                <!-- Hidden fields are crucial for the update script -->
                <input type="hidden" name="Progress_ID" value="<?= htmlspecialchars($row['ID']) ?>">
                <input type="hidden" name="Order_Type" value="<?= htmlspecialchars($orderType) ?>"> <!-- Use orderType from URL -->
                <input type="hidden" name="Product_ID" value="<?= htmlspecialchars($row['Product_ID']) ?>">
                <!-- NOTE: update-progress-rec.php still expects 'Order_Status'. This form no longer sends it. -->
                <!-- update-progress-rec.php will need modification to function correctly. -->

                <table class="table table-bordered">
                    <tr>
                        <th>Product Name</th>
                        <td><?= htmlspecialchars($row['Product_Name']) ?></td>
                    </tr>
                    <tr>
                        <th>User Name</th>
                        <td><?= htmlspecialchars($row['User_Name']) ?></td>
                    </tr>
                    <tr>
                        <th>Quantity</th>
                        <td>
                            <input type="number" name="Quantity" class="form-control" value="<?= htmlspecialchars($row['Quantity']) ?>" min="1" required>
                        </td>
                    </tr>
                    <tr>
                        <th>Total Price</th>
                        <td>
                            <input type="number" name="Total_Price" class="form-control" 
                                value="<?= $orderType === 'custom' ? htmlspecialchars($row['Custom_Price'] ?? $row['Total_Price']) : htmlspecialchars($row['Total_Price']) ?>" 
                                min="0" step="0.01"
                                <?= $orderType !== 'custom' ? 'disabled' : '' ?>>
                            <small class="text-muted">
                                <?= $orderType === 'custom' 
                                    ? 'Enter the total price for the order' 
                                    : 'Price is automatically calculated for non-custom products' ?>
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <th>Product Status</th>
                        <td>
                            <select name="Product_Status" class="form-control" required>
                                <?php foreach ($productStatusLabels as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $row['Product_Status'] == $key ? 'selected' : '' ?>>
                                        <?= "$key% - $value" ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                     <tr id="tracking-number-row" style="display: none;"> <!-- Initially hidden, controlled by JS -->
                        <th>Tracking Number</th>
                        <td>
                            <input type="text" name="Tracking_Number" id="tracking-number" class="form-control" placeholder="Enter Tracking Number" value="<?= htmlspecialchars($row['Tracking_Number'] ?? '') ?>"> <!-- Pre-fill value -->
                        </td>
                    </tr>
                    <tr>
                        <th>Stop Reason</th>
                        <td>
                            <select name="Stop_Reason" class="form-control">
                                <option value="">None</option>
                                <option value="fire" <?= ($row['Stop_Reason'] ?? '') === 'fire' ? 'selected' : '' ?>>We sincerely apologize for the inconvenience caused by the fire.</option>
                                <option value="flood" <?= ($row['Stop_Reason'] ?? '') === 'flood' ? 'selected' : '' ?>>We deeply regret the disruption caused by the flood.</option>
                                <option value="typhoon" <?= ($row['Stop_Reason'] ?? '') === 'typhoon' ? 'selected' : '' ?>>We are truly sorry for the difficulties caused by the typhoon.</option>
                                <option value="earthquake" <?= ($row['Stop_Reason'] ?? '') === 'earthquake' ? 'selected' : '' ?>>We apologize for the disruption caused by the earthquake.</option>
                                <!-- Add other reasons as needed -->
                            </select>
                        </td>
                    </tr>
                </table>

                <h3>Progress Pictures</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Progress</th>
                            <th>Upload New Image</th>
                            <th>Current Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ([20, 30, 40, 50, 60, 70, 80, 90, 100] as $percentage): ?>
                            <?php $picKey = "Progress_Pic_$percentage"; ?>
                            <tr>
                                <td><strong><?= $percentage ?>%</strong></td>
                                <td><input type="file" name="Progress_Pic_<?= $percentage ?>" class="form-control-file"></td>
                                <td>
                                    <?php if (!empty($row[$picKey])): ?>
                                        <?php
                                            // Clean up the path to remove any potential duplicate directories
                                            $imageDisplayPath = preg_replace('/^[\/]*(Capstone_Beta\/)?(Capstone_Admin\/)?(admin\/)?/', '', $row[$picKey]);
                                            $imageDisplayPath = $baseUrl . $imageDisplayPath;
                                        ?>
                                        <img src="<?= htmlspecialchars($imageDisplayPath) ?>"
                                             alt="Progress <?= $percentage ?>%"
                                             class="img-thumbnail">
                                    <?php else: ?>
                                        <span>No image uploaded</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="button-container mt-3"> <!-- Added margin top -->
                    <a href="read-all-progress-form.php" class="buttonBack btn btn-secondary">Back to List</a>
                    <button type="submit" class="buttonUpdate btn btn-primary">Update Progress</button>
                </div>
            </form>
        </div>
    </section>

    <script>
        // Sidebar Toggle
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        if (sidebar && sidebarBtn) {
            sidebarBtn.onclick = function () {
                sidebar.classList.toggle("active");
                if (sidebar.classList.contains("active")) {
                    sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
                } else {
                    sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
                }
            };
        }

        // Profile Dropdown Toggle
        const profileDetailsContainer = document.querySelector(".profile-details");
        const profileDropdown = document.getElementById('profileDropdown');
        const dropdownIcon = document.querySelector(".dropdown-button");

        if (profileDetailsContainer && profileDropdown && dropdownIcon) {
            profileDetailsContainer.addEventListener('click', function(event) {
                // Prevent dropdown from closing if click is inside dropdown
                if (!profileDropdown.contains(event.target)) {
                     profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
                     dropdownIcon.classList.toggle('bx-chevron-up'); // Toggle icon class
                }
            });

            // Close dropdown if clicked outside
            document.addEventListener('click', function(event) {
                if (!profileDetailsContainer.contains(event.target)) {
                    profileDropdown.style.display = 'none';
                    dropdownIcon.classList.remove('bx-chevron-up'); // Ensure icon is down
                }
            });
        }

        // Function to toggle the tracking number input field based on Product Status
        function toggleTrackingNumber() {
            // Select the Product Status dropdown
            const productStatusSelect = document.querySelector('select[name="Product_Status"]');
            const trackingNumberRow = document.getElementById('tracking-number-row');
            const trackingNumberInput = document.getElementById('tracking-number');

            if (!productStatusSelect || !trackingNumberRow || !trackingNumberInput) {
                console.error("Required elements for tracking number toggle not found.");
                return; // Exit if elements are missing
            }

            // Show the tracking number input if the selected status is "Ready for Shipment" (95%)
            if (productStatusSelect.value == 95) {
                trackingNumberRow.style.display = 'table-row';
                // Optional: Make it required only if it's visible and empty
                // trackingNumberInput.required = trackingNumberInput.value.trim() === '';
            } else {
                trackingNumberRow.style.display = 'none';
                trackingNumberInput.required = false; // Remove the required attribute
            }
        }

        // Attach the function to the Product Status dropdown change event
        const productStatusDropdown = document.querySelector('select[name="Product_Status"]');
        if (productStatusDropdown) {
            productStatusDropdown.addEventListener('change', toggleTrackingNumber);
        }

        // Call the function on page load to set the initial state based on the pre-selected value
        document.addEventListener('DOMContentLoaded', toggleTrackingNumber);

        // Add price calculation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.querySelector('input[name="Quantity"]');
            const totalPriceInput = document.querySelector('input[name="Total_Price"]');
            const orderType = '<?= $row['Order_Type'] ?>';

            // Only calculate price for non-custom products
            if (orderType !== 'custom') {
                function calculateTotalPrice() {
                    const quantity = parseInt(quantityInput.value) || 0;
                    // Get the base price from the product info
                    const basePrice = <?= $row['Order_Type'] === 'custom' ? '0' : 'parseFloat("' . $row['Base_Price'] . '")' ?>;
                    totalPriceInput.value = (quantity * basePrice).toFixed(2);
                }

                quantityInput.addEventListener('input', calculateTotalPrice);
                calculateTotalPrice(); // Calculate initial total price
            }
        });
    </script>
</body>
</html>
