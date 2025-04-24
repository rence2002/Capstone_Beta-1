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
// Construct the correct path relative to the web root if PicPath doesn't start with '../' or '/'
$profilePicPath = $admin['PicPath'];
if (!preg_match('/^(\.\.\/|\/)/', $profilePicPath)) {
    // Assuming PicPath is relative to the Capstone_Admin directory
    $profilePicPath = '../' . $profilePicPath;
}
$profilePicPath = htmlspecialchars($profilePicPath);


// Check if the request is an AJAX search request
if (isset($_GET['search']) && !empty($_GET['search'])) { // Check if search is not empty
    $search = $_GET['search'];
    // Updated AJAX Query: Removed Order_Status
    $query = "
        SELECT
            Progress_ID AS ID,
            Product_Name,
            CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
            Order_Type,
            -- Order_Status, -- REMOVED
            Product_Status,
            Total_Price,
            LastUpdate
        FROM tbl_progress p
        JOIN tbl_user_info u ON p.User_ID = u.User_ID
        WHERE (u.First_Name LIKE :search
        OR u.Last_Name LIKE :search
        OR p.Product_Name LIKE :search)
        ORDER BY LastUpdate DESC
    ";
    $stmt = $pdo->prepare($query);
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the table rows for AJAX requests
    // Updated AJAX Table: Removed Order Status column, adjusted colspan, updated Edit condition
    echo '<table width="100%" border="1" cellspacing="5">
        <tr>
            <th>ORDER TYPE</th>
            <th>USER NAME</th>
            <th>PRODUCT NAME</th>
            <!-- <th>ORDER STATUS</th> --> <!-- REMOVED -->
            <th>PRODUCT STATUS</th>
            <th>TOTAL PRICE</th>
            <th colspan="2" style="text-align: center;">ACTIONS</th> <!-- Adjusted colspan -->
        </tr>';
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            // $orderStatusPercentage = calculatePercentage($row['Order_Status']); // REMOVED
            $productStatusPercentage = calculatePercentage($row['Product_Status']);
            echo '
            <tr>
                <td>' . htmlspecialchars($row['Order_Type']) . '</td>
                <td>' . htmlspecialchars($row['User_Name']) . '</td>
                <td>' . htmlspecialchars($row['Product_Name']) . '</td>
                <!--<td>
                    <div class="status-bar">
                        <div class="status-bar-fill order-status-bar" style="width: ' . $orderStatusPercentage . '%;">
                            ' . $orderStatusPercentage . '%
                        </div>
                    </div>
                </td>--> <!-- REMOVED -->
                <td>
                    <div class="status-bar">
                        <div class="status-bar-fill product-status-bar" style="width: ' . $productStatusPercentage . '%;">
                            ' . $productStatusPercentage . '%
                        </div>
                    </div>
                </td>
                <td>₱ ' . number_format((float) $row['Total_Price'], 2, '.', ',') . '</td> <!-- Added currency format -->
                <td style="text-align: center;"><a class="buttonView" href="read-one-progress-form.php?id=' . htmlspecialchars($row['ID']) . '&order_type=' . htmlspecialchars($row['Order_Type']) . '" target="_parent">View</a></td>';
            // Updated Edit condition to use Product_Status
            if ($row['Product_Status'] != 100) {
                echo '<td style="text-align: center;"><a class="buttonEdit" href="update-progress-form.php?id=' . htmlspecialchars($row['ID']) . '&order_type=' . htmlspecialchars($row['Order_Type']) . '" target="_parent">Edit</a></td>';
            } else {
                echo '<td style="text-align: center;">Completed</td>'; // Indicate completed instead of empty cell
            }
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6" style="text-align: center;">No progress records found matching your search.</td></tr>'; // Adjusted colspan
    }
    echo '</table>';
    exit; // Stop further execution for AJAX requests
}

// Fetch all progress records from tbl_progress (or filtered if search term exists)
$search = isset($_GET['search']) ? $_GET['search'] : '';
// Updated Main Query: Removed Order_Status
$query = "
    SELECT
        p.Progress_ID AS ID,
        p.Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        p.Order_Type,
        -- p.Order_Status, -- REMOVED
        p.Product_Status,
        p.Total_Price,
        p.LastUpdate,
        p.Product_ID -- Keep Product_ID in case Product_Name is missing
    FROM tbl_progress p
    JOIN tbl_user_info u ON p.User_ID = u.User_ID
";

// Add WHERE clause only if search term is provided
if (!empty($search)) {
    $query .= "
        WHERE (u.First_Name LIKE :search
        OR u.Last_Name LIKE :search
        OR p.Product_Name LIKE :search)
    ";
}

$query .= " ORDER BY p.LastUpdate DESC"; // Use alias p for LastUpdate

$stmt = $pdo->prepare($query);

// Bind search parameter only if it exists
if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}

$stmt->execute(); // This was line 122 causing the error
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// REMOVED Order Status Map - Not needed as column doesn't exist
// $orderStatusLabels = [ ... ];

// Updated Product Status Map (using the one from the request)
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


// Function to calculate percentage (remains the same)
function calculatePercentage($status) {
    // Ensure status is treated as a number
    $status = (int) $status;
    // Basic percentage calculation, assuming status is already 0-100
    return max(0, min(100, $status));
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Progress List</title> <!-- More specific title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <!-- <script src="../static/js/dashboard.js"></script> --> <!-- Consider removing if not used -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="../static/css-files/progress.css" rel="stylesheet"> <!-- Keep progress CSS -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        /* Add specific styles if needed */
        .status-bar {
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            height: 20px; /* Adjust height as needed */
            position: relative; /* Needed for text overlay */
        }
        .status-bar-fill {
            background-color: #4CAF50; /* Green for progress */
            height: 100%;
            text-align: center;
            color: white;
            line-height: 20px; /* Match height */
            font-size: 12px;
            transition: width 0.5s ease-in-out;
        }
        .product-status-bar { background-color: #2196F3; } /* Blue for product status */
        /* Optional: Style for completed status */
        td.completed-action { color: #777; font-style: italic; }
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
                <span class="dashboard">Progress List</span> <!-- Updated title -->
            </div>
            <div class="search-box">
                <!-- Use a form for better semantics, point action to the current page -->
                <form id="search-form" method="GET" action="read-all-progress-form.php">
                    <input type="text" id="search-input" name="search" placeholder="Search User or Product..." value="<?php echo htmlspecialchars($search); ?>" />
                    <!-- Optional: Add a submit button if you don't want live search -->
                    <!-- <button type="submit"><i class='bx bx-search'></i></button> -->
                </form>
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
            <h4>PROGRESS LIST</h4>
            <!-- Add Back to Dashboard button -->
            <div class="button-container mb-3"> <!-- Added margin bottom -->
                <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                <!-- Removed Create New Progress link as it seems broken based on create-progress-rec.php -->
                <!-- <a href="create-progress-form.php" class="buttonCreate">Create New Progress</a> -->
            </div>
            <div id="progress-list">
                <!-- Updated Main Table: Removed Order Status column, adjusted colspan, updated Edit condition -->
                <table class="table table-bordered table-striped"> <!-- Added bootstrap classes -->
                    <thead>
                        <tr>
                            <th>ORDER TYPE</th>
                            <th>USER NAME</th>
                            <th>PRODUCT NAME</th>
                            <!-- <th>ORDER STATUS</th> --> <!-- REMOVED -->
                            <th>PRODUCT STATUS</th>
                            <th>TOTAL PRICE</th>
                            <th colspan="2" style="text-align: center;">ACTIONS</th> <!-- Adjusted colspan -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) > 0): ?>
                            <?php foreach ($rows as $row): ?>
                                <?php
                                // Fetch Product_Name dynamically ONLY if it's empty in tbl_progress (fallback)
                                if (empty($row['Product_Name']) && !empty($row['Product_ID'])) {
                                    $prodStmt = $pdo->prepare("SELECT Product_Name FROM tbl_prod_info WHERE Product_ID = ?");
                                    $prodStmt->execute([$row['Product_ID']]);
                                    $productInfo = $prodStmt->fetch(PDO::FETCH_ASSOC);
                                    $row['Product_Name'] = $productInfo['Product_Name'] ?? 'N/A';
                                } elseif (empty($row['Product_Name'])) {
                                     $row['Product_Name'] = 'N/A'; // Handle case where both are missing
                                }

                                $productStatusPercentage = calculatePercentage($row['Product_Status']);
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['Order_Type']) ?></td>
                                    <td><?= htmlspecialchars($row['User_Name']) ?></td>
                                    <td><?= htmlspecialchars($row['Product_Name']) ?></td>
                                    <!--<td>
                                        <div class="status-bar">
                                            <div class="status-bar-fill order-status-bar" style="width: <?= calculatePercentage($row['Order_Status']) ?>%;">
                                                <?= calculatePercentage($row['Order_Status']) ?>%
                                            </div>
                                        </div>
                                    </td>--> <!-- REMOVED -->
                                    <td>
                                        <div class="status-bar" title="<?= $productStatusLabels[$row['Product_Status']] ?? 'Unknown Status' ?>"> <!-- Added title attribute -->
                                            <div class="status-bar-fill product-status-bar" style="width: <?= $productStatusPercentage ?>%;">
                                                <?= $productStatusPercentage ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>₱ <?= number_format((float) $row['Total_Price'], 2, '.', ',') ?></td> <!-- Added currency format -->
                                    <td style="text-align: center;"><a class="buttonView btn btn-sm btn-info" href="read-one-progress-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>">View</a></td>
                                    <?php // Updated Edit condition to use Product_Status ?>
                                    <?php if($row['Product_Status'] != 100): ?>
                                        <td style="text-align: center;"><a class="buttonEdit btn btn-sm btn-warning" href="update-progress-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>">Edit</a></td>
                                    <?php else: ?>
                                        <td style="text-align: center;" class="completed-action">Completed</td> <!-- Indicate completed -->
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No progress records found.</td> <!-- Adjusted colspan -->
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
        const profileDetailsContainer = document.getElementById('profile-details-container');
        const profileDropdown = document.getElementById('profileDropdown');
        const dropdownIcon = document.getElementById('dropdown-icon');

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

        // AJAX Search Implementation (Live Search)
        const searchInput = document.getElementById('search-input');
        const progressListDiv = document.getElementById('progress-list');
        let searchTimeout; // To debounce requests

        if (searchInput && progressListDiv) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout); // Clear previous timeout
                const searchValue = this.value;

                // Set a timeout to wait briefly after user stops typing
                searchTimeout = setTimeout(() => {
                    // Show loading indicator (optional)
                    progressListDiv.innerHTML = '<p style="text-align:center;">Searching...</p>';

                    // Send an AJAX request
                    fetch(`read-all-progress-form.php?search=${encodeURIComponent(searchValue)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.text();
                        })
                        .then(data => {
                            // Update the progress list with the filtered results
                            progressListDiv.innerHTML = data;
                        })
                        .catch(error => {
                            console.error('Error fetching search results:', error);
                            progressListDiv.innerHTML = '<p style="text-align:center; color:red;">Error loading results.</p>';
                        });
                }, 300); // Wait 300ms after typing stops
            });
        }

        // Removed old dropdown toggle JS as it wasn't needed
    </script>
</body>
</html>
