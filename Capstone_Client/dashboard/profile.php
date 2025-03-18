<?php
session_start();

$orderStatusMap = [
    0   => 'Order Received',
    10  => 'Order Confirmed',
    20  => 'Design Finalization',
    30  => 'Material Preparation',
    40  => 'Production Started',
    50  => 'Mid-Production',
    60  => 'Finishing Process',
    70  => 'Quality Check',
    80  => 'Final Assembly',
    90  => 'Ready for Delivery',
    100 => 'Delivered / Completed'
];

$productStatusLabels = [
    0   => 'Concept Stage',
    10  => 'Design Approved',
    20  => 'Material Sourcing',
    30  => 'Cutting & Shaping',
    40  => 'Structural Assembly',
    50  => 'Detailing & Refinements',
    60  => 'Sanding & Pre-Finishing',
    70  => 'Varnishing/Painting',
    80  => 'Drying & Curing',
    90  => 'Final Inspection & Packaging',
    100 => 'Completed'
];

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

$userId = $_SESSION["user_id"];

// Include the database connection
include("../config/database.php");

// Fetch user information
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_user_info WHERE User_ID = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Check if user data was found
if (!$user) {
    die("User not found.");
}

// Fetch progress data from tbl_preorder
try {
    $stmt = $pdo->prepare("SELECT p.*, pr.Product_Name FROM tbl_preorder p JOIN tbl_prod_info pr ON p.Product_ID = pr.Product_ID WHERE p.User_ID = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $preorderData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch progress data from tbl_ready_made_orders
try {
    $stmt = $pdo->prepare("SELECT r.*, pr.Product_Name FROM tbl_ready_made_orders r JOIN tbl_prod_info pr ON r.Product_ID = pr.Product_ID WHERE r.User_ID = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $readyMadeOrdersData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch progress data from tbl_customizations
try {
    $stmt = $pdo->prepare("SELECT c.*, pr.Product_Name FROM tbl_customizations c LEFT JOIN tbl_prod_info pr ON c.Product_ID = pr.Product_ID WHERE c.User_ID = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $customizationsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$progressData = array_merge($preorderData, $readyMadeOrdersData, $customizationsData);

// Fetch purchase history data
try {
    $stmt = $pdo->prepare("SELECT ph.*, pr.Product_Name, pr.ImageURL FROM tbl_purchase_history ph JOIN tbl_prod_info pr ON ph.Product_ID = pr.Product_ID WHERE ph.User_ID = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $purchaseHistoryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link rel="stylesheet" href="../static/css-files/profile.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Google Icons Link -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <!-- Link Swiper's CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body>
    <header>
        <nav class="navbar">
            <a href="home.php" class="logo">
                <img src="../static/images/rm raw png.png" alt="" class="logo">
            </a>
            <ul class="menu-links">
                <li><a href="home.php">Home</a></li>
                <li><a href="Review.php">Reviews</a></li>
                <li><a href="Gallery.php">Gallery</a></li>
                <li><a href="cart.php" class="cart" id="cart">Cart</a>
                </li>
                <li><a href="profile.php" class="profile activecon" id="sign_in">Profile</a></li>
                 
                <span id="close-menu-btn" class="material-symbols-outlined">close</span>
            </ul>
            <span id="hamburger-btn" class="material-symbols-outlined">menu</span>
        </nav>
    </header>

    <main>
        <div class="container-profile">
            <div class="profile-icon-con">
                <img class="profile-icon" src="<?php echo ($user['PicPath']) ? '../uploads/user/' . basename($user['PicPath']) : '../static/profile-icon.png'; ?>" alt="Profile Icon">
                <p class="nameofuser"><?= $user['First_Name'] . " " . $user['Last_Name'] ?></p>
                <a class="ep--edit" href="edit-profile.php">Settings</a>
            </div>
            <div class="logout-con">
                <a href="logout.php" class="logout-btn">
                    <i class="fa fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <div class="section-container">
            <div class="section-header" onclick="toggleSection('progress-steps')">
                <h2>Order Progress</h2>
                <span class="toggle-icon">▼</span>
            </div>
            <div id="progress-steps" class="section-content">
                <?php if (empty($progressData)): ?>
                    <p>No available data</p>
                <?php else: ?>
                    <?php foreach ($progressData as $progress): ?>
                        <div class="progress-item">
                            <h3><?= $progress['Product_Name'] ?> - <?= $orderStatusMap[$progress['Order_Status']] ?></h3>
                            <ol class="stepper">
                                <?php foreach ($productStatusLabels as $status => $label): ?>
                                    <li class="<?= $progress['Product_Status'] == $status ? 'active' : '' ?>" data-toggle="modal" data-target="#progressModal" data-progress="<?= htmlspecialchars(json_encode($progress), ENT_QUOTES, 'UTF-8') ?>"><?= $label ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="section-container">
            <div class="section-header" onclick="toggleSection('purchase-history')">
                <h2>Purchase History</h2>
                <span class="toggle-icon">▼</span>
            </div>
            <div id="purchase-history" class="section-content">
                <?php if (empty($purchaseHistoryData)): ?>
                    <p>No available data</p>
                <?php else: ?>
                    <?php foreach ($purchaseHistoryData as $purchase): ?>
                        <div class="purchase-item">
                            <h3><?= $purchase['Product_Name'] ?> - <?= $purchase['Purchase_Date'] ?></h3>
                            <img src="<?= $purchase['ImageURL'] ?>" alt="<?= $purchase['Product_Name'] ?>" style="max-width: 100px; height: auto;">
                            <p><strong>Quantity:</strong> <?= $purchase['Quantity'] ?></p>
                            <p><strong>Total Price:</strong> <?= $purchase['Total_Price'] ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-row">
            <div class="footer-col">
                <h4>Info</h4>
                <ul class="links">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="Gallery.php">Gallery</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Explore</h4>
                <ul class="links">
                    <li><a href="#">Free Designs</a></li>
                    <li><a href="#">Latest Designs</a></li>
                    <li><a href="#">Themes</a></li>
                    <li><a href="#">Popular Designs</a></li>
                    <li><a href="#">Art Skills</a></li>
                    <li><a href="#">New Uploads</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Legal</h4>
                <ul class="links">
                    <li><a href="#">Customer Agreement</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">GDPR</a></li>
                    <li><a href="#">Security</a></li>
                    <li><a href="#">Testimonials</a></li>
                    <li><a href="#">Media Kit</a></li>
                </ul>
            </div>

            <div class="icons">
                <i class="fa-brands fa-facebook-f"></i>
                <i class="fa-brands fa-twitter"></i>
                <i class="fa-brands fa-linkedin"></i>
                <i class="fa-brands fa-github"></i>
            </div>
        </div>
    </footer>

    <!-- Modal -->
    <div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="progressModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="progressModalLabel">Order Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Order details will be populated here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        const orderStatusMap = <?= json_encode($orderStatusMap) ?>;
        const productStatusLabels = <?= json_encode($productStatusLabels) ?>;

        $('#progressModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var progress = button.data('progress');
            var modal = $(this);
            var modalBody = modal.find('.modal-body');

            var orderDetails = `
                <p><strong>Product Name:</strong> ${progress.Product_Name}</p>
                <p><strong>Order Status:</strong> ${orderStatusMap[progress.Order_Status]}</p>
                <p><strong>Product Status:</strong> ${productStatusLabels[progress.Product_Status]}</p>
                <p><strong>Quantity:</strong> ${progress.Quantity}</p>
                <p><strong>Total Price:</strong> ${progress.Total_Price}</p>
                <p><strong>Order Date:</strong> ${progress.Order_Date}</p>
            `;

            modalBody.html(orderDetails);
        });

        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section.classList.contains('active')) {
                section.classList.remove('active');
            } else {
                section.classList.add('active');
            }
        }
    </script>
</body>
</html>
