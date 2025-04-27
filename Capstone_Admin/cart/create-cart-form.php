<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin's ID is stored in the session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login/login.php");
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

// Fetch product data
$productQuery = "SELECT Product_ID, Product_Name, Price, GLB_File_URL FROM tbl_prod_info";
$productStmt = $pdo->prepare($productQuery);
$productStmt->execute();
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user data
$userQuery = "SELECT User_ID, CONCAT(First_Name, ' ', Last_Name) AS Full_Name FROM tbl_user_info";
$userStmt = $pdo->prepare($userQuery);
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
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
                <img src="http://localhost/Capstone_Beta/<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button"></i>

                <div class="dropdown" id="profileDropdown">
                    <!-- Modified link here -->
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>

        </nav>

        <br><br><br>
        <div class="container_boxes">
            <h4>Add to Cart</h4>
            <form name="frmCart" method="POST" action="create-cart-rec.php" class="form-container">
                <div class="form-group">
                    <label for="userId">User:</label>
                    <select name="txtUserId" id="userId" class="form-control" required>
                        <option value="" disabled selected>Select User</option>
                        <?php foreach ($users as $user) { ?>
                            <option value="<?php echo $user['User_ID']; ?>"><?php echo $user['Full_Name']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="productId">Product:</label>
                    <select name="txtProductId" id="productId" class="form-control" required>
                        <option value="" disabled selected>Select Product</option>
                        <?php foreach ($products as $product) { ?>
                            <option value="<?php echo $product['Product_ID']; ?>" data-price="<?php echo $product['Price']; ?>" data-glb="<?php echo $product['GLB_File_URL']; ?>">
                                <?php echo $product['Product_Name']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>3D Model:</label>
                    <model-viewer id="modelViewer" auto-rotate camera-controls style="width: 300px; height: 300px;"></model-viewer>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="txtQuantity" id="quantity" class="form-control" value="1" required>
                </div>

                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" step="0.01" name="txtPrice" id="price" class="form-control" value="" readonly required>
                </div>

                <div class="form-group">
                    <label for="totalPrice">Total Price:</label>
                    <input type="number" step="0.01" name="txtTotalPrice" id="totalPrice" class="form-control" value="" readonly required>
                </div>

                <div class="form-group">
                    <label for="orderType">Order Type:</label>
                    <select name="txtOrderType" id="orderType" class="form-control" required>
                        <option value="ready_made" selected>Ready Made</option>
                        <option value="pre_order">Pre Order</option>
                    </select>
                </div>

                <div class="button-container">
                    <input type="submit" value="Submit" class="buttonUpdate">
                    <input type="reset" value="Reset" class="buttonDelete">
                    <a href="../cart/read-all-cart-form.php" class="buttonBack">Back to Cart List</a>
                </div>
            </form>


        <script>
            const productSelect = document.getElementById('productId');
            const quantityInput = document.getElementById('quantity');
            const totalPriceInput = document.getElementById('totalPrice');
            const priceInput = document.getElementById('price');
            const modelViewer = document.getElementById('modelViewer');

            function calculateTotalPrice() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const quantity = parseInt(quantityInput.value) || 0;

                const totalPrice = price * quantity;
                totalPriceInput.value = totalPrice.toFixed(2);
                priceInput.value = price.toFixed(2);

                //set the model if file is available
                const glbFileURL = selectedOption.getAttribute('data-glb');
                if (glbFileURL) {
                   modelViewer.setAttribute('src', glbFileURL);
                } else {
                  modelViewer.removeAttribute('src');
               }
            }

            productSelect.addEventListener('change', calculateTotalPrice);
            quantityInput.addEventListener('input', calculateTotalPrice);

            window.onload = calculateTotalPrice;

            let sidebar = document.querySelector(".sidebar");
            let sidebarBtn = document.querySelector(".sidebarBtn");
            sidebarBtn.onclick = function() {
                sidebar.classList.toggle("active");
                if (sidebar.classList.contains("active")) {
                    sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
                } else {
                    sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
                }
            };
        </script>
    </section>
</body>
</html>
