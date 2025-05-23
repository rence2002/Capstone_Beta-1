<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin's ID is stored in session after login
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
$baseUrl = 'http://localhost/Capstone_Beta/';
$profilePicPath = $admin['PicPath'];
// Remove any leading slashes or duplicate directories
$profilePicPath = preg_replace('/^[\/]*(Capstone_Beta\/)?(Capstone_Admin\/)?(admin\/)?/', '', $profilePicPath);
$profilePicPath = htmlspecialchars($profilePicPath);

// Check if the request is an AJAX search request
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "
        SELECT 
            p.Product_ID,
            p.Product_Name,
            p.Stock,
            p.Price,
            p.ImageURL,
            COALESCE(AVG(r.Rating), 0) AS AverageRating
        FROM tbl_prod_info p
        LEFT JOIN tbl_reviews r ON p.Product_ID = r.Product_ID
        WHERE p.product_type != 'custom' AND (p.Product_Name LIKE :search
        OR p.Category LIKE :search
        OR p.Description LIKE :search)
        GROUP BY p.Product_ID, p.Product_Name
    ";
    $stmt = $pdo->prepare($query);
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the table rows for AJAX requests
    echo '<table width="100%" border="1" cellspacing="5">
        <tr>
            <th>PRODUCT NAME</th>
            <th>STOCK</th>
            <th>PRICE</th>
            <th>IMAGE</th>
            <th>Rating</th>
            <th colspan="3" style="text-align: center;">ACTIONS</th>
        </tr>';
    foreach ($rows as $row) {
        $productID = htmlspecialchars($row["Product_ID"]);
        $productName = htmlspecialchars($row["Product_Name"]);
        $stock = htmlspecialchars($row["Stock"]);
        $price = htmlspecialchars($row["Price"]);
        $imageURL = htmlspecialchars($row["ImageURL"]);
        $averageRating = htmlspecialchars($row['AverageRating']);

        // Split the ImageURL string by commas and fetch the first image
        $imageURLs = explode(',', $imageURL);
        $firstImageURL = $imageURLs[0]; // Get the first image

        echo '
        <tr>
            <td>'.$productName.'</td>
            <td>'.$stock.'</td>
            <td>'.$price.'</td>
            <td>';
        if (!empty($firstImageURL)) {
            echo '<img src="/Capstone_Beta/' . $firstImageURL . '" alt="Product Image" style="width:50px;height:50px;">';
        } else {
            echo '<img src="/Capstone_Beta/static/images/placeholder.jpg" alt="Placeholder Image" style="width:50px;height:50px;">';
        }
        echo '</td>
            <td>';
        if ($averageRating > 0) {
            echo $averageRating . ' / 5';
        } else {
            echo 'N/A';
        }
        echo '</td>
            <td style="text-align: center;">
                <a class="buttonView" href="read-one-product-form.php?id='.$productID.'" target="_parent">View</a>
            </td>
            <td style="text-align: center;">
                <a class="buttonEdit" href="update-product-form.php?id='.$productID.'" target="_parent">Edit</a>
            </td>
            <td style="text-align: center;">
                <a class="buttonDelete" href="delete-product-form.php?id='.$productID.'" target="_parent">Delete</a>
            </td>
        </tr>';
    }
    echo '</table>';
    exit; // Stop further execution for AJAX requests
}

// Fetch product records from the database, excluding products with 'custom' product_type
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "
    SELECT 
        p.Product_ID,
        p.Product_Name,
        p.Stock,
        p.Price,
        p.ImageURL,
        COALESCE(AVG(r.Rating), 0) AS AverageRating
    FROM tbl_prod_info p
    LEFT JOIN tbl_reviews r ON p.Product_ID = r.Product_ID
    WHERE p.product_type != 'custom' AND (p.Product_Name LIKE :search
    OR p.Category LIKE :search
    OR p.Description LIKE :search)
    GROUP BY p.Product_ID, p.Product_Name
";
$stmt = $pdo->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
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
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" />
                </form>
            </div>


            <div class="profile-details" onclick="toggleDropdown()">
                <img src="<?php echo $baseUrl . $profilePicPath; ?>" alt="Profile Picture" />
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
        <h4>PRODUCT LIST <a href="create-product-form.php">Create New Product</a></h4>
        <!-- Add Back to Dashboard button -->
        <div class="button-container">
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                </div>
        <div id="product-list">
            <table width="100%" border="1" cellspacing="5">
                <tr>
                    <th>PRODUCT NAME</th>
                    <th>STOCK</th>
                    <th>PRICE</th>
                    <th>IMAGE</th>
                    <th>Rating</th>
                    <th colspan="3" style="text-align: center;">ACTIONS</th>
                </tr>
                <?php
                foreach ($rows as $row) {
                    $productID = htmlspecialchars($row["Product_ID"]);
                    $productName = htmlspecialchars($row["Product_Name"]);
                    $stock = htmlspecialchars($row["Stock"]);
                    $price = htmlspecialchars($row["Price"]);
                    $imageURL = htmlspecialchars($row["ImageURL"]);
                    $averageRating = htmlspecialchars($row['AverageRating']);

                    // Split the ImageURL string by commas and fetch the first image
                    $imageURLs = explode(',', $imageURL);
                    $firstImageURL = $imageURLs[0]; // Get the first image
                ?>
                <tr>
                    <td><?php echo $productName; ?></td>
                    <td><?php echo $stock; ?></td>
                    <td><?php echo $price; ?></td>
                    <td>
                        <?php
                        // Check if the first image URL is not empty or null
                        if (!empty($firstImageURL)) {
                            echo '<img src="/Capstone_Beta/' . $firstImageURL . '" alt="Product Image" style="width:50px;height:50px;">';
                        } else {
                            echo '<img src="/Capstone_Beta/static/images/placeholder.jpg" alt="Placeholder Image" style="width:50px;height:50px;">';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                         if ($averageRating > 0) {
                            echo $averageRating . ' / 5';
                         } else {
                             echo 'N/A';
                         }
                         ?>
                    </td>
                    <td style="text-align: center;">
                        <a class="buttonView" href="read-one-product-form.php?id=<?php echo $productID; ?>" target="_parent">View</a>
                    </td>
                    <td style="text-align: center;">
                        <a class="buttonEdit" href="update-product-form.php?id=<?php echo $productID; ?>" target="_parent">Edit</a>
                    </td>
                    <td style="text-align: center;">
                        <a class="buttonDelete" href="delete-product-form.php?id=<?php echo $productID; ?>" target="_parent">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </table>
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

    document.querySelector('.search-box input[name="search"]').addEventListener('input', function () {
        const searchValue = this.value;

        // Send an AJAX request to fetch filtered results
        fetch(`read-all-product-form.php?search=${encodeURIComponent(searchValue)}`)
            .then(response => response.text())
            .then(data => {
                // Update the product list with the filtered results
                const productList = document.getElementById('product-list');
                productList.innerHTML = data;
            })
            .catch(error => console.error('Error fetching search results:', error));
    });
</script>

</body>
</html>
