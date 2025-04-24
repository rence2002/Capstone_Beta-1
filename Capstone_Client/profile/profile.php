<?php
session_start();

// Use the NEW Product Status labels provided in the prompt
$productStatusLabels = [
    0   => 'Request Approved',         // 0% - Order placed by the customer
    10  => 'Design Approved',        // 10% - Finalized by customer
    20  => 'Material Sourcing',      // 20% - Gathering necessary materials
    30  => 'Cutting & Shaping',      // 30% - Preparing materials
    40  => 'Structural Assembly',    // 40% - Base framework built
    50  => 'Detailing & Refinements',// 50% - Carvings, upholstery, elements added
    60  => 'Sanding & Pre-Finishing',// 60% - Smoothening, preparing for final coat
    70  => 'Varnishing/Painting',    // 70% - Applying the final finish
    80  => 'Drying & Curing',        // 80% - Final coating sets in
    90  => 'Final Inspection & Packaging', // 90% - Quality control before handover
    95  => 'Ready for Shipment',     // 95% - Ready for delivery/pickup
    98  => 'Order Delivered',        // 98% - Handed over to customer/courier
    100 => 'Order Recieved',         // 100% - Confirmed received by customer (Note: Typo kept as provided)
];

// Updated Product Icons to match new steps
$productIcons = [
    0   => "<i class='fas fa-thumbs-up'></i>",          // Request Approved
    10  => "<i class='fas fa-pencil-alt'></i>",         // Design Approved
    20  => "<i class='fas fa-box-open'></i>",           // Material Sourcing
    30  => "<i class='fas fa-cut'></i>",                // Cutting & Shaping
    40  => "<i class='fas fa-hammer'></i>",             // Structural Assembly
    50  => "<i class='fas fa-magic'></i>",              // Detailing & Refinements (Using 'magic' for intricate work)
    60  => "<i class='fas fa-wind'></i>",               // Sanding & Pre-Finishing (Using 'wind' for smoothness)
    70  => "<i class='fas fa-paint-brush'></i>",        // Varnishing/Painting
    80  => "<i class='fas fa-clock'></i>",              // Drying & Curing
    90  => "<i class='fas fa-clipboard-check'></i>",    // Final Inspection & Packaging
    95  => "<i class='fas fa-shipping-fast'></i>",      // Ready for Shipment
    98  => "<i class='fas fa-truck'></i>",              // Order Delivered
    100 => "<i class='fas fa-check-double'></i>"        // Order Received
];


// Check if the user is logged in
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

if (!$user) {
    die("User not found.");
}

// Fetch pending orders data from tbl_order_request
try {
    $stmt = $pdo->prepare("
        SELECT orq.*, pr.Product_Name
        FROM tbl_order_request orq
        LEFT JOIN tbl_prod_info pr ON orq.Product_ID = pr.Product_ID
        WHERE orq.User_ID = :userId
    ");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $pendingOrdersData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch progress data from tbl_progress
// IMPORTANT: This query assumes tbl_progress might have the new Progress_Pic columns (40, 90, 95, 98).
// If not, those values will be NULL.
try {
    // Attempt to select all potential progress picture columns
    $stmt = $pdo->prepare("
        SELECT p.*, pr.Product_Name,
               p.Progress_Pic_10, p.Progress_Pic_20, p.Progress_Pic_30,
               p.Progress_Pic_40, p.Progress_Pic_50, p.Progress_Pic_60,
               p.Progress_Pic_70, p.Progress_Pic_80, p.Progress_Pic_90,
               p.Progress_Pic_95, p.Progress_Pic_98, -- Added 95, 98 (assuming they might exist)
               p.Progress_Pic_100, p.Stop_Reason, p.Tracking_Number
        FROM tbl_progress p
        JOIN tbl_prod_info pr ON p.Product_ID = pr.Product_ID
        WHERE p.User_ID = :userId
    ");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $progressData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle potential "column not found" errors gracefully if schema wasn't updated
     if (strpos($e->getMessage(), 'Unknown column') !== false) {
        // Fallback query without the potentially missing columns
        try {
             $stmt = $pdo->prepare("
                SELECT p.*, pr.Product_Name,
                       p.Progress_Pic_10, p.Progress_Pic_20, p.Progress_Pic_30,
                       p.Progress_Pic_50, p.Progress_Pic_60,
                       p.Progress_Pic_70, p.Progress_Pic_80,
                       p.Progress_Pic_100, p.Stop_Reason, p.Tracking_Number
                FROM tbl_progress p
                JOIN tbl_prod_info pr ON p.Product_ID = pr.Product_ID
                WHERE p.User_ID = :userId
            ");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
            $stmt->execute();
            $progressData = $stmt->fetchAll(PDO::FETCH_ASSOC);
             // Add null placeholders for missing pic keys to avoid errors later
             foreach ($progressData as &$item) {
                 $item['Progress_Pic_40'] = null;
                 $item['Progress_Pic_90'] = null;
                 $item['Progress_Pic_95'] = null;
                 $item['Progress_Pic_98'] = null;
             }
             unset($item); // Unset reference
        } catch (PDOException $e2) {
             die("Database error (fallback query failed): " . $e2->getMessage());
        }
    } else {
        die("Database error: " . $e->getMessage());
    }
}


// Fetch purchase history data from tbl_purchase_history
try {
    $stmt = $pdo->prepare("
        SELECT ph.*, pr.Product_Name, pr.ImageURL,
        CASE WHEN r.Review_ID IS NOT NULL THEN r.Review_ID ELSE NULL END AS Review_ID
        FROM tbl_purchase_history ph
        JOIN tbl_prod_info pr ON ph.Product_ID = pr.Product_ID
        LEFT JOIN tbl_reviews r ON ph.Product_ID = r.Product_ID AND ph.User_ID = r.User_ID
        WHERE ph.User_ID = :userId
    ");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $purchaseHistoryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Check if there are any pending orders that are unpaid and unprocessed
$hasPending = false;
if (!empty($pendingOrdersData)) {
    foreach ($pendingOrdersData as $order) {
        if ($order['Payment_Status'] === 'Pending' && $order['Processed'] != 1) {
            $hasPending = true;
            break;
        }
    }
}

// Pass the updated product status labels to JavaScript
echo "<script>\n";
echo "const productStatusLabels = " . json_encode($productStatusLabels) . ";\n";
echo "</script>\n";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link rel="stylesheet" href="../static/css-files/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Ensure FontAwesome 6 for newer icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" /> --> <!-- Redundant if 6.0.0 is included -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> --> <!-- Remove if using FA6 -->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Add any specific styles if needed */
        .progress-image {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            border-radius: 5px;
        }
        .no-picture {
            color: #888;
            margin-top: 10px;
            font-style: italic;
        }
        .stopreason {
            color: #dc3545; /* Bootstrap danger color */
            font-weight: bold;
            margin-top: 10px;
            background-color: #f8d7da; /* Light red background */
            border: 1px solid #f5c6cb;
            padding: 8px;
            border-radius: 4px;
        }
        .tracking-number {
            margin-top: 10px;
            font-weight: bold;
        }
        .purchase-image-div {
            display: flex;
            gap: 10px; /* Spacing between images */
            flex-wrap: wrap; /* Allow images to wrap */
            margin-bottom: 10px;
        }
        .purchase-image {
            max-width: 100px; /* Adjust size as needed */
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .review-btn-container {
            margin-top: 10px;
        }
        .WriteButton, .EditButton {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .EditButton {
            background-color: #ffc107; /* Yellow for edit */
            color: black;
        }
        .WriteButton:hover {
            background-color: #0056b3;
        }
        .EditButton:hover {
             background-color: #e0a800;
        }
        .okay-btn {
             padding: 5px 10px;
             background-color: #28a745; /* Green */
             color: white;
             border: none;
             border-radius: 4px;
             cursor: pointer;
             font-size: 0.9em;
             margin-top: 5px;
             transition: background-color 0.2s ease;
        }
        .okay-btn:hover {
            background-color: #218838;
        }
        .pending-order-item-note {
            background-color: #fff3cd; /* Light yellow */
            border: 1px solid #ffeeba;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #856404; /* Dark yellow text */
        }
        .pending-order-item-note ul {
            margin-left: 20px;
            margin-top: 10px;
            list-style: disc;
        }
        .pending-order-item-note .linkqr {
            color: #007bff; /* Blue link */
            text-decoration: underline;
            cursor: pointer;
        }
        .pending-order-item-note .linkqr:hover {
            color: #0056b3;
        }

    </style>
</head>
<body>
<header>
  <nav class="navbar">
    <a href="../dashboard/home.php" class="logo">
      <img src="../static/images/rm raw png.png" alt="" class="logo">
    </a>
    <ul class="menu-links">
      <li class="">
        <a href="../dashboard/home.php">Home</a>
        <ul class="dropdown-menus">
          <li><a href="../dashboard/home.php#about-section">About</a></li>
          <li><a href="../dashboard/home.php#contact-section">Contacts</a></li>
          <li><a href="../dashboard/home.php#offers-section">Offers</a></li>
        </ul>
      </li>
      <li><a href="../reviews/review.php">Reviews</a></li>
      <li><a href="../gallery/gallery.php" class="">Gallery</a></li>
      <li><a href="../cart/cart.php" class="cart" id="cart">Cart</a></li>
      <li class="dropdown">
        <a href="../profile/profile.php" class="profile activecon" id="sign_in">Profile</a>
        <ul class="dropdown-menus">
          <li><a href="../profile/profile.php">Profile</a></li>
          <li><a href="../logout/logout.php">Logout</a></li>
        </ul>
      </li>
      <span id="close-menu-btn" class="material-symbols-outlined">close</span>
    </ul>
    <span id="hamburger-btn" class="material-symbols-outlined">menu</span>
  </nav>
</header>
<main>
    <div class="container-profile">
        <div class="profile-icon-con">
            <img class="profile-icon" src="<?php echo ($user['PicPath']) ? '../uploads/user/' . basename($user['PicPath']) : '../static/images/profile-icon.png'; ?>" alt="Profile Icon"> <!-- Corrected default path -->
            <p class="nameofuser"><?= htmlspecialchars($user['First_Name']) . " " . htmlspecialchars($user['Last_Name']) ?></p>
            <a class="ep--edit" href="edit-profile.php">Edit Profile</a> <!-- Added text for clarity -->
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs">
        <button class="tab-button active" onclick="openTab('pending-orders-tab')">Pending Orders</button>
        <button class="tab-button" onclick="openTab('product-status-tab')">Product Status</button>
        <button class="tab-button" onclick="openTab('purchase-history-tab')">Purchase History</button>
    </div>

    <!-- Tabs Content -->

    <!-- Pending Orders -->
    <div id="pending-orders-tab" class="tab-content active">
        <!-- Payment Note (Shown only if there are unpaid & unprocessed pending orders) -->
        <?php if ($hasPending): ?>
        <div class="pending-order-item-note">
            <p>
                <strong>Please pay your downpayment.</strong> This is based on the store policy that you agreed to. Below are the available modes of payment:
            </p>
            <ul>
                <li><strong>GCash:</strong> 0975 687 28572</li>
                <li><strong>PayMaya:</strong> 0975 687 28572</li>
            </ul>
            <p>
                <a class="linkqr" data-toggle="modal" data-target="#qrCodeModal">
                    <strong>Click here to view QR code</strong>
                </a>
            </p>
        </div>
        <?php endif; ?>

        <?php if (empty($pendingOrdersData)): ?>
            <p>No pending orders</p>
        <?php else: ?>
            <?php foreach ($pendingOrdersData as $order): ?>
                <div class="pending-order-item">
                    <h3><?= htmlspecialchars($order['Product_Name'] ?? 'Custom Order Request') ?></h3>
                    <p><strong>Order Type:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['Order_Type']))) ?></p> <!-- Improved display -->
                    <p><strong>Quantity:</strong> <?= htmlspecialchars($order['Quantity']) ?></p>
                    <p><strong>Total Price:</strong> ₱<?= htmlspecialchars(number_format($order['Total_Price'], 2)) ?></p> <!-- Added currency symbol -->
                    <p><strong>Request Date:</strong> <?= htmlspecialchars(date('M d, Y H:i', strtotime($order['Request_Date']))) ?></p> <!-- Nicer date format -->
                    <p><strong>Payment Status:</strong>z
                        <?php
                        switch ($order['Payment_Status']) {
                            case 'downpayment_paid':
                                echo '<span style="color: #ffc107; font-weight: bold;">Downpayment Paid</span>'; // Bootstrap warning color
                                break;
                            case 'fully_paid':
                                echo '<span style="color: #28a745; font-weight: bold;">Fully Paid</span>'; // Bootstrap success color
                                break;
                            case 'Pending':
                                echo '<span style="color: #007bff; font-weight: bold;">Pending</span>'; // Bootstrap primary color
                                break;
                            default:
                                echo '<span style="color: #dc3545; font-weight: bold;">Unknown</span>'; // Bootstrap danger color
                                break;
                        }
                        ?>
                    </p>
                    <?php if ($order['Processed'] == 1): ?>
                        <p class="order-status"><strong>Status: </strong><span style="color: orange; font-weight: bold;">Reviewed by Admin</span></p>
                        <form method="POST" action="delete_order_request.php" onsubmit="return confirm('This will remove the request from your view. Are you sure?');">
                            <input type="hidden" name="request_id" value="<?= htmlspecialchars($order['Request_ID']) ?>">
                            <button type="submit" class="okay-btn">Okay</button>
                        </form>
                    <?php else: ?>
                        <p class="order-status"><strong>Status: </strong><span style="color: #17a2b8; font-weight: bold;">Pending Review</span></p> <!-- Bootstrap info color -->
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Product Status -->
<div id="product-status-tab" class="tab-content">
    <?php if (empty($progressData)): ?>
        <p>No available data</p>
    <?php else: ?>
        <?php foreach ($progressData as $progress): ?>
            <div class="progress-item">
                <h3><?= $progress['Product_Name'] ?></h3>
                <div class="stepper-container">
                    <ol class="stepper">
                        <?php 
                        $isActive = true;
                        foreach ($productStatusLabels as $status => $label): 
                            $stepClass = (($progress['Product_Status'] ?? 0) == $status) ? 'active' : ($isActive ? 'active' : '');
                            if (($progress['Product_Status'] ?? 0) == $status) {
                                $isActive = false;
                            }
                            $statusKey = "Progress_Pic_" . $status;
                            $progressPicUrl = $progress[$statusKey] ?? null;
                            $stepData = [
                                'context' => 'product',
                                'data' => $progress,
                                'progressPicUrl' => $progressPicUrl,
                                'stepStatus' => $status
                            ];
                        ?>
                            <li class="updates_text <?= $stepClass ?>" data-progress="<?= htmlspecialchars(json_encode($stepData), ENT_QUOTES, 'UTF-8') ?>">
                                <span class="step-icon"><?= $productIcons[$status] ?? "<i class='fas fa-circle'></i>" ?></span>
                                <span class="step-label"><?= $label ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

    <!-- Purchase History -->
    <div id="purchase-history-tab" class="tab-content">
        <?php if (empty($purchaseHistoryData)): ?>
            <p>No purchase history available.</p>
        <?php else: ?>
            <?php foreach ($purchaseHistoryData as $purchase): ?>
                <div class="purchase-item">
                    <h3><?= htmlspecialchars($purchase['Product_Name']) ?></h3>
                     <p><strong>Purchase Date:</strong> <?= htmlspecialchars(date('M d, Y H:i', strtotime($purchase['Purchase_Date']))) ?></p>
                    <div class="purchase-image-div">
                        <?php
                        $imageUrls = !empty($purchase['ImageURL']) ? explode(',', $purchase['ImageURL']) : [];
                        if (!empty($imageUrls)) {
                            foreach ($imageUrls as $imageUrl):
                                $imageUrl = trim($imageUrl);
                                if (!empty($imageUrl)):
                                    $absoluteImageUrl = $imageUrl;
                                    // Basic check for relative path - adjust logic if needed
                                    if (strpos($imageUrl, '../') === 0) {
                                         // Construct URL relative to the *web root* if possible
                                         $absoluteImageUrl = '/Capstone_Beta/' . substr($imageUrl, 3); // Assumes Capstone_Beta is directly under htdocs
                                         // Or use a more robust base URL definition if available
                                    } elseif (!preg_match('/^https?:\/\//', $imageUrl) && !preg_match('/^\//', $imageUrl)) {
                                        // If not absolute and not root-relative, assume it's relative to some base path
                                        // This might need adjustment based on where images are stored vs script location
                                        $absoluteImageUrl = '/Capstone_Beta/uploads/product/' . $imageUrl; // Example assumption
                                    }
                        ?>
                            <img class="purchase-image" src="<?= htmlspecialchars($absoluteImageUrl) ?>" alt="<?= htmlspecialchars($purchase['Product_Name']) ?>">
                        <?php endif; endforeach;
                        } else {
                            echo "<p>No image available</p>";
                        }
                        ?>
                    </div>
                    <p><strong>Quantity:</strong> <?= htmlspecialchars($purchase['Quantity']) ?></p>
                    <p><strong>Total Price:</strong> ₱<?= htmlspecialchars(number_format($purchase['Total_Price'], 2)) ?></p>
                    <p><strong>Order Type:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $purchase['Order_Type']))) ?></p>
                    <div class="review-btn-container">
                        <?php if (isset($purchase['Review_ID'])): ?>
                            <a href="../reviews/edit_review.php?review_id=<?= urlencode($purchase['Review_ID']) ?>" class="EditButton">Edit Review</a>
                        <?php else: ?>
                            <a href="../reviews/review.php?product_id=<?= urlencode($purchase['Product_ID']) ?>" class="WriteButton">Write Review</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        function openTab(tabId) {
            const tabs = document.querySelectorAll('.tab-content');
            const buttons = document.querySelectorAll('.tab-button');

            tabs.forEach(tab => tab.classList.remove('active'));
            buttons.forEach(btn => btn.classList.remove('active'));

            document.getElementById(tabId).classList.add('active');
            document.querySelector(`.tab-button[onclick*="${tabId}"]`).classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', () => {
             // Ensure the first tab is active on load
             openTab('pending-orders-tab');
        });
    </script>

</main>
<footer class="footer">
  <div class="footer-row">
    <div class="footer-col">
      <h4>Info</h4>
      <ul class="links">
        <li><a href="../dashboard/home.php">Home</a></li>
        <li><a href="../dashboard/home.php#about-section">About Us</a></li>
        <li><a href="../gallery/gallery.php">Gallery</a></li>
        <li><a href="../reviews/review.php">Reviews</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Legal</h4>
      <ul class="links">
      <li><a href="../agreement/agreement.html">Customer Agreement & Privacy Policy</a></li>
      </ul>
    </div>

    <div class="footer-col">
    <h4>Contact</h4>
    <ul class="links">
      <li><a href="https://mail.google.com/mail/u/0/?fs=1&to=Rmbetisfurniture@yahoo.com&su=Inquiry&body=Your+message+here.&tf=cm" target="_blank">Email</a></li>
      <li><a href="https://www.facebook.com/BetisFurnitureExtension" target="_blank">Facebook</a></li>
      <li><a href="viber://chat?number=%2B6396596602006">Phone & Viber</a></li>
    </ul>
</div>

    </div>
  </div>
</footer>

<!-- Modal for Product Progress Details -->
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="progressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> <!-- Made modal larger -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="progressModalLabel">Product Progress Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Content populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title" id="qrCodeModalLabel">Scan to Pay</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <img src="../static/images/qrcode.jpeg" alt="QR Code" class="img-fluid">
        <p class="mt-3"><strong>Use your mobile app to scan this QR code.</strong></p>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="../static/Javascript-files/script.js"></script> <!-- General site script -->
<script>
$(document).ready(function() {
    // Handle clicks on stepper items to show modal
    $('.stepper li').click(function() {
        if ($(this).hasClass('active')) { // Only allow clicks on active/completed steps
            var stepData = $(this).data('progress');
            if (typeof stepData === 'object' && stepData !== null) {
                $('#progressModal').data('progress', stepData);
                $('#progressModal').modal('show');
            } else {
                console.error('Could not retrieve progress data from clicked step.');
                alert('Could not load details for this step.');
            }
        }
    });

    // Populate modal when it's about to be shown
    $('#progressModal').on('show.bs.modal', function (event) {
        var stepData = $(this).data('progress');

        // Validate the data
        if (typeof stepData !== 'object' || stepData === null || stepData.context !== 'product' || typeof stepData.data !== 'object') {
            console.error('Invalid or missing progress data for modal:', stepData);
            $(this).find('.modal-body').html('<p class="text-danger">Error loading details. Invalid data received.</p>');
            return;
        }
        // console.log('Modal data:', stepData); // For debugging

        var modal = $(this);
        var modalBody = modal.find('.modal-body');
        var modalTitle = modal.find('.modal-title');
        var progress = stepData.data; // The main data object for the product
        var progressPicUrl = stepData.progressPicUrl; // The picture URL specific to the clicked step (can be null)
        var stopReason = progress.Stop_Reason || null;
        var stepStatus = stepData.stepStatus; // The status code of the clicked step
        var trackingNumber = progress.Tracking_Number || null;
        var overallProductStatus = progress.Product_Status || 0; // Get the overall status

        modalBody.html(''); // Clear previous content
        modalTitle.text('Product Progress Details'); // Set modal title

        // --- Picture Handling ---
        var pictureHtml = '';
        if (progressPicUrl && progressPicUrl.trim() !== "") {
            var basePath = window.location.origin + "/Capstone_Beta/"; // Base path assumption
            var absoluteUrl = progressPicUrl;
            if (!progressPicUrl.startsWith('http') && !progressPicUrl.startsWith('/')) {
                 absoluteUrl = basePath + progressPicUrl.replace(/^\.?\.?\//, "");
            } else if (progressPicUrl.startsWith('/')) {
                 absoluteUrl = window.location.origin + progressPicUrl;
            }
             // Add error handling for image loading
            pictureHtml = `<div class="product-image text-center mb-3">
                             <img src="${absoluteUrl}" alt="Progress Picture for step ${stepStatus}" class="progress-image img-fluid" onerror="this.onerror=null; this.src='../static/images/placeholder.png'; this.alt='Image not found';">
                           </div>`;
        } else {
            pictureHtml = `<p class="no-picture text-center mb-3">No Picture Available for this step</p>`;
        }

        // --- Stop Reason Handling ---
        var stopReasonHtml = '';
        var reasonMessages = {
            fire: "We sincerely apologize for the inconvenience caused by the fire.",
            flood: "We deeply regret the disruption caused by the flood.",
            typhoon: "We are truly sorry for the difficulties caused by the typhoon.",
            earthquake: "We apologize for the disruption caused by the earthquake."
        };
        if (stopReason && stopReason.trim() !== "") {
            var message = reasonMessages[stopReason.toLowerCase()] || stopReason;
            stopReasonHtml = `<div class="alert alert-danger" role="alert"><strong>Stop Reason:</strong> ${message}</div>`; // Use Bootstrap alert
        }

        // --- Tracking Number Handling ---
        var trackingNumberHtml = '';
        // Show tracking number only if available AND product status is 'Ready for Shipment' or later (>= 95)
        if (trackingNumber && trackingNumber.trim() !== "" && overallProductStatus >= 95) {
            trackingNumberHtml = `<p class="tracking-number"><strong>Tracking Number:</strong> ${trackingNumber}</p>`;
        }

        // --- Construct Modal Body ---
        // Use productStatusLabels (globally available from PHP)
        var currentStepLabel = typeof productStatusLabels !== 'undefined' && productStatusLabels[stepStatus]
                                ? productStatusLabels[stepStatus]
                                : 'Status Unknown';

        // Format Order Type nicely
        var formattedOrderType = progress.Order_Type
                                ? progress.Order_Type.charAt(0).toUpperCase() + progress.Order_Type.slice(1).replace('_', ' ')
                                : 'N/A';

        // Format Price
        var formattedPrice = progress.Total_Price ? '₱' + parseFloat(progress.Total_Price).toFixed(2) : 'N/A';

        // Format Date
        var formattedDate = progress.Date_Added ? new Date(progress.Date_Added).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';


        var productDetails = `
            ${pictureHtml}
            <div class="details-section">
                <p><strong>Product Name:</strong> ${progress.Product_Name || 'Unknown Product'}</p>
                <p><strong>Details for Step:</strong> <span class="badge badge-info">${currentStepLabel}</span></p> <!-- Use badge for status -->
                <hr>
                <p><strong>Order Type:</strong> ${formattedOrderType}</p>
                <p><strong>Quantity:</strong> ${progress.Quantity || 'N/A'}</p>
                <p><strong>Total Price:</strong> ${formattedPrice}</p>
                <p><strong>Order Date:</strong> ${formattedDate}</p>
                ${trackingNumberHtml}
            </div>
             ${stopReasonHtml} <!-- Display stop reason below details -->
        `;
        modalBody.html(productDetails);
    });

});
</script>

</body>
</html>
