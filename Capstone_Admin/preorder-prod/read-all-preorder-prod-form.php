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


// Initialize the search variable
$search = isset($_GET['search']) ? trim($_GET['search']) : ''; // Trim whitespace

// Use the NEW Product Status labels
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

// Base query to fetch ACTIVE PRE-ORDERS from tbl_progress
$baseQuery = "
    SELECT
        p.Progress_ID,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        p.Product_Name,
        p.Total_Price,
        p.Product_Status,
        p.Date_Added -- Changed from Order_Date
    FROM tbl_progress p
    JOIN tbl_user_info u ON p.User_ID = u.User_ID
    WHERE p.Order_Type = 'pre_order' -- Filter specifically for pre-orders
";

// Append search conditions if search term exists
$queryParams = [];
if (!empty($search)) {
    $baseQuery .= " AND (u.First_Name LIKE :search OR u.Last_Name LIKE :search OR p.Product_Name LIKE :search)";
    $queryParams[':search'] = '%' . $search . '%';
}

$baseQuery .= " ORDER BY p.LastUpdate DESC"; // Order by last update

// Prepare and execute the query
$stmt = $pdo->prepare($baseQuery);
$stmt->execute($queryParams);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- AJAX Search Handling ---
// Check if the request is an AJAX search request (check for a specific parameter or header if needed)
// For simplicity, we'll re-use the main logic if 'search' is set in GET, but exit after output
if (isset($_GET['search'])) {
    // Output only the table rows for AJAX
    // Use Bootstrap classes for consistency
    echo '<table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>User Name</th>
                <th>Product Name</th>
                <th>Total Price</th>
                <th>Product Status</th>
                <th colspan="2" style="text-align: center;">ACTIONS</th> <!-- Adjusted colspan -->
            </tr>
        </thead>
        <tbody>'; // Added tbody
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            $progressID = htmlspecialchars($row["Progress_ID"]);
            $userName = htmlspecialchars($row["User_Name"]);
            $productName = htmlspecialchars($row["Product_Name"]);
            $totalPrice = number_format((float)$row["Total_Price"], 2, '.', ','); // Added comma
            $productStatusValue = $row["Product_Status"];
            $productStatusText = htmlspecialchars($productStatusLabels[$productStatusValue] ?? 'Unknown');
            $progressPercent = $productStatusValue; // Use Product_Status for percentage

            echo '
            <tr>
                <td>'.$userName.'</td>
                <td>'.$productName.'</td>
                <td>₱'.$totalPrice.'</td>
                <td>
                    <div class="progress" style="height: 20px; min-width: 150px;" title="'.$productStatusText.'"> <!-- Added min-width and title -->
                        <div class="progress-bar bg-info" role="progressbar"
                            style="width: '.$progressPercent.'%;"
                            aria-valuenow="'.$progressPercent.'"
                            aria-valuemin="0"
                            aria-valuemax="100">
                            '.$progressPercent.'%
                        </div>
                    </div>
                </td>
                <!-- Updated Links to point to /progress/ scripts -->
                <td style="text-align: center;"><a class="buttonView btn btn-sm btn-info" href="../progress/read-one-progress-form.php?id='.$progressID.'&order_type=pre_order">View</a></td>
                <td style="text-align: center;"><a class="buttonEdit btn btn-sm btn-warning" href="../progress/update-progress-form.php?id='.$progressID.'&order_type=pre_order">Edit</a></td>
                <!-- Removed Delete Link -->
            </tr>';
        }
    } else {
         echo '<tr><td colspan="6" class="text-center">No active pre-orders found matching your search.</td></tr>'; // Adjusted colspan
    }
    echo '</tbody></table>'; // Added closing tbody
    exit; // Stop further execution for AJAX requests
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Active Pre-Orders</title> <!-- Specific Title -->
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
        /* Minor adjustments for table readability */
        .table th, .table td { vertical-align: middle; text-align: center; }
        .table .progress { margin: auto; } /* Center progress bar */
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
                <span class="dashboard">Active Pre-Orders</span> <!-- Updated title -->
            </div>
            <div class="search-box">
                <!-- Use a form for better semantics, point action to the current page -->
                <form id="search-form" method="GET" action="">
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
            <h4>ACTIVE PRE-ORDER LIST</h4>
            <!-- Add Back to Dashboard button -->
            <div class="button-container mb-3"> <!-- Added margin bottom -->
                 <a href="create-preorder-prod-form.php" class="buttonCreate btn btn-primary">Create New Pre-Order Request</a>
                 <a href="../dashboard/dashboard.php" class="buttonBack btn btn-secondary">Back to Dashboard</a>
            </div>

            <div id="preorder-list" class="table-responsive"> <!-- Added ID and responsive wrapper -->
                <table class="table table-bordered table-striped"> <!-- Use Bootstrap classes -->
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Product Name</th>
                            <th>Total Price</th>
                            <th>Product Status</th> <!-- Updated Header -->
                            <th colspan="3" style="text-align: center;">ACTIONS</th> <!-- Adjusted colspan -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) > 0): ?>
                            <?php foreach ($rows as $row): ?>
                                <?php
                                $progressID = htmlspecialchars($row["Progress_ID"]);
                                $userName = htmlspecialchars($row["User_Name"]);
                                $productName = htmlspecialchars($row["Product_Name"]);
                                $totalPrice = number_format((float)$row["Total_Price"], 2, '.', ','); // Added comma
                                $productStatusValue = $row["Product_Status"];
                                $productStatusText = htmlspecialchars($productStatusLabels[$productStatusValue] ?? 'Unknown');
                                $progressPercent = $productStatusValue; // Use Product_Status for percentage
                                ?>
                                <tr>
                                    <td><?= $userName ?></td>
                                    <td><?= $productName ?></td>
                                    <td>₱<?= $totalPrice ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px; min-width: 150px;" title="<?= $productStatusText ?>"> <!-- Added min-width and title -->
                                            <div class="progress-bar bg-info" role="progressbar"
                                                style="width: <?= $progressPercent ?>%;"
                                                aria-valuenow="<?= $progressPercent ?>"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                                <?= $progressPercent ?>%
                                            </div>
                                        </div>
                                        </td>
                                             <!-- Links point to local /preorder-prod/ scripts -->
                                             <td style="text-align: center;"><a class="buttonView btn btn-sm btn-info" href="read-one-preorder-prod-form.php?id=<?= $progressID ?>">View</a></td>
                                             <td style="text-align: center;"><a class="buttonEdit btn btn-sm btn-warning" href="update-preorder-prod-form.php?id=<?= $progressID ?>">Edit</a></td>
                                             <td style="text-align: center;"><a class="buttonDelete btn btn-sm btn-danger" href="delete-preorder-prod-form.php?id=<?= $progressID ?>">Delete</a></td>
                                             </tr>
                                             <?php endforeach; ?>



                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No active pre-orders found.</td> <!-- Adjusted colspan -->
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div> <!-- End #preorder-list -->
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

        // AJAX Search Implementation (Live Search)
        const searchInput = document.getElementById('search-input');
        const preorderListDiv = document.getElementById('preorder-list'); // Target the div containing the table
        let searchTimeout; // To debounce requests

        if (searchInput && preorderListDiv) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout); // Clear previous timeout
                const searchValue = this.value;

                // Set a timeout to wait briefly after user stops typing
                searchTimeout = setTimeout(() => {
                    // Show loading indicator (optional)
                    preorderListDiv.innerHTML = '<p style="text-align:center;">Searching...</p>';

                    // Send an AJAX request - URL remains the same as it triggers the AJAX block in this script
                    fetch(`read-all-preorder-prod-form.php?search=${encodeURIComponent(searchValue)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.text();
                        })
                        .then(data => {
                            // Update the preorder list div with the new table HTML
                            preorderListDiv.innerHTML = data;
                        })
                        .catch(error => {
                            console.error('Error fetching search results:', error);
                            preorderListDiv.innerHTML = '<p style="text-align:center; color:red;">Error loading results.</p>';
                        });
                }, 300); // Wait 300ms after typing stops
            });
        }

        // Removed old dropdown toggle JS

    </script>
</body>
</html>
