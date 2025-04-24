<?php
session_start();

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
// Construct the correct path relative to the web root if PicPath doesn't start with '../' or '/'
$profilePicPath = $admin['PicPath'];
if (!preg_match('/^(\.\.\/|\/)/', $profilePicPath)) {
    // Assuming PicPath is relative to the Capstone_Admin directory
    $profilePicPath = '../' . $profilePicPath;
}
$profilePicPath = htmlspecialchars($profilePicPath);


// Initialize the search variable
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Status Labels (Simplified - Using Product Status for both columns now)
$statusLabels = [
    0   => 'Request Approved / Concept', // Combined potential meanings
    10  => 'Design Approved',
    20  => 'Material Sourcing / Prep', // Combined
    30  => 'Cutting & Shaping',
    40  => 'Structural Assembly',
    50  => 'Detailing & Refinements / Mid-Prod', // Combined
    60  => 'Sanding & Pre-Finishing',
    70  => 'Varnishing/Painting / Final Coat', // Combined
    80  => 'Drying & Curing / Assembly & Test', // Combined
    90  => 'Final Inspection / Ready', // Combined
    95  => 'Ready for Shipment', // Specific to custom? Keep if needed.
    98  => 'Order Delivered', // Specific to custom? Keep if needed.
    100 => 'Completed / Received / Sold', // Combined final states
];


// Base query parts - MODIFIED
$customQueryPart = "
    SELECT
        'custom' AS Order_Type,
        c.Customization_ID AS ID,
        c.Furniture_Type AS Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        c.Product_Status AS Status_Value, -- Use Product_Status
        c.Product_Status AS Product_Status_Value, -- Keep for clarity if needed, or remove
        p.Price AS Price, -- This might be 0.00 for custom, consider fetching calculated price if available elsewhere
        c.Last_Update AS Last_Update,
        c.User_ID
    FROM tbl_customizations c
    JOIN tbl_user_info u ON c.User_ID = u.User_ID
    LEFT JOIN tbl_prod_info p ON c.Product_ID = p.Product_ID -- Product_ID might be null initially
";

$preorderQueryPart = "
    SELECT
        'pre_order' AS Order_Type,
        po.Preorder_ID AS ID,
        pr.Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        po.Product_Status AS Status_Value, -- Use Product_Status
        po.Product_Status AS Product_Status_Value, -- Keep for clarity if needed, or remove
        po.Total_Price AS Price, -- Use Total_Price from preorder table
        po.Order_Date AS Last_Update, -- Use Order_Date as Last_Update
        u.User_ID
    FROM tbl_preorder po
    JOIN tbl_user_info u ON po.User_ID = u.User_ID
    JOIN tbl_prod_info pr ON po.Product_ID = pr.Product_ID
";

$readyMadeQueryPart = "
    SELECT
        'ready_made' AS Order_Type,
        rmo.ReadyMadeOrder_ID AS ID,
        pr.Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        rmo.Product_Status AS Status_Value, -- Use Product_Status
        rmo.Product_Status AS Product_Status_Value, -- Keep for clarity if needed, or remove
        rmo.Total_Price AS Price, -- Use Total_Price from ready_made table
        rmo.Order_Date AS Last_Update, -- Use Order_Date as Last_Update
        u.User_ID
    FROM tbl_ready_made_orders rmo
    JOIN tbl_user_info u ON rmo.User_ID = u.User_ID
    JOIN tbl_prod_info pr ON rmo.Product_ID = pr.Product_ID
";

// Add WHERE clauses - MODIFIED (Use Product_Status = 100 for history)
$customWhere = "WHERE c.Product_Status = 100";
$preorderWhere = "WHERE po.Product_Status = 100";
$readyMadeWhere = "WHERE rmo.Product_Status = 100";

// Add search conditions if search term exists
if (!empty($search)) {
    // Ensure search conditions target correct aliases and columns
    $searchConditionCustom = " AND (u.First_Name LIKE :searchCustom OR u.Last_Name LIKE :searchCustom OR c.Furniture_Type LIKE :searchCustom OR c.Customization_ID LIKE :searchCustom)";
    $searchConditionPreorder = " AND (u.First_Name LIKE :searchPreorder OR u.Last_Name LIKE :searchPreorder OR pr.Product_Name LIKE :searchPreorder OR po.Preorder_ID LIKE :searchPreorder)";
    $searchConditionReadyMade = " AND (u.First_Name LIKE :searchReadyMade OR u.Last_Name LIKE :searchReadyMade OR pr.Product_Name LIKE :searchReadyMade OR rmo.ReadyMadeOrder_ID LIKE :searchReadyMade)";

    $customWhere .= $searchConditionCustom;
    $preorderWhere .= $searchConditionPreorder;
    $readyMadeWhere .= $searchConditionReadyMade;
}

// Combine the queries
$query = $customQueryPart . $customWhere . " UNION ALL " . // Use UNION ALL if duplicates are acceptable and potentially faster
         $preorderQueryPart . $preorderWhere . " UNION ALL " .
         $readyMadeQueryPart . $readyMadeWhere . " ORDER BY Last_Update DESC";


$stmt = $pdo->prepare($query);

// Bind search parameters separately for each part of the UNION
if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    // Bind parameters with unique names
    $stmt->bindParam(':searchCustom', $searchParam, PDO::PARAM_STR);
    $stmt->bindParam(':searchPreorder', $searchParam, PDO::PARAM_STR);
    $stmt->bindParam(':searchReadyMade', $searchParam, PDO::PARAM_STR);
}

// *** Line 138 where the error occurred ***
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle AJAX requests to return only table rows
if (isset($_GET['ajax_search'])) { // Changed parameter name to avoid conflict
    ob_start(); // Start output buffering
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            // Use Total_Price directly if available, format it
             $totalPrice = isset($row['Price']) ? number_format((float) $row['Price'], 2, '.', '') : 'N/A';
             $statusValue = $row['Status_Value'] ?? null; // Get the status value
             $statusLabel = isset($statusValue) ? ($statusLabels[$statusValue] ?? "Unknown ($statusValue)") : "Unknown";

            echo '
            <tr>
                <td>' . htmlspecialchars($row['Order_Type']) . '</td>
                <td>' . htmlspecialchars($row['User_Name']) . '</td>
                <td>' . htmlspecialchars($row['Product_Name']) . '</td>
                <td>' . htmlspecialchars($statusLabel) . '</td> <!-- Display Status Label -->
                <td>' . htmlspecialchars($statusLabel) . '</td> <!-- Display Status Label again (as Product Status) -->
                <td>' . $totalPrice . '</td>
                <td style="text-align: center;">
                    <a class="buttonView" href="read-one-history-form.php?id=' . htmlspecialchars($row['ID']) . '&order_type=' . htmlspecialchars($row['Order_Type']) . '" target="_parent">View</a>
                    <a class="buttonDelete" href="delete-history-form.php?id=' . htmlspecialchars($row['ID']) . '&order_type=' . htmlspecialchars($row['Order_Type']) . '" target="_parent">Delete</a>
                </td>
            </tr>';
        }
    } else {
        echo '<tr><td colspan="7" style="text-align:center;">No records found.</td></tr>';
    }
    $tableContent = ob_get_clean(); // Get buffered content
    echo $tableContent; // Send it back
    exit; // Stop further execution for AJAX requests
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Purchase History</title> <!-- More specific title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ensure bootstrap.bundle.min.js is loaded if dropdowns need Popper -->
    <script src="../static/js/bootstrap.bundle.min.js"></script>
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        /* Add specific style for delete button if not in button.css */
        .buttonDelete {
            background-color: #dc3545; /* Red */
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 0.9em;
            margin-left: 5px; /* Add some space between buttons */
        }
        .buttonDelete:hover {
            background-color: #c82333; /* Darker red */
            color: white;
            text-decoration: none;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        /* Style for profile dropdown */
        .profile-details .dropdown {
            display: none; /* Hidden by default */
            position: absolute;
            right: 0;
            top: 100%; /* Position below the profile details */
            background-color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 4px;
            overflow: hidden; /* Ensures border-radius applies to children */
            z-index: 1000; /* Ensure it's above other content */
            min-width: 120px; /* Optional: set a minimum width */
        }
        .profile-details .dropdown a {
            display: block;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            font-size: 0.9em;
        }
        .profile-details .dropdown a:hover {
            background-color: #f2f2f2;
        }
        .profile-details {
            position: relative; /* Needed for absolute positioning of dropdown */
            cursor: pointer; /* Indicate it's clickable */
        }

    </style>
</head>

<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE"  class="logo_image"> <!-- Use logo_image class -->
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
                <a href="../purchase-history/read-all-history-form.php" class="active">
                    <i class="bx bx-history"></i> <!-- Changed icon -->
                    <span class="links_name">All Purchase History</span>
                </a>
            </li>
            <li>
                <a href="../reviews/read-all-reviews-form.php">
                    <i class="bx bx-message-dots"></i>
                    <span class="links_name">All Reviews</span>
                </a>
            </li>
             <!-- Add other nav links as needed -->
        </ul>
    </div>

    <section class="home-section">
    <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Purchase History</span> <!-- Updated title -->
            </div>
            <div class="search-box">
                 <!-- Removed form tag, handled by JS -->
                 <input type="text" id="searchInput" placeholder="Search by Name, Product, ID..." value="<?php echo htmlspecialchars($search); ?>" />
                 <i class="bx bx-search"></i>
            </div>

            <!-- Profile Details Container -->
            <div class="profile-details" id="profile-details-container">
                <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button" id="dropdown-icon"></i>
                <!-- Dropdown Menu -->
                <div class="dropdown" id="profileDropdown">
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>
    </nav>
        <br><br><br>

        <div class="container_boxes">
            <h4>COMPLETED PURCHASE HISTORY LIST</h4>

             <!-- Display Success/Error Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>


            <!-- Add Back to Dashboard button -->
            <div class="button-container" style="margin-bottom: 15px;"> <!-- Added margin -->
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
            </div>
            <div id="purchase-history-list">
                <table width="100%" border="1" cellspacing="0" cellpadding="5"> <!-- Adjusted table style -->
                    <thead>
                        <tr>
                            <th>ORDER TYPE</th>
                            <th>USER NAME</th>
                            <th>PRODUCT NAME</th>
                            <th>STATUS</th> <!-- Simplified Status Column -->
                            <th>PRODUCT STATUS</th> <!-- Kept for consistency, shows same as Status -->
                            <th>TOTAL PRICE</th>
                            <th style="text-align: center;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) > 0): ?>
                            <?php foreach ($rows as $row): ?>
                                <?php
                                    // Use Total_Price directly if available, format it
                                    $totalPrice = isset($row['Price']) ? number_format((float) $row['Price'], 2, '.', '') : 'N/A';
                                    $statusValue = $row['Status_Value'] ?? null; // Get the status value
                                    $statusLabel = isset($statusValue) ? ($statusLabels[$statusValue] ?? "Unknown ($statusValue)") : "Unknown";
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['Order_Type']) ?></td>
                                    <td><?= htmlspecialchars($row['User_Name']) ?></td>
                                    <td><?= htmlspecialchars($row['Product_Name']) ?></td>
                                    <td><?= htmlspecialchars($statusLabel) ?></td> <!-- Display Status Label -->
                                    <td><?= htmlspecialchars($statusLabel) ?></td> <!-- Display Status Label again -->
                                    <td><?= $totalPrice ?></td>
                                    <td style="text-align: center;">
                                        <a class="buttonView" href="read-one-history-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>" target="_parent">View</a>
                                        <a class="buttonDelete" href="delete-history-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>" target="_parent">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                             <tr><td colspan="7" style="text-align:center;">No records found matching your search criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Link to External JS -->
    <!-- <script src="../static/js/dashboard.js"></script> --> <!-- Commented out if functionality is below -->

    <script>
        // Sidebar Toggle (Consistent version)
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        if (sidebar && sidebarBtn) {
            sidebarBtn.onclick = function () {
                sidebar.classList.toggle("active");
                // Toggle icon class based on sidebar state
                sidebarBtn.classList.toggle("bx-menu-alt-right", sidebar.classList.contains("active"));
            };
        }

        // Profile Dropdown Toggle (Consistent version)
        const profileDetailsContainer = document.getElementById('profile-details-container');
        const profileDropdown = document.getElementById('profileDropdown');
        const dropdownIcon = document.getElementById('dropdown-icon'); // Assuming you have an icon element

        if (profileDetailsContainer && profileDropdown) {
            profileDetailsContainer.addEventListener('click', function(event) {
                // Prevent dropdown from closing if clicking inside it, unless clicking a link
                if (!event.target.closest('a')) {
                     profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
                     // Toggle chevron icon if it exists
                     if (dropdownIcon) {
                         dropdownIcon.classList.toggle('bx-chevron-up', profileDropdown.style.display === 'block');
                     }
                }
                // Allow clicks on links within the dropdown to proceed
            });

            // Close dropdown if clicking outside
            document.addEventListener('click', function(event) {
                if (!profileDetailsContainer.contains(event.target)) {
                    profileDropdown.style.display = 'none';
                    // Reset chevron icon if it exists
                    if (dropdownIcon) {
                        dropdownIcon.classList.remove('bx-chevron-up');
                    }
                }
            });
        }


        // AJAX Search Implementation
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.querySelector('#purchase-history-list table tbody');

        let debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchValue = this.value.trim();
                // Use ajax_search=1 to trigger the AJAX response in PHP
                const url = `read-all-history-form.php?ajax_search=1&search=${encodeURIComponent(searchValue)}`;

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(data => {
                        if (tableBody) {
                            tableBody.innerHTML = data.trim(); // Replace the table body content
                        } else {
                            console.error("Table body not found for updating.");
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching search results:', error);
                         if (tableBody) {
                             tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center; color: red;">Error loading data.</td></tr>';
                         }
                    });
            }, 300); // Debounce search requests (300ms delay)
        });

    </script>
</body>
</html>
