<?php
session_start();

// Include the database connection
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin data (for header/sidebar)
$adminId = $_SESSION['admin_id'];
$stmtAdmin = $pdo->prepare("SELECT First_Name, PicPath FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmtAdmin->bindParam(':admin_id', $adminId);
$stmtAdmin->execute();
$admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    // Handle case where admin data isn't found, though unlikely if session is set
    $_SESSION['error_message'] = "Admin details not found.";
    header("Location: read-all-history-form.php");
    exit();
}
$adminName = htmlspecialchars($admin['First_Name']);
$baseUrl = 'http://localhost/Capstone_Beta/';
$profilePicPath = $admin['PicPath'];
// Remove any leading slashes or duplicate directories
$profilePicPath = preg_replace('/^[\/]*(Capstone_Beta\/)?(Capstone_Admin\/)?(admin\/)?/', '', $profilePicPath);
$profilePicPath = htmlspecialchars($profilePicPath);


// Get the ID and Order Type from the URL
$recordId = $_GET['id'] ?? null;
$orderType = $_GET['order_type'] ?? null;

// Validate ID and Order Type
if (!$recordId || !filter_var($recordId, FILTER_VALIDATE_INT) || !$orderType) {
    $_SESSION['error_message'] = "Error: Invalid, missing ID, or missing Order Type for deletion.";
    header("Location: read-all-history-form.php");
    exit();
}

// Fetch record details for confirmation display
try {
    $query = "SELECT
                ph.*,
                CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name
              FROM tbl_purchase_history ph
              JOIN tbl_user_info u ON ph.User_ID = u.User_ID
              WHERE ph.Purchase_ID = :record_id
              AND ph.Order_Type = :order_type
              AND ph.Product_Status = 100";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':record_id', $recordId, PDO::PARAM_INT);
    $stmt->bindParam(':order_type', $orderType, PDO::PARAM_STR);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        // If no record found (either doesn't exist or isn't status 100)
        $_SESSION['error_message'] = "Error: Completed purchase record not found for the specified ID and Type.";
        header("Location: read-all-history-form.php");
        exit();
    }
} catch (PDOException $e) {
     error_log("Database error fetching record for deletion: " . $e->getMessage()); // Log error
    $_SESSION['error_message'] = "Database error fetching record details. Please try again.";
    header("Location: read-all-history-form.php");
    exit();
}

// Handle form submission for deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $deleteQuery = "DELETE FROM tbl_purchase_history 
                       WHERE Purchase_ID = :record_id 
                       AND Order_Type = :order_type 
                       AND Product_Status = 100";
        
        $stmt = $pdo->prepare($deleteQuery);
        $stmt->bindParam(':record_id', $recordId, PDO::PARAM_INT);
        $stmt->bindParam(':order_type', $orderType, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Purchase record successfully deleted.";
        } else {
            $_SESSION['error_message'] = "No record was deleted. The record may have already been deleted or does not exist.";
        }
        
        header("Location: read-all-history-form.php");
        exit();
    } catch (PDOException $e) {
        error_log("Database error during deletion: " . $e->getMessage());
        $_SESSION['error_message'] = "Error deleting the record. Please try again.";
        header("Location: read-all-history-form.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Confirm Deletion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <!-- Use Bootstrap Bundle JS for Popper dependency (needed for some components) -->
    <script src="../static/js/bootstrap.bundle.min.js"></script>
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        /* Styles from original file - NO CHANGES HERE */
        .confirmation-box {
            background-color: #f8d7da; /* Light red */
            border: 1px solid #f5c6cb; /* Red border */
            color: #721c24; /* Dark red text */
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
        }
        .confirmation-box h4 {
            margin-top: 0;
            color: #721c24;
        }
        .confirmation-box p {
            margin-bottom: 15px;
        }
        .button-group {
            margin-top: 20px;
        }
        .buttonConfirmDelete {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            margin-right: 10px;
        }
        .record-details th {
            text-align: right;
            padding-right: 10px;
            width: 150px; /* Adjust as needed */
            font-weight: bold; /* Make labels bold */
            vertical-align: top;
        }
         .record-details td {
            text-align: left;
            padding-bottom: 5px; /* Add spacing between rows */
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
            <!-- Assuming logo_image class handles sizing -->
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE"  class="logo_image">
        </span>
      </div>
        <ul class="nav-links">
            <li><a href="../dashboard/dashboard.php"><i class="bx bx-grid-alt"></i><span class="links_name">Dashboard</span></a></li>
            <li><a href="read-all-history-form.php" class="active"><i class="bx bx-history"></i><span class="links_name">All Purchase History</span></a></li>
             <li><a href="../reviews/read-all-reviews-form.php"><i class="bx bx-message-dots"></i><span class="links_name">All Reviews</span></a></li>
            <!-- Add other nav links as needed -->
        </ul>
    </div>

    <section class="home-section">
    <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Confirm Deletion</span>
            </div>
             <!-- No search box needed here -->
             <!-- Profile Details Container - Updated HTML structure for JS -->
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
            <h4>Confirm Deletion</h4>

            <div class="confirmation-box">
                <h4><i class='bx bx-error-circle'></i> Warning!</h4>
                <p>You are about to permanently delete the following purchase history record. Associated images (if any, especially for custom orders) might also be deleted by the processing script.</p>
                <p><strong>Are you sure you want to proceed? This action cannot be undone.</strong></p>
            </div>

            <h5>Record Details:</h5>
            <table class="record-details">
                 <tr><th>Record ID:</th><td><?php echo htmlspecialchars($record['Purchase_ID']); ?></td></tr>
                 <tr><th>Order Type:</th><td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $orderType))); ?></td></tr>
                 <tr><th>User:</th><td><?php echo htmlspecialchars($record['User_Name']); ?> (ID: <?php echo htmlspecialchars($record['User_ID']); ?>)</td></tr>
                 <!-- Display Date -->
                 <?php
                    $dateLabel = "Date";
                    $dateValue = "N/A";
                    if (isset($record['Purchase_Date'])) { // From tbl_purchase_history (though we avoid querying it)
                        $dateLabel = "Purchase Date";
                        $dateValue = $record['Purchase_Date'];
                    } elseif (isset($record['Order_Date'])) { // From tbl_preorder or tbl_ready_made_orders
                        $dateLabel = "Order Date";
                        $dateValue = $record['Order_Date'];
                    } elseif (isset($record['Last_Update'])) { // Fallback for tbl_customizations
                        $dateLabel = "Last Update";
                        $dateValue = $record['Last_Update'];
                    }
                 ?>
                 <tr><th><?php echo $dateLabel; ?>:</th><td><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($dateValue))); ?></td></tr>
                 <!-- Add other relevant details if needed, e.g., Price -->
                 <?php if (isset($record['Total_Price'])): ?>
                     <tr><th>Total Price:</th><td>₱ <?php echo number_format((float)$record['Total_Price'], 2); ?></td></tr>
                 <?php endif; ?>

            </table>


            <form action="delete-history-rec.php" method="POST" class="button-group">
                <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($record['Purchase_ID']); ?>">
                <input type="hidden" name="order_type" value="<?php echo htmlspecialchars($orderType); ?>">
                <a href="read-all-history-form.php" class="buttonBack">Cancel</a>
                <button type="submit" class="buttonDelete">Confirm Delete</button>
              
            </form>
        </div>
    </section>

    <!-- Use consistent JS for sidebar and dropdown -->
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
        const profileDetailsContainer = document.querySelector(".profile-details");
        const profileDropdown = document.getElementById('profileDropdown');
        const dropdownIcon = document.querySelector(".dropdown-button"); // Assuming you have an icon element

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
    </script>
</body>
</html>
