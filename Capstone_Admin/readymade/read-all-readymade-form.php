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

// Updated Product Status Map (using the new one from the prompt)
$productStatusLabels = [
    0 => 'Request Approved', // 0% - Order placed by the customer
    10 => 'Design Approved', // 10% - Finalized by customer (Note: This might be more relevant for Custom/PreOrder, but included for consistency if used)
    20 => 'Payment Processing', // 20% - Or Material Sourcing if applicable
    30 => 'Order Confirmed / Cutting & Shaping', // 30% - Preparing materials / Confirmed
    40 => 'Structural Assembly / Preparing for Shipment', // 40% - Base framework built / Prep for ship
    50 => 'Shipped / Detailing & Refinements',// 50% - Carvings, elements added / Shipped
    60 => 'Out for Delivery / Sanding & Pre-Finishing',// 60% - Smoothening / Out for delivery
    70 => 'Delivered / Varnishing/Painting', // 70% - Applying the final finish / Delivered
    80 => 'Installed / Drying & Curing', // 80% - Final coating sets in / Installed
    90 => 'Final Inspection & Packaging', // 90% - Quality control before handover
    95 => 'Ready for Shipment', // 95% - Ready for handover/shipment
    98 => 'Order Delivered', // 98% - Confirmed delivery by logistics/customer
    100 => 'Order Received / Complete', // 100% - Final confirmation by customer / Order cycle complete
];


// Fetch ready-made order records from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "
    SELECT 
        rmo.ReadyMadeOrder_ID,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        p.Product_Name,
        rmo.Quantity,
        rmo.Total_Price,
        rmo.Product_Status, -- Corrected column name
        rmo.Order_Date
    FROM tbl_ready_made_orders rmo
    JOIN tbl_user_info u ON rmo.User_ID = u.User_ID
    JOIN tbl_prod_info p ON rmo.Product_ID = p.Product_ID
    WHERE u.First_Name LIKE :search 
    OR u.Last_Name LIKE :search 
    OR p.Product_Name LIKE :search
    OR rmo.Product_Status LIKE :search -- Corrected column name
";
$stmt = $pdo->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --- AJAX Search Response ---
if (isset($_GET['search'])) {
    // Use the updated $productStatusLabels here as well
    echo '<table width="100%" border="1" cellspacing="5">
        <thead>
            <tr>
                <th>USER NAME</th>
                <th>PRODUCT NAME</th>
                <th>TOTAL PRICE</th>
                <th>ORDER STATUS</th>
                <th colspan="3" style="text-align: center;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>';
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            $orderID = htmlspecialchars($row["ReadyMadeOrder_ID"]);
            $userName = htmlspecialchars($row["User_Name"]);
            $productName = htmlspecialchars($row["Product_Name"]);
            $totalPrice = number_format((float)$row["Total_Price"], 2, '.', '');
            $productStatusValue = (int)$row["Product_Status"]; // Corrected variable name
            $progressPercent = min(max($productStatusValue, 0), 100); // Use the status value directly for progress %
            // Use the new status labels array for the tooltip
            $productStatusLabel = htmlspecialchars($productStatusLabels[$productStatusValue] ?? 'Unknown Status (' . $productStatusValue . ')'); 

            echo '
            <tr>
                <td>' . $userName . '</td>
                <td>' . $productName . '</td>
                <td>' . $totalPrice . '</td>
                <td>
                    <div class="progress" style="width: 150px;" title="' . $productStatusLabel . '">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: ' . $progressPercent . '%;" aria-valuenow="' . $progressPercent . '" aria-valuemin="0" aria-valuemax="100">
                            ' . $progressPercent . '%
                        </div>
                    </div>
                    <!-- Removed the <small> tag that displayed the label text -->
                </td>
                <td style="text-align: center;"><a class="buttonView" href="read-one-readymade-form.php?id=' . $orderID . '" target="_parent">View</a></td>
                <td style="text-align: center;"><a class="buttonEdit" href="update-readymade-form.php?id=' . $orderID . '" target="_parent">Edit</a></td>
                <td style="text-align: center;"><a class="buttonDelete" href="delete-readymade-form.php?id=' . $orderID . '" target="_parent" onclick="return confirm(\'Are you sure you want to delete this order?\');">Delete</a></td>
            </tr>';
        }
    } else {
        echo '<tr><td colspan="7" style="text-align:center;">No orders found matching your search.</td></tr>';
    }
    echo '</tbody></table>';
    exit; // Stop further execution for AJAX requests
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Ready-Made Orders</title> <!-- More specific title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle for Popper -->
    <!-- Removed dashboard.js script as it wasn't provided and might conflict -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <!-- Removed link to admin_home.js as it wasn't provided -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        /* Add tooltip styling for progress bar */
        .progress[title]:hover::after {
            content: attr(title);
            position: absolute;
            background-color: #555;
            color: #fff;
            padding: 5px;
            border-radius: 3px;
            font-size: 0.8em;
            white-space: nowrap;
            z-index: 10;
            margin-top: -25px; /* Adjust as needed */
            margin-left: 10px; /* Adjust as needed */
        }
        /* Removed the td small style as the element is gone */
    </style>
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
                    <i class="bx bx-history"></i> <!-- Changed icon -->
                    <span class="links_name">All Purchase History</span>
                </a>
            </li>
            <li>
                <a href="../reviews/read-all-reviews-form.php">
                    <i class="bx bx-star"></i> <!-- Changed to star icon -->
                    <span class="links_name">All Reviews</span>
                </a>
            </li>
                </a>
            </li>
        </ul>
    </div>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Ready-Made Orders</span> <!-- Updated Title -->
            </div>
            <div class="search-box">
                <!-- Use a form for better semantics, though handled by JS -->
                <form id="searchForm" action="" method="GET">
                     <input type="text" id="searchInput" name="search" placeholder="Search User, Product, Status#..." value="<?php echo htmlspecialchars($search); ?>" />
                     <i class="bx bx-search"></i> <!-- Search Icon -->
                </form>
            </div>

            <div class="profile-details">
                 <img src="../<?php echo $profilePicPath; ?>" alt="Profile Picture" />
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
            <h4>READY-MADE ORDERS LIST <a href="create-readymade-form.php" class="buttonCreate">Create New Order</a></h4> <!-- Added class to create button -->
            <!-- Add Back to Dashboard button -->
            <div class="button-container">
                <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
            </div>
            <div id="ready-made-orders-list">
                <table width="100%" border="1" cellspacing="5">
                    <thead>
                        <tr>
                            <th>USER NAME</th>
                            <th>PRODUCT NAME</th>
                            <th>TOTAL PRICE</th>
                            <th>ORDER STATUS</th>
                            <th colspan="3" style="text-align: center;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) > 0): ?>
                            <?php foreach ($rows as $row): 
                                $productStatusValue = (int)$row["Product_Status"]; // Corrected variable
                                $progressPercent = min(max($productStatusValue, 0), 100);
                                // Use the new status labels array for the tooltip
                                $productStatusLabel = htmlspecialchars($productStatusLabels[$productStatusValue] ?? 'Unknown Status (' . $productStatusValue . ')'); 
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["User_Name"]) ?></td>
                                    <td><?= htmlspecialchars($row["Product_Name"]) ?></td>
                                    <td><?= number_format((float)$row["Total_Price"], 2, '.', '') ?></td>
                                    <td>
                                        <div class="progress" style="width: 150px;" title="<?= $productStatusLabel ?>"> <!-- Add title for tooltip -->
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $progressPercent ?>%;" aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?= $progressPercent ?>%
                                            </div>
                                        </div>
                                        <!-- Removed the <small> tag that displayed the label text -->
                                    </td>
                                    <td style="text-align: center;"><a class="buttonView" href="read-one-readymade-form.php?id=<?= htmlspecialchars($row["ReadyMadeOrder_ID"]) ?>" target="_parent">View</a></td>
                                    <td style="text-align: center;"><a class="buttonEdit" href="update-readymade-form.php?id=<?= htmlspecialchars($row["ReadyMadeOrder_ID"]) ?>" target="_parent">Edit</a></td>
                                    <td style="text-align: center;"><a class="buttonDelete" href="delete-readymade-form.php?id=<?= htmlspecialchars($row["ReadyMadeOrder_ID"]) ?>" target="_parent" onclick="return confirm('Are you sure you want to delete this order?');">Delete</a></td> <!-- Added confirmation -->
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center;">No ready-made orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </section>

<script>
    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".sidebarBtn");
    let profileDetails = document.querySelector(".profile-details");
    let dropdown = document.getElementById("profileDropdown");
    let searchInput = document.getElementById("searchInput");
    let readyMadeOrdersList = document.getElementById('ready-made-orders-list');

    sidebarBtn.onclick = function () {
        sidebar.classList.toggle("active");
        if (sidebar.classList.contains("active")) {
            sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
        } else {
            sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
        }
    };

    profileDetails.onclick = function() {
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        // Toggle chevron direction (optional)
        const chevron = profileDetails.querySelector('.bx-chevron-down');
        if (dropdown.style.display === 'block') {
            chevron.classList.add('bx-chevron-up');
            chevron.classList.remove('bx-chevron-down');
        } else {
            chevron.classList.remove('bx-chevron-up');
            chevron.classList.add('bx-chevron-down');
        }
    }

    // Close dropdown if clicking outside
    window.onclick = function(event) {
      if (!profileDetails.contains(event.target)) {
        dropdown.style.display = 'none';
        const chevron = profileDetails.querySelector('.bx-chevron-up');
        if(chevron){
             chevron.classList.remove('bx-chevron-up');
             chevron.classList.add('bx-chevron-down');
        }
      }
    }

    // --- AJAX Search ---
    let searchTimeout; // To debounce search requests

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout); // Clear previous timeout
        const searchValue = this.value.trim();

        // Set a timeout to wait briefly after user stops typing
        searchTimeout = setTimeout(() => {
            const url = `read-all-readymade-form.php?search=${encodeURIComponent(searchValue)}`;

            // Show loading indicator (optional)
            readyMadeOrdersList.innerHTML = '<p style="text-align:center;">Loading...</p>'; 

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(data => {
                    // The response should be the HTML of the table section
                    readyMadeOrdersList.innerHTML = data; 
                })
                .catch(error => {
                    console.error('Error fetching search results:', error);
                    readyMadeOrdersList.innerHTML = '<p style="text-align:center; color: red;">Error loading results.</p>'; // Show error
                });
        }, 300); // Wait 300ms after typing stops
    });

    // Prevent form submission on enter key for search, as it's handled by JS
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault(); 
    });

</script>
</body>
</html>
