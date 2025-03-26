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

// Check if a Review_ID is passed via GET parameter
if (isset($_GET['id'])) {
    $query = "SELECT * FROM tbl_reviews WHERE Review_ID = ?";

    // Prepare the query and store it in a statement variable
    $stmt = $pdo->prepare($query);

    // Bind the 'id' parameter to the SQL query
    $stmt->bindValue(1, $_GET['id'], PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch the review record
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the record exists
    if ($row) {
        // Store the data to variables
        $reviewID = $row["Review_ID"];
        $userID = $row["User_ID"];
        $productID = $row["Product_ID"];
        $rating = $row["Rating"];
        $reviewText = $row["Review_Text"];
        $reviewDate = $row["Review_Date"];
        $picPaths = json_decode($row["PicPath"], true) ?? [];
    } else {
        // Redirect if no record is found
        header("Location: read-all-reviews-form.php");
        exit();
    }
} else {
    // Redirect back if 'id' is not set
    header("Location: read-all-reviews-form.php");
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
    <!-- <link href="../static/css-files/dashboard.css" rel="stylesheet"> -->
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
            <div class="search-box">
                <input type="text" placeholder="Search..." />
                <i class="bx bx-search"></i>
            </div>


            <div class="profile-details" onclick="toggleDropdown()">
    <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" />
    <span class="admin_name"><?php echo $adminName; ?></span>
    <i class="bx bx-chevron-down dropdown-button"></i>

    <div class="dropdown" id="profileDropdown">
        <a href="../admin/read-one-admin-form.php">Settings</a>
        <a href="../admin/logout.php">Logout</a>
    </div>
</div>

<!-- Link to External JS -->
<script src="dashboard.js"></script>


 </nav>

        <br><br><br><br>

        <div class="container_boxes">
            <form name="frmReviewRec" method="POST" action="delete-review-rec.php">
                <h2>Delete Review Record</h2>
                <table>
                    <?php
                    echo '
                    <tr>
                        <td>Review ID:</td>
                        <td>' . htmlspecialchars($reviewID) . '</td>
                    </tr>
                    <tr>
                        <td>User ID:</td>
                        <td>' . htmlspecialchars($userID) . '</td>
                    </tr>
                    <tr>
                        <td>Product ID:</td>
                        <td>' . htmlspecialchars($productID) . '</td>
                    </tr>
                    <tr>
                        <td>Rating:</td>
                        <td>' . htmlspecialchars($rating) . '</td>
                    </tr>
                    <tr>
                        <td>Review Text:</td>
                        <td>' . nl2br(htmlspecialchars($reviewText)) . '</td>
                    </tr>
                    <tr>
                        <td>Review Date:</td>
                        <td>' . htmlspecialchars($reviewDate) . '</td>
                    </tr>';
                    if (!empty($picPaths)) {
                        foreach ($picPaths as $index => $picPath) {
                            echo '
                            <tr>
                                <td>Review Picture ' . ($index + 1) . ':</td>
                                <td><img src="' . htmlspecialchars($picPath) . '" alt="Review Picture ' . ($index + 1) . '" style="max-width: 100%; height: auto;"></td>
                            </tr>';
                        }
                    }
                    ?>
                </table>

                <div class="button-container">
                    <a href="read-all-reviews-form.php" target="_parent" class="buttonBack">Back to List</a>
                    <a href="delete-review-rec.php?id=<?php echo htmlspecialchars($reviewID); ?>" target="_parent" class="buttonDelete">Delete Review</a>
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
