<?php
session_start();
include("../config/database.php");

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $pdo->prepare("
        UPDATE tbl_cart 
        SET Quantity = :quantity,
            Total_Price = Price * :quantity
        WHERE Cart_ID = :cart_id
    ");
    
    $result = $stmt->execute([
        'quantity' => $data['quantity'],
        'cart_id' => $data['cart_id']
    ]);

    if ($result) {
        // Get the updated total price
        $priceStmt = $pdo->prepare("
            SELECT Total_Price 
            FROM tbl_cart 
            WHERE Cart_ID = :cart_id
        ");
        $priceStmt->execute(['cart_id' => $data['cart_id']]);
        $totalPrice = $priceStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'total_price' => $totalPrice
        ]);
    } else {
        throw new Exception('Failed to update cart');
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}