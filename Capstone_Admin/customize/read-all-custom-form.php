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

/**
 * Get the color for the progress bar based on the product status.
 */
function getColorForStatus($status) {
    if ($status >= 90) {
        return '#28a745'; // Green for completed or near completion
    } elseif ($status >= 50) {
        return '#ffc107'; // Yellow for mid-production
    } elseif ($status > 0) {
        return '#17a2b8'; // Blue for initial stages
    } else {
        return '#dc3545'; // Red for not started or issues
    }
}

// Use the NEW Product Status labels provided in the prompt
$productStatusLabels = [
    0   => 'Request Approved',         // 0% - Order placed by the customer
    10  => 'Design Approved',          // 10% - Finalized by customer
    20  => 'Material Sourcing',        // 20% - Gathering necessary materials
    30  => 'Cutting & Shaping',        // 30% - Preparing materials
    40  => 'Structural Assembly',      // 40% - Base framework built
    50  => 'Detailing & Refinements',  // 50% - Carvings, upholstery, elements added
    60  => 'Sanding & Pre-Finishing',  // 60% - Smoothening, preparing for final coat
    70  => 'Varnishing/Painting',      // 70% - Applying the final finish
    80  => 'Drying & Curing',          // 80% - Final coating sets in
    90  => 'Final Inspection & Packaging', // 90% - Quality control before handover
    95  => 'Ready for Shipment',
    98  => 'Order Delivered',
    100 => 'Order Recieved',           // Note: Typo 'Recieved' in provided map, kept as is. Should likely be 'Received'
];

// Check if the request is an AJAX search request
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "
        SELECT 
            c.Customization_ID, 
            CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name, 
            c.Furniture_Type, 
            c.Product_Status 
        FROM tbl_customizations c
        JOIN tbl_user_info u ON c.User_ID = u.User_ID
        WHERE u.First_Name LIKE :search 
        OR u.Last_Name LIKE :search 
        OR c.Furniture_Type LIKE :search
        OR c.Product_Status LIKE :search
    ";
    $stmt = $pdo->prepare($query);
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    $stmt->execute();
    $customizations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the table rows for AJAX requests
    echo '<table>
        <tr>
            <th>Customization ID</th>
            <th>User Name</th>
            <th>Furniture Type</th>
            <th>Product Status</th>
            <th colspan="3" style="text-align: center;">ACTIONS</th>
        </tr>';
    foreach ($customizations as $customization) {
        $customizationID = htmlspecialchars($customization["Customization_ID"]);
        $userName = htmlspecialchars($customization["User_Name"]);
        $furnitureType = htmlspecialchars($customization["Furniture_Type"]);
        $productStatus = $customization["Product_Status"];
        $statusLabel = $productStatusLabels[$productStatus] ?? 'Unknown Status';
        echo '
        <tr>
            <td>'.$customizationID.'</td>
            <td>'.$userName.'</td>
            <td>'.$furnitureType.'</td>
            <td>
                <div class="status-bar">
                    <div class="status-bar-fill product-status-bar" style="width: '.$productStatus.'%;">
                        '.$productStatus.'%
                    </div>
                </div>
                <div class="status-text">'.htmlspecialchars($productStatusLabels[$productStatus] ?? 'Unknown Status').'</div>
            </td>
            <td style="text-align: center;"><a class="buttonView" href="read-one-custom-form.php?id='.$customizationID.'" target="_parent">View</a></td>
            <td style="text-align: center;"><a class="buttonEdit" href="update-custom-form.php?id='.$customizationID.'" target="_parent">Edit</a></td>
            <td style="text-align: center;"><a class="buttonDelete" href="delete-custom-form.php?id='.$customizationID.'" target="_parent">Delete</a></td>
        </tr>';
    }
    echo '</table>';
    exit; // Stop further execution for AJAX requests
}

// Fetch customization records from the database with progress data
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "
    SELECT 
        c.Customization_ID, 
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name, 
        c.Furniture_Type, 
        p.Product_Status,
        p.Progress_ID,
        p.Order_Type,
        p.Product_Name,
        c.Product_ID
    FROM tbl_customizations c
    JOIN tbl_user_info u ON c.User_ID = u.User_ID
    LEFT JOIN tbl_progress p ON c.Product_ID = p.Product_ID 
        AND p.Order_Type = 'custom'
    WHERE u.First_Name LIKE :search 
    OR u.Last_Name LIKE :search 
    OR c.Furniture_Type LIKE :search
    OR p.Product_Status LIKE :search
    ORDER BY p.LastUpdate DESC
";
$stmt = $pdo->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
$stmt->execute();
$customizations = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            white-space: nowrap;
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
                <span class="dashboard">Dashboard</span>
            </div>
            <div class="search-box">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" />
                </form>
            </div>
            <div class="profile-details" onclick="toggleDropdown()">
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
            <form name="frmCustomizations" method="POST" action="">
                <h4>CUSTOMIZATION LIST <a href="create-custom-form.php">Create New Customization</a></h4>
                <div class="button-container">
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                </div>
                <div id="customization-list">
                    <table>
                        <tr>
                            <th>CUSTOM ID</th>
                            <th>USER NAE</th>
                            <th>TYPE</th>
                            <th>STATUS</th>
                            <th colspan="3" style="text-align: center;">ACTIONS</th>
                        </tr>
                        <?php
                        foreach ($customizations as $customization) { 
                            $customizationID = htmlspecialchars($customization["Customization_ID"]);
                            $userName = htmlspecialchars($customization["User_Name"]);
                            $furnitureType = htmlspecialchars($customization["Furniture_Type"]);
                            $productStatus = $customization["Product_Status"];
                            $statusLabel = $productStatusLabels[$productStatus] ?? 'Unknown Status';
                            echo '
                            <tr>
                                <td>'.$customizationID.'</td>
                                <td>'.$userName.'</td>
                                <td>'.$furnitureType.'</td>
                                <td>
                                    <div class="status-bar">
                                        <div class="status-bar-fill product-status-bar" style="width: '.$productStatus.'%;">
                                            '.$productStatus.'%
                                        </div>
                                    </div>
                                    <div class="status-text"><small>'.htmlspecialchars($productStatusLabels[$productStatus] ?? 'Unknown Status').'</small></div>
                                </td>
                                <td style="text-align: center;"><a class="buttonView" href="read-one-custom-form.php?id='.$customizationID.'" target="_parent">View</a></td>
                                <td style="text-align: center;"><a class="buttonEdit" href="update-custom-form.php?id='.$customizationID.'" target="_parent">Edit</a></td>
                                <td style="text-align: center;"><a class="buttonDelete" href="delete-custom-form.php?id='.$customizationID.'" target="_parent">Delete</a></td>
                            </tr>';
                        }
                        ?>
                    </table>
                </div>
            </form>
        </div>
    </section>
    <script>
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        sidebarBtn.onclick = function () {
            sidebar.classList.toggle("active");
            if (sidebar.classList.contains("active")) {
                sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
        };
        document.querySelectorAll('.dropdown-toggle').forEach((toggle) => {
            toggle.addEventListener('click', function () {
                const parent = this.parentElement; // Get the parent <li> of the toggle
                const dropdownMenu = parent.querySelector('.dropdown-menu'); // Get the <ul> of the dropdown menu
                parent.classList.toggle('active'); // Toggle the 'active' class on the parent <li>
                // Toggle the chevron icon rotation
                const chevron = this.querySelector('i'); // Find the chevron icon inside the toggle
                if (parent.classList.contains('active')) {
                    chevron.classList.remove('bx-chevron-down');
                    chevron.classList.add('bx-chevron-up'); // Change to up when menu is open
                } else {
                    chevron.classList.remove('bx-chevron-up');
                    chevron.classList.add('bx-chevron-down'); // Change to down when menu is closed
                }
                // Toggle the display of the dropdown menu
                dropdownMenu.style.display = parent.classList.contains('active') ? 'block' : 'none';
            });
        });
        document.querySelector('.search-box input[name="search"]').addEventListener('input', function () {
            const searchValue = this.value;
            // Send an AJAX request to fetch filtered results
            fetch(`read-all-custom-form.php?search=${encodeURIComponent(searchValue)}`)
                .then(response => response.text())
                .then(data => {
                    // Update the customization list with the filtered results
                    const customizationList = document.getElementById('customization-list');
                    customizationList.innerHTML = data;
                })
                .catch(error => console.error('Error fetching search results:', error));
        });
    </script>
</body>
</html>

// Add this function before the HTML section
function calculatePercentage($status) {
    // Ensure status is treated as a number
    $status = (int) $status;
    // Basic percentage calculation, assuming status is already 0-100
    return max(0, min(100, $status));
}