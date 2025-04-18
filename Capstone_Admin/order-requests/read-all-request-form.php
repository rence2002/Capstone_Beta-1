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
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Fetch order requests from the database
$query = "
    SELECT 
        o.Request_ID AS Order_ID, 
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name, 
        o.Order_Type, 
        o.Order_Status 
    FROM tbl_order_request o
    JOIN tbl_user_info u ON o.User_ID = u.User_ID
    WHERE o.Order_Status = 0
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <!-- <link href="../static/css-files/dashboard.css" rel="stylesheet"> -->
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="../static/js/admin_home.js" rel="">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <link href="../static/css-files/modal.css" rel="stylesheet">
    <script src="../static/js/modal.js"></script>
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
        <i class="bx bx-message-dots"></i> <!-- Changed to a more appropriate message icon -->
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
            <div class="profile-details" onclick="toggleDropdown()">
                <img src="../<?php echo $profilePicPath; ?>" alt="Profile Picture" />
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
            <h4>ORDER REQUESTS LIST</h4>
            <!-- Add Back to Dashboard button -->
            <div class="button-container">
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                </div>
                <table>
            <table width="100%" border="1" cellspacing="5">
                <tr>
                    <th>ORDER ID</th>
                    <th>USER NAME</th>
                    <th>ORDER TYPE</th>
                    <th>STATUS</th>
                    <th>PAYMENT STATUS</th> <!-- Dropdown column -->
                    <th colspan="3" style="text-align: center;">ACTIONS</th>
                </tr>
                <?php
                foreach ($rows as $row) {
                    $orderID = htmlspecialchars($row["Order_ID"]);
                    $userName = htmlspecialchars($row["User_Name"]);
                    $orderType = htmlspecialchars($row["Order_Type"]);
                    $status = htmlspecialchars($row["Order_Status"]);

                    // Determine the view URL based on the order type
                    if ($orderType == 'custom') {
                        $viewURL = "read-one-form-customize.php?id=$orderID";
                    } else {
                        $viewURL = "read-one-request-form.php?id=$orderID";
                    }

                    echo '
                    <tr>
                        <td>' . $orderID . '</td>
                        <td>' . $userName . '</td>
                        <td>' . $orderType . '</td>
                        <td>' . $status . '</td>
                        <td style="text-align: center;">
                            <form method="GET" action="accept-request-rec.php" id="form_' . $orderID . '">
                                <input type="hidden" name="id" value="' . $orderID . '">
                                <select name="payment_status" id="payment_status_' . $orderID . '" required>
                                    <option value="" disabled selected>Select Payment Status</option> <!-- Default option -->
                                    <option value="downpayment_paid">Downpayment Paid</option>
                                    <option value="fully_paid">Fully Paid</option>
                                </select>
                            </form>
                        </td>
                        <td style="text-align: center;"><a class="buttonView" href="' . $viewURL . '" target="_parent">View</a></td>
                        <td style="text-align: center;">
                            <button type="submit" form="form_' . $orderID . '" class="buttonAccept">Confirm</button>
                        </td>
                        <td style="text-align: center;"><a class="buttonDecline" href="decline-request-rec.php?id=' . $orderID . '" target="_parent">Decline</a></td>
                    </tr>';
                }
                ?>
            </table>
        </div>
    </section>

    <script>
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        sidebarBtn.onclick = function () {
            sidebar.classList.toggle("active");
            if (sidebar.classList.contains("active")) {
                sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else {
                sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
            }
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

    // Modal functionality
    const modal = document.getElementById("paymentStatusModal");
    const closeModal = document.querySelector(".modal .close");
    const openModalButtons = document.querySelectorAll(".openModal");
    const orderIdInput = document.getElementById("orderIdInput");

    openModalButtons.forEach(button => {
        button.addEventListener("click", function () {
            const orderId = this.getAttribute("data-order-id");
            orderIdInput.value = orderId;
            modal.style.display = "block";
        });
    });

    closeModal.addEventListener("click", function () {
        modal.style.display = "none";
    });

    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
    </script>
</body>
</html>