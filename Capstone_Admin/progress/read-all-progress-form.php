<?php
session_start();

include '../config/database.php';

// Initialize search variable
$search = isset($_GET['search']) ? $_GET['search'] : '';

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
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    // Updated AJAX Query: Fixed typo and improved search
    $query = "
        SELECT
            p.Progress_ID AS ID,
            p.Product_Name,
            CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
            p.Order_Type,
            p.Product_Status,
            p.Total_Price,
            p.LastUpdate,
            p.Product_ID,
            u.First_Name,
            u.Last_Name
        FROM tbl_progress p
        JOIN tbl_user_info u ON p.User_ID = u.User_ID
        WHERE (u.First_Name LIKE :search
        OR u.Last_Name LIKE :search
        OR p.Product_Name LIKE :search)
        ORDER BY p.LastUpdate DESC
    ";
    $stmt = $pdo->prepare($query);
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the table rows for AJAX requests
    echo '<table width="100%" border="1" cellspacing="5">
        <tr>
            <th>ORDER TYPE</th>
            <th>USER NAME</th>
            <th>PRODUCT NAME</th>
            <th>PRODUCT STATUS</th>
            <th>TOTAL PRICE</th>
            <th colspan="2" style="text-align: center;">ACTIONS</th>
        </tr>';
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            $productStatusPercentage = calculatePercentage($row['Product_Status']);
            echo '
            <tr>
                <td>' . htmlspecialchars($row['Order_Type']) . '</td>
                <td>' . htmlspecialchars($row['User_Name']) . '</td>
                <td>' . htmlspecialchars($row['Product_Name']) . '</td>
                <td>
                    <div class="status-bar">
                        <div class="status-bar-fill product-status-bar" style="width: ' . $productStatusPercentage . '%;">
                            ' . $productStatusPercentage . '%
                        </div>
                    </div>
                </td>
                <td>₱ ' . number_format((float) $row['Total_Price'], 2, '.', ',') . '</td>
                <td style="text-align: center;">
                    <a class="buttonView" href="read-one-progress-form.php?id=' . htmlspecialchars($row['ID']) . '&order_type=' . htmlspecialchars($row['Order_Type']) . '" target="_parent">View</a>
                </td>';
            if ($row['Product_Status'] != 100) {
                echo '<td style="text-align: center;">
                    <a class="buttonEdit" href="update-progress-form.php?id=' . htmlspecialchars($row['ID']) . '&order_type=' . htmlspecialchars($row['Order_Type']) . '" target="_parent">Edit</a>
                </td>';
            } else {
                echo '<td style="text-align: center;">Completed</td>';
            }
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7" style="text-align: center;">No progress records found matching your search.</td></tr>';
    }
    echo '</table>';
    exit;
}

// Fetch all progress records
try {
    $stmt = $pdo->prepare("
        SELECT p.*, pr.Product_Name, u.First_Name, u.Last_Name
        FROM tbl_progress p
        JOIN tbl_prod_info pr ON p.Product_ID = pr.Product_ID
        JOIN tbl_user_info u ON p.User_ID = u.User_ID
        WHERE (p.Product_Status < 100 OR (p.Product_Status = 100 AND p.Order_Received = 0))
        ORDER BY p.Date_Added DESC
    ");
    $stmt->execute();
    $progressRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

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
    <h4>PROGRESS LIST

    <a href="create-progress-form.php">Create New Pre-Order Request</a>
    </h4>

    <div class="button-container">
        <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
    </div>

    <div id="progress-list">
        <table width="100%" border="1" cellspacing="5">
            <tr>
                <th>Order Type</th>
                <th>User Name</th>
                <th>Product Name</th>
                <th>Product Status</th>
                <th>Total Price</th>
                <th colspan="2" style="text-align: center;">ACTIONS</th>
            </tr>

            <?php if (count($progressRecords) > 0): ?>
                <?php foreach ($progressRecords as $row): ?>
                    <?php
                    if (empty($row['Product_Name']) && !empty($row['Product_ID'])) {
                        $prodStmt = $pdo->prepare("SELECT Product_Name FROM tbl_prod_info WHERE Product_ID = ?");
                        $prodStmt->execute([$row['Product_ID']]);
                        $productInfo = $prodStmt->fetch(PDO::FETCH_ASSOC);
                        $row['Product_Name'] = $productInfo['Product_Name'] ?? 'N/A';
                    } elseif (empty($row['Product_Name'])) {
                        $row['Product_Name'] = 'N/A';
                    }

                    $productStatusPercentage = calculatePercentage($row['Product_Status']);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Order_Type']); ?></td>
                        <td><?php echo htmlspecialchars($row['First_Name'] . ' ' . $row['Last_Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Product_Name']); ?></td>
                        <td>
                            <div class="status-bar">
                                <div class="status-bar-fill product-status-bar" style="width: <?php echo $productStatusPercentage; ?>%;">
                                    <?php echo $productStatusPercentage; ?>%
                                </div>
                            </div>
                        </td>
                        <td>₱ <?php echo number_format((float) $row['Total_Price'], 2, '.', ','); ?></td>
                        <td style="text-align: center;">
                            <a class="buttonView" href="read-one-progress-form.php?id=<?php echo htmlspecialchars($row['Progress_ID']); ?>&order_type=<?php echo htmlspecialchars($row['Order_Type']); ?>" target="_parent">View</a>
                        </td>
                        <td style="text-align: center;">
                            <?php if ((int)$row['Product_Status'] !== 100): ?>
                                <a class="buttonEdit" href="update-progress-form.php?id=<?php echo htmlspecialchars($row['Progress_ID']); ?>&order_type=<?php echo htmlspecialchars($row['Order_Type']); ?>" target="_parent">Edit</a>
                            <?php else: ?>
                                <span class="completed-action">Completed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No progress records available.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
// Optional: Add dropdown toggle JS for profile
document.getElementById('dropdown-icon').addEventListener('click', function() {
    document.getElementById('profileDropdown').classList.toggle('show');
});
</script>

</section>
</body>
</html>


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

        // Improved AJAX Search Implementation
        const searchInput = document.getElementById('search-input');
        const progressListDiv = document.getElementById('progress-list');
        let searchTimeout;

        if (searchInput && progressListDiv) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                const searchValue = this.value.trim();

                // Set a timeout to wait after user stops typing
                searchTimeout = setTimeout(() => {
                    if (searchValue.length === 0) {
                        // If search is empty, reload the original content
                        window.location.reload();
                        return;
                    }

                    // Send an AJAX request
                    fetch(`read-all-progress-form.php?search=${encodeURIComponent(searchValue)}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(data => {
                        progressListDiv.innerHTML = data;
                    })
                    .catch(error => {
                        console.error('Error fetching search results:', error);
                        progressListDiv.innerHTML = '<div style="text-align:center; padding:20px; color:red;">Error loading results. Please try again.</div>';
                    });
                }, 500); // Increased debounce time to 500ms
            });

            // Handle form submission to prevent page reload
            const searchForm = document.getElementById('search-form');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const searchValue = searchInput.value.trim();
                    if (searchValue.length > 0) {
                        searchInput.dispatchEvent(new Event('input'));
                    }
                });
            }
        }
    </script>
</body>
</html>
