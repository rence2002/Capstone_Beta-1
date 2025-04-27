<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Assuming the admin's ID is stored in session after login
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


// Fetch UNPROCESSED order requests from the database
// Updated Query: Removed Order_Status, added Payment_Status, filter by Processed = 0
$query = "
    SELECT
        o.Request_ID AS Order_ID,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        o.Order_Type,
        o.Payment_Status, -- Get the current payment status
        o.Payment_Reference_Number, -- Get the payment reference number
        o.Processed, -- Keep for potential future use/filtering
        o.Submission_Attempts -- Added for new functionality
    FROM tbl_order_request o
    JOIN tbl_user_info u ON o.User_ID = u.User_ID
    WHERE o.Processed = 0 -- Filter for unprocessed requests
    ORDER BY o.Request_Date ASC -- Show oldest requests first
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Order Requests</title> <!-- Specific Title -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <!-- <script src="../static/js/dashboard.js"></script> --> <!-- Removed, likely redundant -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <!-- <link href="../static/js/admin_home.js" rel=""> --> <!-- Incorrect link type -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <!-- <link href="../static/css-files/modal.css" rel="stylesheet"> --> <!-- Removed, modal not used here -->
    <!-- <script src="../static/js/modal.js"></script> --> <!-- Removed, modal not used here -->
    <style>
        /* Minor adjustments for table readability */
        .table th, .table td { vertical-align: middle; text-align: center; }
        .table select { min-width: 150px; } /* Give dropdown some space */
        
        /* Reset button styling */
        .buttonReset {
            display: inline-block;
            padding: 6px 12px;
            background-color: #ffc107;
            color: #000;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .buttonReset:hover {
            background-color: #e0a800;
            color: #000;
            text-decoration: none;
        }

        /* Success/Error message styling */
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
              <a href="../dashboard/dashboard.php" class="">
                  <i class="bx bx-grid-alt"></i>
                  <span class="links_name">Dashboard</span>
              </a>
          </li>
          <li>
              <a href="../purchase-history/read-all-history-form.php" class="">
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
                <span class="dashboard">Order Requests</span> <!-- Updated title -->
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
    <h4>PENDING ORDER REQUESTS</h4>

    <!-- Success/Error Messages -->
    <?php if (isset($_GET['success']) && $_GET['success'] === 'attempts_reset'): ?>
        <div class="alert alert-success">
            Submission attempts have been reset successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
        </div>
    <?php endif; ?>

    <!-- Back to Dashboard -->
    <div class="button-container mb-3">
        <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
    </div>

    <div id="order-list">
        <table width="100%" border="1" cellspacing="5">
            <tr>
                <th>ORDER ID</th>
                <th>USER NAME</th>
                <th>ORDER TYPE</th>
                <th>CURRENT PAYMENT</th>
                <th>PAYMENT REFERENCE</th>
                <th>SET PAYMENT (on Confirm)</th>
                <th colspan="3" style="text-align: center;">ACTIONS</th>
            </tr>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): 
                    $orderID = htmlspecialchars($row["Order_ID"]);
                    $userName = htmlspecialchars($row["User_Name"]);
                    $orderType = htmlspecialchars($row["Order_Type"]);
                    $currentPaymentStatus = htmlspecialchars($row["Payment_Status"]);
                    $paymentReference = htmlspecialchars($row["Payment_Reference_Number"] ?? 'Not provided');
                    $viewURL = $orderType == 'custom' 
                        ? "read-one-form-customize.php?id=$orderID" 
                        : "read-one-request-form.php?id=$orderID";
                ?>
                <tr>
                    <td><?php echo $orderID; ?></td>
                    <td><?php echo $userName; ?></td>
                    <td><?php echo ucwords(str_replace('_', ' ', $orderType)); ?></td>
                    <td><?php echo ucwords(str_replace('_', ' ', $currentPaymentStatus)); ?></td>
                    <td>
                        <?php if (isset($row['Submission_Attempts']) && $row['Submission_Attempts'] >= 3): ?>
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                                <span style="color: #dc3545; font-style: italic;font-size: 13px;">Max Attempts Reached</span>
                                <a class="buttonReset" href="reset-attempts.php?id=<?php echo $orderID; ?>">Reset Attempts</a>
                            </div>
                        <?php else: ?>
                            <?php echo $paymentReference; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" action="accept-request-rec.php" id="form_<?php echo $orderID; ?>" style="margin: 0;">
                            <input type="hidden" name="id" value="<?php echo $orderID; ?>">
                            <select name="payment_status" id="payment_status_<?php echo $orderID; ?>" required>
                                <option value="" disabled selected>Select Status</option>
                                <option value="downpayment_paid">Downpayment Paid</option>
                                <option value="fully_paid">Fully Paid</option>
                            </select>
                        </form>
                    </td>
                    <td style="text-align: center;">
                        <a class="buttonView" href="<?php echo $viewURL; ?>">View</a>
                    </td>
                    <td style="text-align: center;">
                        <button type="submit" form="form_<?php echo $orderID; ?>" class="buttonAccept">Confirm</button>
                    </td>
                    <td style="text-align: center;">
                        <a class="buttonDecline" href="decline-request-rec.php?id=<?php echo $orderID; ?>">Decline</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No pending order requests found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>


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

        // Auto-remove success notification after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                setTimeout(function() {
                    successAlert.style.opacity = '0';
                    successAlert.style.transition = 'opacity 0.5s ease-out';
                    setTimeout(function() {
                        successAlert.remove();
                    }, 500);
                }, 3000);
            }
        });
    </script>
</body>
</html>
