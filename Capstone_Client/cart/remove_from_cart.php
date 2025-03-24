<?php
session_start();
include("../config/database.php");

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$cart_id = $data['cart_id'] ?? null;

if (!$cart_id) {
    echo json_encode(['success' => false, 'message' => 'Cart ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM tbl_cart WHERE Cart_ID = :cart_id AND User_ID = :user_id");
    $result = $stmt->execute([
        'cart_id' => $cart_id,
        'user_id' => $_SESSION['user_id']
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}