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
// Construct the correct path relative to the web root if PicPath doesn't start with '../' or '/'
$profilePicPath = $admin['PicPath'];
if (!preg_match('/^(\.\.\/|\/)/', $profilePicPath)) {
    // Assuming PicPath is relative to the Capstone_Admin directory
    $profilePicPath = '../' . $profilePicPath;
}
$profilePicPath = htmlspecialchars($profilePicPath);


// Fetch users from the database
$userStmt = $pdo->prepare("SELECT User_ID, First_Name, Last_Name FROM tbl_user_info ORDER BY Last_Name, First_Name"); // Added Last_Name and ordering
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products from the database, including Product_Name and Price
$productStmt = $pdo->prepare("SELECT Product_ID, Product_Name, Price FROM tbl_prod_info ORDER BY Product_Name");
$productStmt->execute();
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Product Status Labels (Not directly used in this form, but good for reference)
$productStatusLabels = [
    0   => 'Request Approved',
    10  => 'Design Approved',
    20  => 'Material Sourcing',
    30  => 'Cutting & Shaping',
    40  => 'Structural Assembly',
    50  => 'Detailing & Refinements',
    60  => 'Sanding & Pre-Finishing',
    70  => 'Varnishing/Painting',
    80  => 'Drying & Curing',
    90  => 'Final Inspection & Packaging',
    95  => 'Ready for Shipment',
    98  => 'Order Delivered',
    100 => 'Order Recieved',
];

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Create Progress</title> <!-- Specific Title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <!-- <script src="../static/js/dashboard.js"></script> --> <!-- Removed, likely redundant -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <!-- <link href="../static/js/admin_home.js" rel=""> --> <!-- Incorrect link type -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        /* Minor adjustments for form readability */
        .container_boxes table td { padding: 5px; }
        .container_boxes select, .container_boxes input[type=number], .container_boxes input[type=text] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .container_boxes input[readonly] { background-color: #e9ecef; }
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
              <a href="../dashboard/dashboard.php"> <!-- Removed active class -->
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
                <span class="dashboard">Create New Progress Record</span> <!-- Updated title -->
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
            <!-- Form action points to the new processing script -->
            <form name="frmProgress" method="POST" action="create-progress-rec.php">
                <h4>Create New Progress</h4>
                <table class="table table-borderless" style="width: 50%;"> <!-- Use Bootstrap table for better spacing -->
                    <tr>
                        <td style="width: 30%;"><label for="userSelect">User:</label></td>
                        <td>
                            <select id="userSelect" name="User_ID" required>
                                <option value="" disabled selected>Select User</option>
                                <?php foreach ($users as $user) : ?>
                                    <option value="<?= htmlspecialchars($user['User_ID']) ?>">
                                        <?= htmlspecialchars($user['Last_Name'] . ', ' . $user['First_Name']) ?> (<?= htmlspecialchars($user['User_ID']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="productSelect">Product:</label></td>
                        <td>
                            <select id="productSelect" name="Product_ID" required>
                                <option value="" data-price="0" data-name="" disabled selected>Select Product</option>
                                <?php foreach ($products as $product) : ?>
                                    <option value="<?= htmlspecialchars($product['Product_ID']) ?>"
                                            data-price="<?= htmlspecialchars($product['Price']) ?>"
                                            data-name="<?= htmlspecialchars($product['Product_Name']) ?>">
                                        <?= htmlspecialchars($product['Product_Name']) ?> (ID: <?= htmlspecialchars($product['Product_ID']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <!-- Hidden input to store Product Name -->
                            <input type="hidden" id="productNameInput" name="Product_Name" value="">
                        </td>
                    </tr>
                    <tr>
                        <td><label for="orderTypeSelect">Order Type:</label></td>
                        <td>
                            <!-- Changed name to match tbl_progress column -->
                            <select id="orderTypeSelect" name="Order_Type" required>
                                <option value="" disabled selected>Select Order Type</option>
                                <option value="pre_order">Pre-order</option> <!-- Use values matching potential enums or consistent strings -->
                                <option value="ready_made">Ready Made</option>
                                <option value="custom">Custom</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="quantityInput">Quantity:</label></td>
                        <td><input type="number" id="quantityInput" name="Quantity" min="1" value="1" required></td>
                    </tr>
                    <tr>
                        <td><label for="totalPriceInput">Total Price:</label></td>
                        <td><input type="text" id="totalPriceInput" name="Total_Price" readonly required placeholder="Calculated automatically"></td>
                    </tr>
                    <!-- Hidden field for initial Product Status -->
                    <input type="hidden" name="Product_Status" value="0">

                    <!-- Removed old Status dropdown -->
                    <!-- <tr>
                        <td>Status:</td>
                        <td><select name="txtStatus">...</select></td>
                    </tr> -->
                </table>

                <div class="button-container mt-3">
                    <button type="submit" class="buttonUpdate btn btn-success">Create Record</button>
                    <button type="reset" class="buttonDelete btn btn-warning">Reset Form</button>
                    <a href="read-all-progress-form.php" class="buttonBack btn btn-secondary">Back to List</a>
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

        // --- Price Calculation and Product Name Population ---
        const productSelect = document.getElementById('productSelect');
        const quantityInput = document.getElementById('quantityInput');
        const totalPriceInput = document.getElementById('totalPriceInput');
        const productNameInput = document.getElementById('productNameInput');

        function updatePriceAndName() {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const name = selectedOption.getAttribute('data-name') || '';
            const quantity = parseInt(quantityInput.value) || 0;

            const totalPrice = price * quantity;

            totalPriceInput.value = totalPrice.toFixed(2); // Format to 2 decimal places
            productNameInput.value = name; // Update hidden product name field
        }

        // Add event listeners
        if (productSelect && quantityInput && totalPriceInput && productNameInput) {
            productSelect.addEventListener('change', updatePriceAndName);
            quantityInput.addEventListener('input', updatePriceAndName);

            // Initial calculation on page load (if a product might be pre-selected, though unlikely here)
            // updatePriceAndName();
        } else {
            console.error("One or more elements for price calculation not found.");
        }

        // Reset form needs to clear calculated fields too
        const form = document.forms['frmProgress'];
        if (form) {
            form.addEventListener('reset', function() {
                // Use setTimeout to allow default reset to happen first
                setTimeout(() => {
                    totalPriceInput.value = '';
                    productNameInput.value = '';
                    // Optionally reset quantity to 1 if desired
                    // quantityInput.value = 1;
                }, 0);
            });
        }

    </script>
</body>
</html>
