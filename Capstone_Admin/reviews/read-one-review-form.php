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

// Fetch admin data
$adminId = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT First_Name, PicPath FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmt->bindParam(':admin_id', $adminId);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Check if the review ID is present in the URL
$reviewID = $_GET['id'] ?? null;

if (!$reviewID) {
    echo "Error: Review ID is missing.";
    exit();
}

// Prepare the query to fetch review details along with user and product information
$query = "
    SELECT 
        r.Review_ID, 
        r.Rating, 
        r.Review_Text, 
        r.Review_Date, 
        r.PicPath, 
        u.First_Name, 
        u.Last_Name,
        p.Product_Name
    FROM tbl_reviews r
    JOIN tbl_user_info u ON r.User_ID = u.User_ID
    LEFT JOIN tbl_prod_info p ON r.Product_ID = p.Product_ID
    WHERE r.Review_ID = :review_id
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':review_id', $reviewID, PDO::PARAM_INT);
$stmt->execute();
$review = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the review exists
if (!$review) {
    echo "Review not found!";
    exit();
}

// Extract review data
$userName = htmlspecialchars($review['First_Name'] . " " . $review['Last_Name']);
$productName = htmlspecialchars($review['Product_Name'] ?? "N/A"); // Handle case where product is not set
$rating = htmlspecialchars($review['Rating']);
$reviewText = htmlspecialchars($review['Review_Text']);
$reviewDate = htmlspecialchars($review['Review_Date']);
$reviewPicPaths = json_decode($review['PicPath'], true) ?? [];
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
                <img src="../<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button"></i>

                <div class="dropdown" id="profileDropdown">
                    <!-- Modified link here -->
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>

<!-- Link to External JS -->
<script src="dashboard.js"></script>


 </nav>

        <br><br><br>

        <div class="container_boxes">
    <h4>Review Details</h4>
    <table class="table">
        <tr>
            <th>User Name</th>
            <td><?php echo $userName; ?></td>
        </tr>
        <tr>
            <th>Product Name</th>
            <td><?php echo $productName; ?></td>
        </tr>
        <tr>
            <th>Review Pictures</th>
            <td>
                <?php if (!empty($reviewPicPaths)): ?>
                    <?php foreach ($reviewPicPaths as $index => $picPath): ?>
                        <img src="<?php echo htmlspecialchars($picPath); ?>" alt="Review Picture <?php echo $index + 1; ?>" style="max-width: 100%; height: auto;">
                    <?php endforeach; ?>
                <?php else: ?>
                    No pictures available.
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Rating</th>
            <td><?php echo $rating; ?> / 5</td>
        </tr>
        <tr>
            <th>Review Text</th>
            <td><?php echo $reviewText; ?></td>
        </tr>
        <tr>
            <th>Review Date</th>
            <td><?php echo $reviewDate; ?></td>
        </tr>
    </table>

    <!-- Buttons -->
    <div class="button-container">
        <a href="../reviews/read-all-reviews-form.php" class="buttonBack">Back to Review List</a>
        <!-- <a href="../reviews/update-review-form.php?id=<?php echo $reviewID; ?>" class="buttonUpdate">Update Review</a>
        <a href="../reviews/delete-review-form.php?id=<?php echo $reviewID; ?>" class="buttonDelete">Delete Review</a> -->
    </div>
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
    </script>
</body>
</html>
