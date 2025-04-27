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
$baseUrl = 'http://localhost/Capstone_Beta/';
$profilePicPath = $admin['PicPath'];
// Remove any leading slashes or duplicate directories
$profilePicPath = preg_replace('/^[\/]*(Capstone_Beta\/)?(Capstone_Admin\/)?(admin\/)?/', '', $profilePicPath);
$profilePicPath = htmlspecialchars($profilePicPath);


// Initialize the search variable
$search = isset($_GET['search']) ? trim($_GET['search']) : ''; // Trim search term

// --- Query Logic Correction ---
// Query completed orders from tbl_purchase_history
$query = "
    SELECT 
        ph.Purchase_ID AS ID,
        ph.Order_Type,
        ph.Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        ph.Product_Status AS Status_Value,
        ph.Total_Price AS Price,
        ph.Purchase_Date AS Last_Update,
        ph.User_ID,
        ph.Product_ID
    FROM tbl_purchase_history ph
    JOIN tbl_user_info u ON ph.User_ID = u.User_ID
    WHERE ph.Product_Status = 100
";

// Add search conditions if search term exists
$params = []; // Array to hold query parameters
if (!empty($search)) {
    $query .= " AND (u.First_Name LIKE :search OR u.Last_Name LIKE :search OR ph.Product_Name LIKE :search OR ph.Purchase_ID LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

// Add ordering
$query .= " ORDER BY ph.Purchase_Date DESC";

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->execute($params); // Pass the parameters array
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle AJAX requests to return only table rows
if (isset($_GET['ajax_search'])) {
    ob_start(); // Start output buffering
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            // Format price
            $totalPrice = isset($row['Price']) ? '₱' . number_format((float) $row['Price'], 2, '.', ',') : 'N/A';
            // Since this is history, status is always completed
            $statusLabel = 'Completed';

            echo '
            <tr>
                <td>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $row['Order_Type']))) . '</td>
                <td>' . htmlspecialchars($row['User_Name']) . '</td>
                <td>' . htmlspecialchars($row['Product_Name']) . '</td>
                <td>' . htmlspecialchars($statusLabel) . '</td>
                <td>' . $totalPrice . '</td>
                <td style="text-align: center;">
                    <a class="buttonView" href="read-one-history-form.php?id=' . htmlspecialchars($row['ID']) . '&order_type=' . urlencode($row['Order_Type']) . '" target="_parent">View</a>
                    <a class="buttonDelete" href="delete-history-form.php?id=' . htmlspecialchars($row['ID']) . '&order_type=' . urlencode($row['Order_Type']) . '" target="_parent" onclick="return confirm(\'Are you sure you want to delete this history record?\');">Delete</a>
                </td>
            </tr>';
        }
    } else {
        echo '<tr><td colspan="6" style="text-align:center;">No purchase history records found' . (!empty($search) ? ' matching your search' : '') . '.</td></tr>';
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
    <title>Admin Dashboard - Purchase History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
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
            display: inline-block; /* Ensure proper spacing */
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
        /* Ensure table layout is consistent */
        #purchase-history-list table {
            table-layout: fixed; /* Helps with column widths */
            width: 100%;
        }
        #purchase-history-list th,
        #purchase-history-list td {
            word-wrap: break-word; /* Prevent long text from breaking layout */
            vertical-align: middle; /* Align content vertically */
        }
        /* Adjust column widths as needed */
        #purchase-history-list th:nth-child(1), /* Order Type */
        #purchase-history-list td:nth-child(1) { width: 10%; }
        #purchase-history-list th:nth-child(2), /* User Name */
        #purchase-history-list td:nth-child(2) { width: 20%; }
        #purchase-history-list th:nth-child(3), /* Product Name */
        #purchase-history-list td:nth-child(3) { width: 25%; }
        #purchase-history-list th:nth-child(4), /* Status */
        #purchase-history-list td:nth-child(4) { width: 10%; }
        #purchase-history-list th:nth-child(5), /* Total Price */
        #purchase-history-list td:nth-child(5) { width: 15%; }
        #purchase-history-list th:nth-child(6), /* Actions */
        #purchase-history-list td:nth-child(6) { width: 20%; text-align: center; }


    </style>
</head>

<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE"  class="logo_image">
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
                    <i class="bx bx-history"></i>
                    <span class="links_name">Purchase History</span>
                </a>
            </li>
            <li>
                <a href="../reviews/read-all-reviews-form.php">
                    <i class="bx bx-message-dots"></i>
                    <span class="links_name">All Reviews</span>
                </a>
            </li>
             <!-- Add other relevant nav links -->
        </ul>
    </div>

    <section class="home-section">
    <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Purchase History</span>
            </div>
            <div class="search-box">
                <!-- Search form -->
                <form id="search-form" method="GET" action="">
                    <input type="text" id="searchInput" name="search" placeholder="Search User, Product, or ID..." value="<?php echo htmlspecialchars($search); ?>" />
                    <!-- Removed search button as AJAX handles it on input -->
                </form>
            </div>

            <!-- Profile Details Container -->
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
            <h4>COMPLETED PURCHASE HISTORY LIST</h4>

             <!-- Display Success/Error Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>


            <!-- Add Back to Dashboard button -->
            <div class="button-container" style="margin-bottom: 15px;">
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
            </div>
            <div id="purchase-history-list">
                <table width="100%" border="1" cellspacing="0" cellpadding="5">
                    <thead>
                        <tr>
                            <th>ORDER TYPE</th>
                            <th>USER NAME</th>
                            <th>PRODUCT NAME</th>
                            <th>STATUS</th> <!-- Simplified Status Column -->
                            <th>TOTAL PRICE</th>
                            <th style="text-align: center;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) > 0): ?>
                            <?php foreach ($rows as $row): ?>
                                <?php
                                    // Format price
                                    $totalPrice = isset($row['Price']) ? '₱' . number_format((float) $row['Price'], 2, '.', ',') : 'N/A';
                                    // Status is always completed here
                                    $statusLabel = 'Completed';
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $row['Order_Type']))) ?></td>
                                    <td><?= htmlspecialchars($row['User_Name']) ?></td>
                                    <td><?= htmlspecialchars($row['Product_Name']) ?></td>
                                    <td><?= htmlspecialchars($statusLabel) ?></td>
                                    <td><?= $totalPrice ?></td>
                                    <td style="text-align: center;">
                                        <a class="buttonView" href="read-one-history-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>" target="_parent">View</a>
                                        <a class="buttonDelete" href="delete-history-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>" target="_parent">Delete</a>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                             <tr><td colspan="6" style="text-align:center;">No purchase history records found<?= !empty($search) ? ' matching your search' : '' ?>.</td></tr> <!-- Adjusted colspan -->
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
        // Sidebar Toggle (Consistent version)
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        if (sidebar && sidebarBtn) {
            sidebarBtn.onclick = function () {
                sidebar.classList.toggle("active");
                sidebarBtn.classList.toggle("bx-menu-alt-right", sidebar.classList.contains("active"));
            };
        }

        // Profile Dropdown Toggle (Consistent version)
        const profileDetailsContainer = document.querySelector('.profile-details');
        const profileDropdown = document.getElementById('profileDropdown');
        const dropdownIcon = document.querySelector('.dropdown-button');

        if (profileDetailsContainer && profileDropdown) {
            profileDetailsContainer.addEventListener('click', function(event) {
                if (!event.target.closest('a')) {
                     profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
                     if (dropdownIcon) {
                         dropdownIcon.classList.toggle('bx-chevron-up', profileDropdown.style.display === 'block');
                     }
                }
            });

            document.addEventListener('click', function(event) {
                if (!profileDetailsContainer.contains(event.target)) {
                    profileDropdown.style.display = 'none';
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
                             tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; color: red;">Error loading data.</td></tr>'; // Adjusted colspan
                         }
                    });
            }, 300); // Debounce search requests (300ms delay)
        });

    </script>
</body>
</html>
