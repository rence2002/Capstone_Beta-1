<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin's ID is stored in the session
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../index.php"); // Corrected redirect path
    exit();
}

$adminId = $_SESSION['admin_id']; // Logged-in admin's ID

// Fetch logged-in admin data for profile display
$stmt = $pdo->prepare("SELECT First_Name, PicPath FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmt->bindParam(':admin_id', $adminId, PDO::PARAM_STR);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if admin data is fetched
if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Use the session admin ID
$adminIDToView = $adminId;

// Fetch the admin data to be displayed
$stmt = $pdo->prepare("SELECT * FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmt->bindParam(':admin_id', $adminIDToView, PDO::PARAM_STR);
$stmt->execute();
$adminToView = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if admin data exists for the specified ID
if (!$adminToView) {
    echo "No admin found for ID: " . htmlspecialchars($adminIDToView);
    exit();
}

// Sanitize the fetched data
$adminID = htmlspecialchars($adminToView["Admin_ID"]);
$lname = htmlspecialchars($adminToView["Last_Name"]);
$fname = htmlspecialchars($adminToView["First_Name"]);
$mname = htmlspecialchars($adminToView["Middle_Name"]);
$email = htmlspecialchars($adminToView["Email_Address"]);
$mobile = htmlspecialchars($adminToView["Mobile_Number"]);
$status = htmlspecialchars($adminToView["Status"]);
$picPath = htmlspecialchars($adminToView["PicPath"]);

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
    <link href="../static/js/admin_home.js" rel="">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />

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
                    <a href="../admin/read-one-admin-form.php">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>

        </nav>

    <br><br><br>
    <div class="container_boxes">
        <form name="frmAdminRec" method="POST" action="">
            <h4>View Admin Record</h4>
            <table>
                <tr>
                    <td>Admin ID:</td>
                    <td><?php echo $adminID; ?></td>
                </tr>
                <tr>
                    <td>Last Name:</td>
                    <td><?php echo $lname; ?></td>
                </tr>
                <tr>
                    <td>First Name:</td>
                    <td><?php echo $fname; ?></td>
                </tr>
                <tr>
                    <td>Middle Name:</td>
                    <td><?php echo $mname; ?></td>
                </tr>
                <tr>
                    <td>Email Address:</td>
                    <td><?php echo $email; ?></td>
                </tr>
                <tr>
                    <td>Mobile Number:</td>
                    <td><?php echo $mobile; ?></td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td><?php echo $status; ?></td>
                </tr>
                <tr>
                    <td>Profile Picture:</td>
                    <td><img src="../<?php echo $picPath; ?>" alt="Profile Picture" style="width: 100px; height: 100px; border-radius: 50%;"></td>
                </tr>
            </table>
            
            <!-- Buttons outside the table -->
            <div class="button-container">
            <a href="../dashboard/dashboard.php" target="_parent" class="buttonBack">Back to Dashboard</a>
                <a href="update-admin-form.php" target="_parent" class="buttonUpdate">Update Record</a>
            </div>
        </form>
    </div>
    </div>

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


    </script>
</body>
</html>
