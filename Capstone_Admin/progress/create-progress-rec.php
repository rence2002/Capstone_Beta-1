<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Ensure POST data is received
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method.";
    exit();
}

// --- Retrieve and Validate Form Data ---
$User_ID = $_POST['User_ID'] ?? null;
$Product_ID = $_POST['Product_ID'] ?? null;
$Product_Name = $_POST['Product_Name'] ?? null; // From hidden input
$Order_Type = $_POST['Order_Type'] ?? null;
$Product_Status = $_POST['Product_Status'] ?? null; // Should be '0' from hidden input
$Quantity = $_POST['Quantity'] ?? null;
$Total_Price = $_POST['Total_Price'] ?? null;

// Basic Validation
$errors = [];
if (empty($User_ID)) {
    $errors[] = "User ID is required.";
}
if (empty($Product_ID) || !is_numeric($Product_ID)) {
    $errors[] = "Valid Product ID is required.";
}
if (empty($Product_Name)) {
    // This might happen if JS fails or product wasn't selected properly
    $errors[] = "Product Name is required (should be auto-populated). Please re-select the product.";
}
if (empty($Order_Type)) {
    $errors[] = "Order Type is required.";
}
if ($Product_Status === null || !is_numeric($Product_Status)) { // Check explicitly for '0'
    $errors[] = "Valid Product Status is required (should be 0).";
}
if (empty($Quantity) || !is_numeric($Quantity) || $Quantity < 1) {
    $errors[] = "Valid Quantity (at least 1) is required.";
}
if ($Total_Price === null || !is_numeric($Total_Price) || $Total_Price < 0) {
    $errors[] = "Valid Total Price is required (should be calculated).";
}

// If validation errors exist, display them (or redirect back with errors)
if (!empty($errors)) {
    // Simple error display for now
    echo "Error creating progress record:<br>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo '<a href="create-progress-form.php">Go Back</a>';
    exit();
}


// --- Database Insertion Logic ---
try {
    // Prepare the INSERT statement for tbl_progress
    $sql = "INSERT INTO tbl_progress (
                User_ID,
                Product_ID,
                Product_Name,
                Order_Type,
                Product_Status,
                Quantity,
                Total_Price
                -- Date_Added and LastUpdate use defaults/triggers
            ) VALUES (
                :User_ID,
                :Product_ID,
                :Product_Name,
                :Order_Type,
                :Product_Status,
                :Quantity,
                :Total_Price
            )";

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':User_ID', $User_ID, PDO::PARAM_STR);
    $stmt->bindParam(':Product_ID', $Product_ID, PDO::PARAM_INT);
    $stmt->bindParam(':Product_Name', $Product_Name, PDO::PARAM_STR);
    $stmt->bindParam(':Order_Type', $Order_Type, PDO::PARAM_STR);
    $stmt->bindParam(':Product_Status', $Product_Status, PDO::PARAM_INT);
    $stmt->bindParam(':Quantity', $Quantity, PDO::PARAM_INT);
    $stmt->bindParam(':Total_Price', $Total_Price, PDO::PARAM_STR); // Bind as string for decimal

    // Execute the query
    if ($stmt->execute()) {
        // Success: Redirect to the list view
        header("Location: read-all-progress-form.php?success=created");
        exit();
    } else {
        // Execution failed
        $errorInfo = $stmt->errorInfo();
        throw new PDOException("Failed to execute insert statement: " . ($errorInfo[2] ?? 'Unknown error'));
    }

} catch (PDOException $e) {
    // Log the detailed error
    error_log("Database Error in create-progress-rec.php: " . $e->getMessage());
    // Display a user-friendly error message
    echo "Database Error: Failed to create progress record. Please check logs or contact support.";
    // echo "Error: " . $e->getMessage(); // Show detailed error during development
    echo '<br><a href="create-progress-form.php">Go Back</a>';
} catch (Exception $e) {
    // Log other errors
    error_log("General Error in create-progress-rec.php: " . $e->getMessage());
    echo "An unexpected error occurred: " . htmlspecialchars($e->getMessage());
    echo '<br><a href="create-progress-form.php">Go Back</a>';
}

?>
