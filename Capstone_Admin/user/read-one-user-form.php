<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
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

// Fetch user data from the database
if (isset($_GET['id'])) {
    $query = "SELECT * FROM tbl_user_info WHERE User_ID = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $_GET['id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if user data is fetched
if (!$user) {
    echo "User not found.";
    exit();
}

$userID = htmlspecialchars($user["User_ID"]);
$lname = htmlspecialchars($user["Last_Name"]);
$fname = htmlspecialchars($user["First_Name"]);
$mname = htmlspecialchars($user["Middle_Name"]);
$email = htmlspecialchars($user["Email_Address"]);
$mobile = htmlspecialchars($user["Mobile_Number"]);
$address = htmlspecialchars($user["Home_Address"]);
$status = htmlspecialchars($user["Status"]);
$userPicPath = htmlspecialchars($user["PicPath"]); // Fetch the user's profile picture path
$validIDPath = htmlspecialchars($user["Valid_ID_Path"]); // Fetch the user's valid ID path
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
</head>

<body>
    <div class="sidebar">
        <div class="logo-details">
            <span class="logo_name">
                <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE" class="logo_name">
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
            <h4>VIEW USER RECORD</h4>
            <form name="frmUserRec" method="POST" action="">
                <table>
                    <tr>
                        <td>User ID:</td>
                        <td><?php echo $userID; ?></td>
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
                        <td>Home Address:</td>
                        <td><?php echo $address; ?></td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><?php echo $status; ?></td>
                    </tr>
                    <!-- Display user's profile picture -->
                    <tr>
                        <td>Profile Picture:</td>
                        <td><img src="../<?php echo $userPicPath; ?>" alt="User Profile Picture" style="width: 100px; height: 100px; border-radius: 50%;"></td>
                    </tr>
                    <!-- Display user's valid ID -->
                    <tr>
                        <td>Valid ID:</td>
                        <td><img src="../<?php echo $validIDPath; ?>" alt="Valid ID" style="width: 100px; height: 100px;"></td>
                    </tr>
                    <!-- Display ID Validation Status -->
                    <tr>
                        <td>ID Validation Status:</td>
                        <td><?php echo htmlspecialchars($user['ID_Verification_Status']); ?></td>
                    </tr>
                </table>
                <div class="button-container">
                    <a href="read-all-user-form.php" target="_parent" class="buttonBack">Back to List</a>
                    <a href="update-user-form.php?id=<?php echo $userID; ?>" target="_parent" class="buttonEdit">Edit</a>
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
            } else {
                sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
            }
        };
    </script>
</body>
</html>
