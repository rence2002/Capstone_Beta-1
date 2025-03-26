<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin's ID is stored in the session
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../index.php");
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
$adminIDToUpdate = $adminId; // Use $adminId from the session

// Fetch the admin data to be updated
$stmt = $pdo->prepare("SELECT * FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmt->bindParam(':admin_id', $adminIDToUpdate, PDO::PARAM_STR);
$stmt->execute();
$adminToUpdate = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if admin data exists for the specified ID
if (!$adminToUpdate) {
    echo "No admin found.";
    exit();
}
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
                    <a href="read-one-admin-form.php">Settings</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

        </nav>

    <br><br><br>
    <div class="container_boxes">
        <form name="frmAdminRec" method="POST" action="update-admin-rec.php" enctype="multipart/form-data">
            <h4>UPDATE ADMIN RECORD</h4>
            <table>
                <tr>
                    <td>Admin ID:</td>
                    <td><input type="text" name="txtID" value="<?php echo htmlspecialchars($adminToUpdate['Admin_ID']); ?>" readonly></td>
                </tr>
                <tr>
                    <td>Last Name:</td>
                    <td><input type="text" name="txtLName" value="<?php echo htmlspecialchars($adminToUpdate['Last_Name']); ?>"></td>
                </tr>
                <tr>
                    <td>First Name:</td>
                    <td><input type="text" name="txtFName" value="<?php echo htmlspecialchars($adminToUpdate['First_Name']); ?>"></td>
                </tr>
                <tr>
                    <td>Middle Name:</td>
                    <td><input type="text" name="txtMName" value="<?php echo htmlspecialchars($adminToUpdate['Middle_Name']); ?>"></td>
                </tr>
                <tr>
                    <td>Email Address:</td>
                    <td><input type="text" name="txtEmail" value="<?php echo htmlspecialchars($adminToUpdate['Email_Address']); ?>"></td>
                </tr>
                <tr>
                    <td>Mobile Number:</td>
                    <td><input type="text" name="txtMobile" value="<?php echo htmlspecialchars($adminToUpdate['Mobile_Number']); ?>"></td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td>
                        <select name="txtStatus">
                            <option value="Active" <?php if ($adminToUpdate['Status'] === 'Active') echo 'selected'; ?>>Active</option>
                            <option value="Inactive" <?php if ($adminToUpdate['Status'] === 'Inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Password:</td>
                    <td><input type="password" name="txtPass" placeholder="Leave blank to keep current password"></td>
                </tr>
                <tr>
                    <td>Confirm Password:</td>
                    <td><input type="password" name="txtConfirm" placeholder="Leave blank to keep current password"></td>
                </tr>
                <tr>
                    <td>Profile Picture:</td>
                    <td>
                        <input type="file" name="filePic">
                        <img src="../<?php echo htmlspecialchars($adminToUpdate['PicPath']); ?>" alt="Profile Picture" style="width:100px;height:100px;">
                    </td>
                </tr>
            </table>
            
            <!-- Separated buttons -->
            <div class="button-container">
                <input type="submit" value="Submit" class="buttonUpdate">
                <a href="read-one-admin-form.php" target="_parent" class="buttonBack">Back to Settings</a>
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
