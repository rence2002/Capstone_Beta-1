<?php
session_start();

$orderStatusMap = [
    0   => 'Order Received',                // First step when an order is made
    10  => 'Payment Pending',               // Awaiting payment (could be downpayment)
    20  => 'Order Confirmed',               // Payment confirmed, order accepted
    30  => 'Design Stage',                  // The order is in design (conceptualizing)
    40  => 'Production Stage',              // Material sourcing and production start
    50  => 'Mid-Production',                // Halfway through production
    60  => 'Finishing Process',             // Final detailing, refinements
    70  => 'Quality Check',                 // Ensuring the product is up to standard
    80  => 'Final Assembly',                // The final stages of putting it all together
    90  => 'Ready for Delivery',            // Finished and ready to be sent to the customer
    100 => 'Delivered / Completed'          // Order successfully delivered or completed
];


$orderIcons = [
    0   => "<i class='fas fa-receipt'></i>",              // Order Received
    10  => "<i class='fas fa-hourglass-half'></i>",       // Payment Pending
    20  => "<i class='fas fa-check-circle'></i>",         // Order Confirmed
    30  => "<i class='fas fa-pencil-ruler'></i>",         // Design Stage
    40  => "<i class='fas fa-boxes'></i>",                // Production Stage
    50  => "<i class='fas fa-cogs'></i>",                 // Mid-Production
    60  => "<i class='fas fa-brush'></i>",                // Finishing Process
    70  => "<i class='fas fa-search'></i>",               // Quality Check
    80  => "<i class='fas fa-tools'></i>",                // Final Assembly
    90  => "<i class='fas fa-truck'></i>",                // Ready for Delivery
    100 => "<i class='fas fa-flag-checkered'></i>"        // Delivered / Completed
];

$productStatusLabels = [
    0   => 'Concept Stage',           // Initial design or idea stage
    10  => 'Design Approved',         // Design is approved, ready for next phase
    20  => 'Material Sourcing',       // Materials are being sourced
    30  => 'Production Started',      // Production has begun
    50  => 'Mid-Production',          // Halfway through production
    60  => 'Finishing Process',       // Final refinements
    70  => 'Quality Check',           // Quality assurance before delivery
    80  => 'Final Assembly',          // Assembly of all parts
    100 => 'Completed'                // Finished product, ready for delivery
];


$productIcons = [
    0   => "<i class='fas fa-lightbulb'></i>",            // Concept Stage
    10  => "<i class='fas fa-pencil-alt'></i>",           // Design Approved
    20  => "<i class='fas fa-box-open'></i>",             // Material Sourcing
    30  => "<i class='fas fa-industry'></i>",             // Production Started
    50  => "<i class='fas fa-cogs'></i>",                 // Mid-Production
    60  => "<i class='fas fa-brush'></i>",                // Finishing Process
    70  => "<i class='fas fa-search'></i>",               // Quality Check
    80  => "<i class='fas fa-tools'></i>",                // Final Assembly
    100 => "<i class='fas fa-check'></i>"                 // Completed
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
try {
    $stmt = $pdo->prepare("
        SELECT p.*, pr.Product_Name,
               p.Progress_Pic_10, p.Progress_Pic_20, p.Progress_Pic_30,
               p.Progress_Pic_40, p.Progress_Pic_50, p.Progress_Pic_60,
               p.Progress_Pic_70, p.Progress_Pic_80, p.Progress_Pic_90,
               p.Progress_Pic_100, p.Stop_Reason, p.Tracking_Number
        FROM tbl_progress p 
        JOIN tbl_prod_info pr ON p.Product_ID = pr.Product_ID 
        WHERE p.User_ID = :userId
    ");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $progressData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
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

echo "<script>
";
echo "const orderStatusMap = " . json_encode($orderStatusMap) . ";
";
echo "const productStatusLabels = " . json_encode($productStatusLabels) . ";
";
echo "</script>
";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link rel="stylesheet" href="../static/css-files/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>

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
          <li><a href="#about-section">About</a></li>
          <li><a href="#contact-section">Contacts</a></li>
          <li><a href="#offers-section">Offers</a></li>
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
            <img class="profile-icon" src="<?php echo ($user['PicPath']) ? '../uploads/user/' . basename($user['PicPath']) : '../static/profile-icon.png'; ?>" alt="Profile Icon">
            <p class="nameofuser"><?= $user['First_Name'] . " " . $user['Last_Name'] ?></p>
            <a class="ep--edit" href="edit-profile.php"></a>
        </div>
    </div>

    <?php 
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
?>

<!-- Pending Orders Section -->
<!-- Tabs Navigation -->
<div class="tabs">
    <button class="tab-button active" onclick="openTab('pending-orders-tab')">Pending Orders</button>
    <button class="tab-button" onclick="openTab('order-status-tab')">Order Status</button>
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
            <a class="linkqr" href="#" data-toggle="modal" data-target="#qrCodeModal">
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
                <h3><?= htmlspecialchars($order['Product_Name']) ?? 'Custom Order' ?></h3>
                <p><strong>Order Type:</strong> <?= htmlspecialchars($order['Order_Type']) ?></p>
                <p><strong>Quantity:</strong> <?= htmlspecialchars($order['Quantity']) ?></p>
                <p><strong>Total Price:</strong> <?= htmlspecialchars($order['Total_Price']) ?></p>
                <p><strong>Request Date:</strong> <?= htmlspecialchars($order['Request_Date']) ?></p>
                <p><strong>Payment Status:</strong> 
                    <?php
                    switch ($order['Payment_Status']) {
                        case 'downpayment_paid':
                            echo '<span style="color: #FFDB58;">Downpayment Paid</span>';
                            break;
                        case 'fully_paid':
                            echo '<span style="color: green;">Fully Paid</span>';
                            break;
                        case 'Pending':
                            echo '<span style="color: blue;">Pending</span>';
                            break;
                        default:
                            echo '<span style="color: red;">Unknown</span>';
                            break;
                    }
                    ?>
                </p>
                <?php if ($order['Processed'] == 1): ?>
                    <?php if ($order['Order_Status'] == 1): ?>
                        <p class="order-status"><strong>Status: </strong><span style="color: green;">Approved</span></p>
                    <?php elseif ($order['Order_Status'] == -1): ?>
                        <p class="order-status"><strong>Status: </strong><span style="color: red;">Rejected</span></p>
                    <?php endif; ?>
                    <form method="POST" action="delete_order_request.php">
                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($order['Request_ID']) ?>">
                        <button type="submit" class="okay-btn">Okay</button>
                    </form>
                <?php else: ?>
                    <p class="order-status"><strong>Status: </strong><span style="color: blue;">Pending</span></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Order Status -->
<div id="order-status-tab" class="tab-content">
    <?php if (empty($progressData)): ?>
        <p>No available data</p>
    <?php else: ?>
        <?php foreach ($progressData as $progress): ?>
            <div class="progress-item">
                <h3><?= htmlspecialchars($progress['Product_Name']) ?> - <?= $orderStatusMap[$progress['Order_Status']] ?? 'Status Unknown' ?></h3>
                <div class="stepper-container">
                    <ol class="stepper">
                        <?php 
                        $isActive = true;
                        foreach ($orderStatusMap as $status => $label): 
                            $stepClass = (($progress['Order_Status'] ?? 0) == $status) ? 'active' : ($isActive ? 'active' : '');
                            if (($progress['Order_Status'] ?? 0) == $status) {
                                $isActive = false;
                            }
                        ?>
                            <li class="updates_text <?= $stepClass ?>" data-progress="<?= htmlspecialchars(json_encode(['context' => 'order', 'data' => $progress]), ENT_QUOTES, 'UTF-8') ?>">
                                <span class="step-icon"><?= $orderIcons[$status] ?? "<i class='fas fa-circle'></i>" ?></span>
                                <span class="step-label"><?= htmlspecialchars($label) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
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
        <p>No available data</p>
    <?php else: ?>
        <?php foreach ($purchaseHistoryData as $purchase): ?>
            <div class="purchase-item">
                <h3><?= $purchase['Product_Name'] ?> - <?= $purchase['Purchase_Date'] ?></h3>
                <div class="purchase-image-div">
                    <?php
                    $imageUrls = explode(',', $purchase['ImageURL']);
                    foreach ($imageUrls as $imageUrl):
                        $imageUrl = trim($imageUrl);
                        if (!empty($imageUrl)):
                    ?>
                        <img class="purchase-image" src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($purchase['Product_Name']) ?>">
                    <?php endif; endforeach; ?>
                </div>
                <p><strong>Quantity:</strong> <?= $purchase['Quantity'] ?></p>
                <p><strong>Total Price:</strong> <?= $purchase['Total_Price'] ?></p>
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
</script>

</main>
<footer class="footer">
  <div class="footer-row">
    <div class="footer-col">
      <h4>Info</h4>
      <ul class="links">
        <li><a href="home.php">Home</a></li>
        <li><a href="#about-section">About Us</a></li>
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
      <li><a href="https://mail.google.com/mail/u/0/?fs=1&to=Rmbetisfurniture@yahoo.com&su=Your+Subject+Here&body=Your+message+here.&tf=cm" target="_blank">Email</a></li>
      <li><a href="https://www.facebook.com/BetisFurnitureExtension" target="_blank">Facebook</a></li>
      <li><a href="viber://chat?number=%2B6396596602006">Phone & Viber</a></li>
    </ul>
</div>

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
                <!-- Order/Product details will be populated here -->
                <div id="orderDetails"></div>
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
<script src="../static/Javascript-files/script.js"></script>
<script>
$(document).ready(function() {
    $('.stepper li').click(function() {
        if ($(this).hasClass('active')) {
            var stepData = $(this).data('progress');
            $('#progressModal').data('progress', stepData);
            $('#progressModal').modal('show');
        }
    });

    $('#progressModal').on('show.bs.modal', function (event) {
        var stepData = $(this).data('progress');
        if (typeof stepData !== 'object' || stepData === null) {
            console.error('Invalid or missing progress data:', stepData);
            return;
        }
        console.log('Raw progress data:', stepData);
        var modal = $(this);
        var modalBody = modal.find('.modal-body');
        var modalTitle = modal.find('.modal-title');
        var context = stepData.context;
        var progress = stepData.data;
        var progressPicUrl = stepData.progressPicUrl; // Get the step-specific picture URL
        var stopReason = progress.Stop_Reason || null; // Get the Stop Reason
        var stepStatus = stepData.stepStatus; // Get the step status
        var trackingNumber = progress.Tracking_Number || null; // Get the Tracking Number

        modalBody.html(''); // Clear the modal body

        if (context === 'order') {
            modalTitle.text('Order Details');
        } else if (context === 'product') {
            modalTitle.text('Product Details');
        }
        var pictureHtml = ''; // Initialize picture HTML
        if (progressPicUrl && progressPicUrl.trim() !== "") {
            var basePath = window.location.origin + "/Capstone_Beta/Capstone_Client/";
            var absoluteUrl = basePath + progressPicUrl.replace("../", ""); // Remove "../" from the path
            pictureHtml = `<div class="product-image"><img src="${absoluteUrl}" alt="Progress Picture" class="progress-image"></div>`;
        } else {
            pictureHtml = `<p class="no-picture">No Picture Available</p>`;
        }

        var stopReasonHtml = '';
var reasonMessages = {
    fire: "We sincerely apologize for the inconvenience caused by the fire.",
    flood: "We deeply regret the disruption caused by the flood.",
    typhoon: "We are truly sorry for the difficulties caused by the typhoon.",
    earthquake: "We apologize for the disruption caused by the earthquake."
};

if (stopReason && stopReason.trim() !== "") {
    var message = reasonMessages[stopReason] || stopReason;
    stopReasonHtml = `<p class="stopreason"><strong>Stop Reason:</strong> ${message}</p>`;
}

        var trackingNumberHtml = '';
        if (trackingNumber && trackingNumber.trim() !== "") {
            trackingNumberHtml = `<p class="tracking-number"><strong>Tracking Number:</strong> ${trackingNumber}</p>`;
        }

        if (context === 'order') {
            var orderDetails = `
                ${pictureHtml}
                <p class="product-status"><strong>Product Name:</strong> ${progress.Product_Name || 'Unknown Product'}</p>
                <p class="product-status"><strong>Order Status:</strong> ${orderStatusMap[progress.Order_Status] || 'Status Unknown'}</p>
                <p class="product-status"><strong>Quantity:</strong> ${progress.Quantity || 'N/A'}</p>
                <p class="product-status"><strong>Total Price:</strong> ${progress.Total_Price || 'N/A'}</p>
                <p class="product-status"><strong>Order Date:</strong> ${progress.Date_Added || 'N/A'}</p>
                ${trackingNumberHtml}
                ${stopReasonHtml}
            `;
            modalBody.html(orderDetails);
        } else if (context === 'product') {
            var productDetails = `
                ${pictureHtml}
                <p class="product-status"><strong>Product Name:</strong> ${progress.Product_Name || 'Unknown Product'}</p>
                <p class="product-status"><strong>Product Status:</strong> ${productStatusLabels[stepStatus] || 'Status Unknown'}</p>
                <p class="product-status"><strong>Quantity:</strong> ${progress.Quantity || 'N/A'}</p>
                <p class="product-status"><strong>Total Price:</strong> ${progress.Total_Price || 'N/A'}</p>
                <p class="product-status"><strong>Order Date:</strong> ${progress.Date_Added || 'N/A'}</p>
                ${stopReasonHtml}
            `;
            modalBody.html(productDetails);
        }
    });
});
</script>
<script>
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) {
        console.error(`Section with id "${sectionId}" not found.`);
        return;
    }
    if (section.classList.contains('active')) {
        section.classList.remove('active');
    } else {
        section.classList.add('active');
    }
}
</script>
<script>
function deleteOrderRequest(requestId, button) {
    if (!confirm("Are you sure you want to delete this order?")) {
        return;
    }

    // Send an AJAX request to delete the order
    fetch('delete_order_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `request_id=${encodeURIComponent(requestId)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the order item from the DOM
            const orderItem = button.closest('.pending-order-item');
            if (orderItem) {
                orderItem.remove();
            }

            // Check if there are no more pending orders
            const pendingOrdersContainer = document.getElementById('pending-orders');
            if (pendingOrdersContainer && pendingOrdersContainer.children.length === 0) {
                pendingOrdersContainer.innerHTML = '<p>No pending orders</p>';
            }
        } else {
            alert(data.message || "Failed to delete the order. Please try again.");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred. Please try again.");
    });
}
</script>
</body>
</html>