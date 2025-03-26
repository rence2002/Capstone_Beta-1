<?php
session_start();
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartId = $_POST['cart_id'] ?? null;
    $quantity = (int)($_POST['quantity'] ?? 0);

    if (!$cartId || $quantity < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit;
    }

    // Verify cart ownership
    $stmt = $pdo->prepare("SELECT * FROM tbl_cart WHERE Cart_ID = ?");
    $stmt->execute([$cartId]);
    $cartItem = $stmt->fetch();

    if (!$cartItem || $cartItem['User_ID'] !== $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    // Update quantity and total
    $totalPrice = $cartItem['Price'] * $quantity;
    $updateStmt = $pdo->prepare("
        UPDATE tbl_cart 
        SET Quantity = ?, Total_Price = ? 
        WHERE Cart_ID = ?
    ");
    $updateStmt->execute([$quantity, $totalPrice, $cartId]);

    echo json_encode(['success' => true]);
    exit;
}
?>