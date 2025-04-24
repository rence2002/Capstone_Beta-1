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
        o.Processed -- Keep for potential future use/filtering
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
            <!-- Add Back to Dashboard button -->
            <div class="button-container mb-3"> <!-- Added margin bottom -->
                <a href="../dashboard/dashboard.php" class="buttonBack btn btn-secondary">Back to Dashboard</a>
            </div>

            <div class="table-responsive"> <!-- Make table responsive -->
                <table class="table table-bordered table-striped"> <!-- Use Bootstrap table classes -->
                    <thead>
                        <tr>
                            <th>ORDER ID</th>
                            <th>USER NAME</th>
                            <th>ORDER TYPE</th>
                            <!-- <th>STATUS</th> --> <!-- REMOVED incorrect status column -->
                            <th>CURRENT PAYMENT</th> <!-- Added column for current status -->
                            <th>SET PAYMENT (on Confirm)</th> <!-- Clarified header for dropdown -->
                            <th colspan="3">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) > 0): ?>
                            <?php
                            foreach ($rows as $row) {
                                $orderID = htmlspecialchars($row["Order_ID"]);
                                $userName = htmlspecialchars($row["User_Name"]);
                                $orderType = htmlspecialchars($row["Order_Type"]);
                                // $status = htmlspecialchars($row["Order_Status"]); // REMOVED
                                $currentPaymentStatus = htmlspecialchars($row["Payment_Status"]); // Get current payment status

                                // Determine the view URL based on the order type
                                // Assuming Customization_ID is needed for custom view, fetch it if necessary
                                // For now, keeping the original logic but it might need adjustment
                                if ($orderType == 'custom') {
                                    // You might need the Customization_ID here from tbl_order_request
                                    // Let's assume read-one-form-customize.php uses Request_ID for now
                                    $viewURL = "read-one-form-customize.php?id=$orderID";
                                } else {
                                    $viewURL = "read-one-request-form.php?id=$orderID";
                                }

                                echo '
                                <tr>
                                    <td>' . $orderID . '</td>
                                    <td>' . $userName . '</td>
                                    <td>' . ucwords(str_replace('_', ' ', $orderType)) . '</td> <!-- Nicer display -->
                                    <td>' . ucwords(str_replace('_', ' ', $currentPaymentStatus)) . '</td> <!-- Display current payment status -->
                                    <td>
                                        <!-- This form submits to accept-request-rec.php -->
                                        <form method="POST" action="accept-request-rec.php" id="form_' . $orderID . '" style="margin: 0;">
                                            <input type="hidden" name="id" value="' . $orderID . '">
                                            <select name="payment_status" id="payment_status_' . $orderID . '" class="form-select form-select-sm" required>
                                                <option value="" disabled selected>Select Status</option>
                                                <option value="downpayment_paid">Downpayment Paid</option>
                                                <option value="fully_paid">Fully Paid</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td><a class="buttonView btn btn-sm btn-info" href="' . $viewURL . '">View</a></td>
                                    <td>
                                        <!-- This button submits the form above it -->
                                        <button type="submit" form="form_' . $orderID . '" class="buttonAccept btn btn-sm btn-success">Confirm</button>
                                    </td>
                                    <td>
                                        <!-- Decline action - Consider using POST and confirmation for safety -->
                                        <a class="buttonDecline btn btn-sm btn-danger" href="decline-request-rec.php?id=' . $orderID . '" onclick="return confirm(\'Are you sure you want to decline this request?\');">Decline</a>
                                    </td>
                                </tr>';
                            }
                            ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No pending order requests found.</td> <!-- Adjusted colspan -->
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div> <!-- End table-responsive -->
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

        // Removed old dropdown toggle JS
        // Removed modal JS as modal elements were removed

    </script>
</body>
</html>
