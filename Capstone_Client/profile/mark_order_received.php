<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include database connection
include("../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["progress_id"])) {
    $progressId = $_POST["progress_id"];
    $userId = $_SESSION["user_id"];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Get progress data
        $stmt = $pdo->prepare("
            SELECT * FROM tbl_progress 
            WHERE Progress_ID = :progress_id 
            AND User_ID = :user_id 
            AND Product_Status = 100 
            AND Order_Received = 0
        ");
        $stmt->execute([
            'progress_id' => $progressId,
            'user_id' => $userId
        ]);
        $progressData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($progressData) {
            // Insert into purchase history
            $stmt = $pdo->prepare("
                INSERT INTO tbl_purchase_history (
                    User_ID, Product_ID, Product_Name, Quantity, 
                    Total_Price, Order_Type, Purchase_Date, Product_Status
                ) VALUES (
                    :user_id, :product_id, :product_name, :quantity,
                    :total_price, :order_type, NOW(), :product_status
                )
            ");
            $stmt->execute([
                'user_id' => $userId,
                'product_id' => $progressData['Product_ID'],
                'product_name' => $progressData['Product_Name'],
                'quantity' => $progressData['Quantity'],
                'total_price' => $progressData['Total_Price'],
                'order_type' => $progressData['Order_Type'],
                'product_status' => $progressData['Product_Status']
            ]);

            // Delete from progress table
            $stmt = $pdo->prepare("
                DELETE FROM tbl_progress 
                WHERE Progress_ID = :progress_id
            ");
            $stmt->execute(['progress_id' => $progressId]);

            // Commit transaction
            $pdo->commit();
            
            // Redirect back to profile with success message
            $_SESSION['success_message'] = "Order marked as received and moved to purchase history!";
        } else {
            throw new Exception("Invalid order or order already received");
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
}

// Redirect back to profile page
header("location: profile.php");
exit;
?> 