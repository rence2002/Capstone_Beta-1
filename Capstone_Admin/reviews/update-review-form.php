<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Redirect to login page if admin is not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin data for profile display
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

// DISABLE ERROR DETECTION
error_reporting(0);

// Fetch review data to update
if (!isset($_GET['id'])) {
    echo "Review ID is missing!";
    exit();
}

$reviewID = $_GET['id'];

// Prepare query to fetch review details along with user's information
$query = "
    SELECT r.Review_ID, r.Rating, r.Review_Text, r.Review_Date, r.PicPath, u.First_Name AS user_first_name, u.Last_Name AS user_last_name 
    FROM tbl_reviews r
    JOIN tbl_user_info u ON r.User_ID = u.User_ID
    WHERE r.Review_ID = :review_id
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':review_id', $reviewID);
$stmt->execute();
$review = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the review exists
if (!$review) {
    echo "Review not found!";
    exit();
}

// Extract review data
$userName = $review['user_first_name'] . " " . $review['user_last_name'];
$rating = $review['Rating'];
$reviewText = htmlspecialchars($review['Review_Text']);
$reviewDate = $review['Review_Date'];
$reviewPicPaths = json_decode($review['PicPath'], true) ?? [];

// Process the form submission to update the review
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newRating = $_POST['txtRating'];
    $newReviewText = htmlspecialchars(trim($_POST['txtReviewText']));
    $newPicPaths = $reviewPicPaths;

    // Handle file uploads
    for ($i = 1; $i <= 3; $i++) {
        $fileKey = "reviewPic$i";
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/review_pics/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileTmpPath = $_FILES[$fileKey]['tmp_name'];
            $fileName = basename($_FILES[$fileKey]['name']);
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueIdentifier = time() . '_' . bin2hex(random_bytes(5)); // Unique identifier
            $newFileName = 'review_' . $reviewID . '_' . $uniqueIdentifier . '.' . $fileExtension;
            $filePath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $filePath)) {
                $newPicPaths[$i - 1] = $filePath;
            }
        }
    }

    // Convert the PicPath array to a JSON string
    $newPicPathsJson = json_encode($newPicPaths);

    // Update review in the database
    $updateQuery = "
        UPDATE tbl_reviews 
        SET Rating = :rating, Review_Text = :review_text, PicPath = :pic_path 
        WHERE Review_ID = :review_id
    ";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->bindParam(':rating', $newRating);
    $updateStmt->bindParam(':review_text', $newReviewText);
    $updateStmt->bindParam(':pic_path', $newPicPathsJson);
    $updateStmt->bindParam(':review_id', $reviewID);

    if ($updateStmt->execute()) {
        // Successfully updated
        header("Location: read-all-reviews-form.php?status=success");
        exit();
    } else {
        echo "Failed to update review.";
    }
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
        <form name="frmUpdateReview" method="POST" action="update-review-rec.php" enctype="multipart/form-data">
            <h4>UPDATE REVIEW</h4>
            <table class="table">
                <tr>
                    <td>Review ID:</td>
                    <td><input type="text" name="txtReviewID" value="<?php echo htmlspecialchars($review['Review_ID']); ?>" readonly></td>
                </tr>
                <tr>
                    <td>User Name:</td>
                    <td><input type="text" name="txtUserName" value="<?php echo htmlspecialchars($userName); ?>" readonly></td>
                </tr>
                <tr>
                    <td>Rating:</td>
                    <td>
                        <select name="txtRating" class="form-select">
                            <option value="1" <?php if ($rating == 1) echo 'selected'; ?>>1</option>
                            <option value="2" <?php if ($rating == 2) echo 'selected'; ?>>2</option>
                            <option value="3" <?php if ($rating == 3) echo 'selected'; ?>>3</option>
                            <option value="4" <?php if ($rating == 4) echo 'selected'; ?>>4</option>
                            <option value="5" <?php if ($rating == 5) echo 'selected'; ?>>5</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Review Text:</td>
                    <td><textarea name="txtReviewText" rows="5" cols="40"><?php echo $reviewText; ?></textarea></td>
                </tr>
                <tr>
                    <td>Review Date:</td>
                    <td><input type="text" name="txtReviewDate" value="<?php echo htmlspecialchars($reviewDate); ?>" readonly></td>
                </tr>
                <tr>
                    <td>Review Picture 1:</td>
                    <td>
                        <?php if (isset($reviewPicPaths[0])): ?>
                            <img src="<?php echo $reviewPicPaths[0]; ?>" alt="Review Picture 1" style="max-width: 100%; height: auto;">
                        <?php else: ?>
                            No picture available.
                        <?php endif; ?>
                        <input type="file" name="reviewPic1" class="form-control mt-2">
                    </td>
                </tr>
                <tr>
                    <td>Review Picture 2:</td>
                    <td>
                        <?php if (isset($reviewPicPaths[1])): ?>
                            <img src="<?php echo $reviewPicPaths[1]; ?>" alt="Review Picture 2" style="max-width: 100%; height: auto;">
                        <?php else: ?>
                            No picture available.
                        <?php endif; ?>
                        <input type="file" name="reviewPic2" class="form-control mt-2">
                    </td>
                </tr>
                <tr>
                    <td>Review Picture 3:</td>
                    <td>
                        <?php if (isset($reviewPicPaths[2])): ?>
                            <img src="<?php echo $reviewPicPaths[2]; ?>" alt="Review Picture 3" style="max-width: 100%; height: auto;">
                        <?php else: ?>
                            No picture available.
                        <?php endif; ?>
                        <input type="file" name="reviewPic3" class="form-control mt-2">
                    </td>
                </tr>
            </table>

            <!-- Separated buttons -->
            <div class="button-container">
                <input type="submit" value="Update Review" class="buttonUpdate">
                <a href="read-all-reviews-form.php" target="_parent" class="buttonBack">Back to Review List</a>
            </div>
        </form>
    </div>

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
