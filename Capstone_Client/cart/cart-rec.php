<?php
session_start();
include("../config/database.php");

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Get cart items for the user
    $stmt = $pdo->prepare("
        SELECT c.*, p.Product_ID, p.product_type
        FROM tbl_cart c
        JOIN tbl_prod_info p ON c.Product_ID = p.Product_ID
        WHERE c.User_ID = :user_id
    ");
    $stmt->execute(['user_id' => $_SESSION["user_id"]]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if the cart is empty
    if (empty($cartItems)) {
        throw new Exception('Cart is empty');
    }

    // Insert each cart item into order requests
    foreach ($cartItems as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO tbl_order_request (
                User_ID, 
                Product_ID, 
                Quantity, 
                Order_Type,
                Total_Price
            ) VALUES (
                :user_id,
                :product_id,
                :quantity,
                :order_type,
                :total_price
            )
        ");

        $stmt->execute([
            'user_id' => $_SESSION["user_id"],
            'product_id' => $item['Product_ID'],
            'quantity' => $item['Quantity'],
            'order_type' => $item['product_type'] === 'custom' ? 'custom' : 'ready_made',
            'total_price' => $item['Total_Price']
        ]);
    }

    // Clear the user's cart
    $stmt = $pdo->prepare("DELETE FROM tbl_cart WHERE User_ID = :user_id");
    $stmt->execute(['user_id' => $_SESSION["user_id"]]);

    // Commit transaction
    $pdo->commit();

    // Return a success response
    echo json_encode(['success' => true, 'message' => 'Orders placed successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();

    // Log the error for debugging
    error_log("Error processing order: " . $e->getMessage());

    // Return an error response
    echo json_encode([
        'success' => false, 
        'message' => 'Error processing order: ' . $e->getMessage()
    ]);
}
?>
