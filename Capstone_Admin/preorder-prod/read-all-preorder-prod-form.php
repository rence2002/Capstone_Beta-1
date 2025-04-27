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
$baseUrl = 'http://localhost/Capstone_Beta/';

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

// Add this function before the HTML section
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
    <title>Admin Dashboard - Active Pre-Orders</title> <!-- Specific Title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        /* Minor adjustments for table readability */
        .table th, .table td { vertical-align: middle; text-align: center; }
        .table .progress { margin: auto; } /* Center progress bar */
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
      </ul>
    </div>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Active Pre-Orders</span> <!-- Updated title -->
            </div>
            <div class="search-box">
                <!-- Search form -->
                <form id="search-form" method="GET" action="">
                    <input type="text" id="search-input" name="search" placeholder="Search User or Product..." value="<?php echo htmlspecialchars($search); ?>" />
                </form>
            </div>
            <div class="profile-details" onclick="toggleDropdown()">
                <img src="<?php echo $baseUrl . $profilePicPath; ?>" alt="Profile Picture" />
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
            <h4>ACTIVE PRE-ORDER LIST
                <a href="create-preorder-prod-form.php">Create New Pre-Order Request</a>
            </h4>

            <div class="button-container">
                <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
            </div>

            <div id="preorder-list">
                <table width="100%" border="1" cellspacing="5">
                    <tr>
                        <th>USER NAME</th>
                        <th>RODUCT NAME</th>
                        <th>TOTAL PRICE</th>
                        <th>PRODUCT STATUS</th>
                        <th colspan="3" style="text-align: center;">ACTIONS</th>
                    </tr>

                    <?php if (count($rows) > 0): ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["User_Name"]) ?></td>
                                <td><?= htmlspecialchars($row["Product_Name"]) ?></td>
                                <td>₱<?= number_format($row["Total_Price"], 2, '.', ',') ?></td>
                                <td>
                                    <div class="status-bar">
                                        <div class="status-bar-fill product-status-bar" style="width: <?php echo calculatePercentage($row["Product_Status"]); ?>%;" title="<?php echo htmlspecialchars($productStatusLabels[$row["Product_Status"]] ?? 'Unknown Status'); ?>">
                                            <?php echo calculatePercentage($row["Product_Status"]); ?>%
                                        </div>
                                    </div>
                                    <small><?php echo htmlspecialchars($productStatusLabels[$row["Product_Status"]] ?? 'Unknown Status'); ?></small>
                                </td>
                                <td style="text-align: center;">
                                    <a class="buttonView" href="../preorder-prod/read-one-preorder-prod-form.php?id=<?= htmlspecialchars($row['Progress_ID']) ?>&order_type=pre_order" target="_parent">View</a>
                                </td>
                                <td style="text-align: center;">
                                    <a class="buttonEdit" href="../preorder-prod/update-preorder-prod-form.php?id=<?= htmlspecialchars($row['Progress_ID']) ?>&order_type=pre_order" target="_parent">Edit</a>
                                </td>
                                <td style="text-align: center;">
                                    <a class="buttonDelete" href="../preorder-prod/delete-preorder-prod-form.php?id=<?= htmlspecialchars($row['Progress_ID']) ?>&order_type=pre_order" target="_parent">Delete</a>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No active pre-orders found.</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </section>
</body>
</html>
