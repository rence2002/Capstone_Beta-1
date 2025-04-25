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


// Fetch product data for dropdown (Consider if pre-orders should only be for readymade?)
// Keeping the original filter for now.
$productQuery = "SELECT Product_ID, Product_Name, Price, GLB_File_URL FROM tbl_prod_info WHERE product_type = 'readymade'";
$productStmt = $pdo->prepare($productQuery);
$productStmt->execute();
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user data for dropdown
$userQuery = "SELECT User_ID, CONCAT(First_Name, ' ', Last_Name) AS User_Name FROM tbl_user_info ORDER BY Last_Name, First_Name"; // Added ordering
$userStmt = $pdo->prepare($userQuery);
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

// REMOVED the incorrect PHP processing block from within the form file.
// Form submission is handled by the 'action' attribute target.

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Create Pre-Order Request</title> <!-- Specific Title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <!-- <script src="../static/js/dashboard.js"></script> --> <!-- Removed -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <!-- <link href="../static/js/admin_home.js" rel=""> --> <!-- Incorrect link type -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
      
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
                <span class="dashboard">Create Pre-Order Request</span> <!-- Updated title -->
            </div>
            <div class="profile-details" id="profile-details-container"> <!-- Added ID -->
                <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button" id="dropdown-icon"></i> <!-- Added ID -->
                <div class="dropdown" id="profileDropdown">
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>
        </nav>
        <br><br><br>

        <div class="container_boxes">
    <h4>
        CREATE PRE-ORDER REQUEST
      
    </h4>


    <form name="frmPreorder" method="POST" action="create-preorder-prod-rec.php">
        <div id="preorder-form">
            <table width="100%" border="1" cellspacing="5">
                <tr>
                    <td style="width: 30%;"><label for="productSelect">Product:</label></td>
                    <td>
                        <select name="Product_ID" id="productSelect" class="form-select" required>
                            <option value="" data-price="0" data-glb="">Select Product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['Product_ID']; ?>"
                                        data-price="<?php echo htmlspecialchars($product['Price']); ?>"
                                        data-glb="<?php echo htmlspecialchars($product['GLB_File_URL'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($product['Product_Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><label for="modelViewer">3D Model:</label></td>
                    <td>
                        <model-viewer id="modelViewer" auto-rotate camera-controls style="width: 100%; height: 300px;"></model-viewer>
                    </td>
                </tr>

                <tr>
                    <td><label for="userSelect">User:</label></td>
                    <td>
                        <select name="User_ID" id="userSelect" class="form-select" required>
                            <option value="">Select User</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo htmlspecialchars($user['User_ID']); ?>">
                                    <?php echo htmlspecialchars($user['User_Name']); ?> (<?php echo htmlspecialchars($user['User_ID']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><label for="quantityInput">Quantity:</label></td>
                    <td>
                        <input type="number" name="Quantity" id="quantityInput" class="form-control" min="1" value="1" required>
                    </td>
                </tr>

                <tr>
                    <td><label for="totalPriceInput">Total Price:</label></td>
                    <td>
                        <input type="text" name="Total_Price" id="totalPriceInput" class="form-control" readonly required placeholder="Calculated automatically">
                    </td>
                </tr>
            </table>

            <!-- Hidden fields -->
            <input type="hidden" name="Order_Type" value="pre_order">
            <input type="hidden" name="Payment_Status" value="Pending">
            <input type="hidden" name="Processed" value="0">
        </div>

        <!-- Buttons under the table -->
        <div class="button-container mt-3">
        <a href="../preorder-prod/read-all-preorder-prod-form.php" class="buttonBack">Back to List</a>
            <button type="submit" class="buttonUpdate btn btn-success">Create Request</button>
            <button type="reset" class="buttonDelete btn btn-warning">Reset Form</button>
        </div>
    </form>
</div>

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

            // Profile Dropdown Toggle (Consistent version)
            const profileDetailsContainer = document.getElementById('profile-details-container');
            const profileDropdown = document.getElementById('profileDropdown');
            const dropdownIcon = document.getElementById('dropdown-icon');

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

            // Calculate total price
            const productSelect = document.getElementById('productSelect');
            const quantityInput = document.getElementById('quantityInput');
            const totalPriceInput = document.getElementById('totalPriceInput'); // Corrected ID

            function calculateTotalPrice() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const quantity = parseInt(quantityInput.value) || 0;

                const totalPrice = price * quantity;
                totalPriceInput.value = totalPrice.toFixed(2); // Format to 2 decimal places
            }

            // Update 3D Model Viewer
            function updateModelViewer() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const glbFileURL = selectedOption.getAttribute('data-glb');
                const modelViewer = document.getElementById('modelViewer');

                if (modelViewer) { // Check if element exists
                    if (glbFileURL && glbFileURL !== 'null' && glbFileURL !== '') { // Check for valid URL
                        modelViewer.src = glbFileURL;
                        modelViewer.style.display = 'block'; // Show viewer
                    } else {
                        modelViewer.removeAttribute('src');
                        modelViewer.style.display = 'none'; // Hide viewer if no model
                    }
                }
            }

            // Event listeners
            if (productSelect && quantityInput && totalPriceInput) {
                productSelect.addEventListener('change', () => {
                    calculateTotalPrice();
                    updateModelViewer();
                });
                quantityInput.addEventListener('input', calculateTotalPrice);

                // Initial setup on page load
                calculateTotalPrice();
                updateModelViewer();
            }

            // Reset form needs to clear calculated fields and hide model viewer
            const form = document.forms['frmPreorder'];
            if (form) {
                form.addEventListener('reset', function() {
                    // Use setTimeout to allow default reset to happen first
                    setTimeout(() => {
                        if(totalPriceInput) totalPriceInput.value = '';
                        const modelViewer = document.getElementById('modelViewer');
                        if(modelViewer) {
                             modelViewer.removeAttribute('src');
                             modelViewer.style.display = 'none';
                        }
                        // Optionally reset quantity to 1
                        // if(quantityInput) quantityInput.value = 1;
                    }, 0);
                });
            }

        </script>
        <!-- Ensure model-viewer script is loaded -->
        <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    </section>
</body>
</html>
