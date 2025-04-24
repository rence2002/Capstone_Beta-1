<?php
session_start(); // Start the session

// Include the database connection
include("../config/database.php");

// Check if the admin's ID is stored in the session after login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Ensure POST method is used
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// --- GET USER INPUT FROM WEB FORM (Simplified Form) ---
// Expecting Progress_ID, Quantity, Total_Price
$progressID = $_POST['Progress_ID'] ?? null;
$quantity = $_POST['Quantity'] ?? null;
$totalPrice = $_POST['Total_Price'] ?? null;
// Note: Product_Status might be passed as hidden, but we won't update it here.

// --- VALIDATE REQUIRED FIELDS ---
$errors = [];
if (empty($progressID) || !is_numeric($progressID)) {
    $errors[] = "Valid Progress ID is required.";
}
if ($quantity === null || !is_numeric($quantity) || $quantity < 1) {
    $errors[] = "Valid Quantity (at least 1) is required.";
}
if ($totalPrice === null || !is_numeric($totalPrice) || $totalPrice < 0) {
    $errors[] = "Valid Total Price is required.";
}

// If validation errors exist, display them
if (!empty($errors)) {
    echo "Error updating pre-order details:<br>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    // Link back to the specific update form
    echo '<a href="update-preorder-prod-form.php?id=' . urlencode($progressID ?? '') . '">Go Back</a>';
    exit();
}

// --- UPDATE tbl_progress (Only Quantity and Total Price) ---
// Removed updates for Product_Status, Stop_Reason, Tracking_Number, Payment_Status, Progress_Pics
// Removed history transfer logic as Product_Status is not updated here.
$query = "UPDATE tbl_progress SET
            Quantity = :quantity,
            Total_Price = :totalPrice
            -- LastUpdate column should update automatically via DB trigger/default
          WHERE Progress_ID = :progressID
            AND Order_Type = 'pre_order'"; // Extra check for safety

try {
    $stmt = $pdo->prepare($query);

    // BIND PARAMETER VALUES
    $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
    $stmt->bindParam(":totalPrice", $totalPrice, PDO::PARAM_STR); // Bind as string for decimal
    $stmt->bindParam(":progressID", $progressID, PDO::PARAM_INT);

    // EXECUTE STATEMENT
    if ($stmt->execute()) {
        // Check if any row was actually updated
        if ($stmt->rowCount() > 0) {
            // Redirect to the pre-order list page on successful update
            header("Location: read-all-preorder-prod-form.php?success=details_updated&id=" . $progressID);
            exit;
        } else {
            // No rows updated - maybe data was unchanged or ID/Order_Type didn't match
            header("Location: read-all-preorder-prod-form.php?warning=nochange&id=" . $progressID);
            exit();
        }
    } else {
        // Execution failed
        $errorInfo = $stmt->errorInfo();
        throw new PDOException("Failed to execute update statement: " . ($errorInfo[2] ?? 'Unknown error'));
    }
} catch (PDOException $e) {
    // Catch and display/log any database-related errors
    error_log("Database Error updating pre-order details (Progress ID: $progressID): " . $e->getMessage());
    echo "Database Error: Could not update the pre-order details. Please check logs or contact support.";
    // echo "Error: " . $e->getMessage(); // Development only
    echo '<br><a href="update-preorder-prod-form.php?id=' . urlencode($progressID ?? '') . '">Go Back</a>';
} catch (Exception $e) {
    // Catch other potential errors
    error_log("General Error updating pre-order details (Progress ID: $progressID): " . $e->getMessage());
    echo "An unexpected error occurred: " . htmlspecialchars($e->getMessage());
    echo '<br><a href="update-preorder-prod-form.php?id=' . urlencode($progressID ?? '') . '">Go Back</a>';
}
?>
