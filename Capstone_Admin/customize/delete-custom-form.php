<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin data for profile display
$adminId = $_SESSION['admin_id'];
$stmtAdmin = $pdo->prepare("SELECT First_Name, PicPath FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmtAdmin->bindParam(':admin_id', $adminId);
$stmtAdmin->execute();
$admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

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

// Check if Customization_ID is provided
if (!isset($_GET['id'])) {
    echo "No customization ID provided.";
    // Optionally redirect back to the list
    // header("Location: read-all-custom-form.php?error=" . urlencode("No ID provided"));
    exit();
}

$customizationID = $_GET['id'];
$customizationData = null; // Initialize variable

// Product Status Labels (copied from update form for consistency)
$productStatusLabels = [
    0   => 'Request Approved', 10  => 'Design Approved', 20  => 'Material Sourcing',
    30  => 'Cutting & Shaping', 40  => 'Structural Assembly', 50  => 'Detailing & Refinements',
    60  => 'Sanding & Pre-Finishing', 70  => 'Varnishing/Painting', 80  => 'Drying & Curing',
    90  => 'Final Inspection & Packaging', 95  => 'Ready for Shipment', 98  => 'Order Delivered',
    100 => 'Order Recieved',
];


try {
    // Fetch customization details along with user name
    $query = "
        SELECT
            c.Customization_ID, c.User_ID, c.Furniture_Type, c.Product_Status,
            c.Request_Date, c.Product_ID,
            u.First_Name, u.Last_Name
        FROM tbl_customizations c
        JOIN tbl_user_info u ON c.User_ID = u.User_ID
        WHERE c.Customization_ID = :customizationID
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':customizationID', $customizationID, PDO::PARAM_INT);
    $stmt->execute();
    $customizationData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customizationData) {
        echo "Customization record not found for ID: " . htmlspecialchars($customizationID);
        // Optionally redirect back
        // header("Location: read-all-custom-form.php?error=" . urlencode("Record not found"));
        exit();
    }

    // Prepare data for display
    $displayUserName = htmlspecialchars($customizationData['First_Name'] . ' ' . $customizationData['Last_Name']);
    $displayFurnitureType = htmlspecialchars($customizationData['Furniture_Type']);
    $displayRequestDate = htmlspecialchars($customizationData['Request_Date']);
    $displayProductID = htmlspecialchars($customizationData['Product_ID'] ?? 'N/A'); // Handle potential NULL
    $displayProductStatus = htmlspecialchars(
        $productStatusLabels[$customizationData['Product_Status']] ?? 'Unknown Status (' . $customizationData['Product_Status'] . ')'
    );


} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    // Log error if needed: error_log("DB Error fetching customization for delete: " . $e->getMessage());
    exit();
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Confirm Deletion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.bundle.min.js"></script> <!-- Use bundle -->
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <style>
        .container_boxes table td { padding: 8px; vertical-align: top; }
        .container_boxes table td:first-child { width: 30%; font-weight: bold; }
        .alert-warning { margin-top: 15px; }
    </style>
</head>

<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE" class="logo_image">
        </span>
      </div>
      <ul class="nav-links">
          <li><a href="../dashboard/dashboard.php"><i class="bx bx-grid-alt"></i><span class="links_name">Dashboard</span></a></li>
          <li><a href="../purchase-history/read-all-history-form.php"><i class="bx bx-history"></i><span class="links_name">Purchase History</span></a></li>
          <li><a href="../reviews/read-all-reviews-form.php"><i class="bx bx-message-dots"></i><span class="links_name">All Reviews</span></a></li>
          <!-- Add other relevant links here -->
      </ul>
    </div>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Confirm Deletion</span>
            </div>
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
            <h4>Delete Customization Record</h4>

            <?php if ($customizationData): ?>
                <div class="alert alert-warning" role="alert">
                    <strong>Warning!</strong> You are about to permanently delete the following customization record. This action cannot be undone.
                     Associated images might also be deleted by the processing script.
                </div>

                <table class="table table-bordered">
                    <tr>
                        <td>Customization ID:</td>
                        <td><?= htmlspecialchars($customizationID) ?></td>
                    </tr>
                    <tr>
                        <td>User Name:</td>
                        <td><?= $displayUserName ?> (ID: <?= htmlspecialchars($customizationData['User_ID']) ?>)</td>
                    </tr>
                    <tr>
                        <td>Furniture Type:</td>
                        <td><?= $displayFurnitureType ?></td>
                    </tr>
                     <tr>
                        <td>Request Date:</td>
                        <td><?= $displayRequestDate ?></td>
                    </tr>
                     <tr>
                        <td>Current Status:</td>
                        <td><?= $displayProductStatus ?></td>
                    </tr>
                     <tr>
                        <td>Associated Product ID:</td>
                        <td><?= $displayProductID ?></td>
                    </tr>
                    <!-- Add any other critical fields you want the admin to see before deleting -->
                </table>

                <!-- Confirmation Buttons -->
                <div class="button-container mt-3">
                    <a href="read-all-custom-form.php" class="buttonBack btn btn-secondary">Cancel (Back to List)</a>
                    <!-- The actual deletion happens in delete-custom-rec.php -->
                    <a href="delete-custom-rec.php?id=<?php echo htmlspecialchars($customizationID); ?>" class="buttonDelete btn btn-danger">Confirm Deletion</a>
                </div>

            <?php else: ?>
                <div class="alert alert-danger" role="alert">
                    Could not retrieve customization details for deletion. Please go back to the list.
                </div>
                 <div class="button-container mt-3">
                    <a href="read-all-custom-form.php" class="buttonBack btn btn-secondary">Back to List</a>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <script>
        // Sidebar Toggle (Consistent version)
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        if (sidebar && sidebarBtn) {
            sidebarBtn.onclick = function () {
                sidebar.classList.toggle("active");
                sidebarBtn.classList.toggle("bx-menu-alt-right");
            };
        }

        // Profile Dropdown Toggle (Consistent version)
        const profileDetailsContainer = document.getElementById('profile-details-container');
        const profileDropdown = document.getElementById('profileDropdown');
        const dropdownIcon = document.getElementById('dropdown-icon');

        if (profileDetailsContainer && profileDropdown && dropdownIcon) {
            profileDetailsContainer.addEventListener('click', function(event) {
                // Prevent dropdown from closing if clicking inside it
                if (!profileDropdown.contains(event.target)) {
                     profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
                     // Toggle chevron icon based on display state
                     dropdownIcon.classList.toggle('bx-chevron-up', profileDropdown.style.display === 'block');
                }
            });
            // Close dropdown if clicking outside
            document.addEventListener('click', function(event) {
                if (!profileDetailsContainer.contains(event.target)) {
                    profileDropdown.style.display = 'none';
                    dropdownIcon.classList.remove('bx-chevron-up');
                }
            });
        }
    </script>
</body>
</html>
