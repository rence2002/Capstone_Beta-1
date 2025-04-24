<?php
session_start();

// Include database connection
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// --- Get form data and sanitize inputs ---
// Use the hidden field name from the updated form
$readyMadeOrderID = isset($_POST['txtReadyMadeOrderID_hidden']) ? (int)$_POST['txtReadyMadeOrderID_hidden'] : null;
$quantity = isset($_POST['txtQuantity']) ? (int)$_POST['txtQuantity'] : null;
// Product_Status, Payment_Status, Tracking_Number are NOT submitted by the form anymore

// --- Validate required fields ---
// Only check for Order ID and Quantity now
if (!$readyMadeOrderID || !$quantity || $quantity < 1) { // Ensure quantity is at least 1
    // Consider more specific error messages or logging
    die("Error: Missing or invalid required fields. Order ID and a valid Quantity (>= 1) are required.");
}

try {
    // --- Fetch product base price ---
    // We only need the base price to recalculate the total price accurately on the server
    $query = "
        SELECT
            p.Price AS Product_Base_Price
        FROM tbl_ready_made_orders r
        JOIN tbl_prod_info p ON r.Product_ID = p.Product_ID
        WHERE r.ReadyMadeOrder_ID = :readyMadeOrderID
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':readyMadeOrderID', $readyMadeOrderID, PDO::PARAM_INT);
    $stmt->execute();
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orderData) {
        // This could happen if the order ID is invalid or the associated product is deleted
        die("Error: Order not found or associated product price is missing.");
    }

    $productBasePrice = (float)$orderData['Product_Base_Price'];

    // --- Server-Side Calculation for Total Price ---
    // Always recalculate server-side to prevent manipulation
    $calculatedTotalPrice = $quantity * $productBasePrice;

    // --- Update tbl_ready_made_orders ---
    // Only update Quantity and Total_Price based on the form submission
    $updateOrderQuery = "
        UPDATE tbl_ready_made_orders
        SET
            Quantity = :quantity,
            Total_Price = :totalPrice
            -- Removed Product_Status update from this script
        WHERE ReadyMadeOrder_ID = :readyMadeOrderID
    ";
    $stmt = $pdo->prepare($updateOrderQuery);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':totalPrice', $calculatedTotalPrice, PDO::PARAM_STR); // Use calculated price
    $stmt->bindParam(':readyMadeOrderID', $readyMadeOrderID, PDO::PARAM_INT);
    $stmt->execute();

    // --- REMOVED Update tbl_progress ---
    // The logic to update tbl_progress based on Product_Status is removed
    // as Product_Status is no longer submitted by this form.
    // Progress updates are handled by the dedicated progress update mechanism.

    // --- REMOVED Handle Purchase History ---
    // The logic to create/update tbl_purchase_history based on Product_Status = 100
    // is removed. This should be triggered by the progress update mechanism when
    // the order reaches the final 'completed' status.

    // --- REMOVED Handle Progress Deletion ---
    // The logic to delete from tbl_progress when Product_Status = 100 is removed.
    // This should also be handled by the progress update mechanism upon completion.


    // --- Redirect back to the list ---
    // Redirect after successful update
    header("Location: read-all-readymade-form.php");
    exit();

} catch (PDOException $e) {
    // Log error properly in a real application
    error_log("Database error in update-readymade-rec.php: " . $e->getMessage()); // Example logging
    die("Database error occurred. Please contact support."); // User-friendly message
} catch (Exception $e) {
    error_log("General error in update-readymade-rec.php: " . $e->getMessage()); // Example logging
    die("An unexpected error occurred. Please try again later."); // User-friendly message
}
?>
