<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

include("../config/database.php");

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['product_id']) && isset($data['quantity'])) {
    $productId = $data['product_id'];
    $quantity = $data['quantity'];
    $userId = $_SESSION["user_id"];

    // Check if the product is already in the cart
    $stmt = $pdo->prepare("SELECT * FROM tbl_cart WHERE User_ID = :user_id AND Product_ID = :product_id");
    $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        // Update the quantity if the product is already in the cart
        $stmt = $pdo->prepare("UPDATE tbl_cart SET Quantity = Quantity + :quantity WHERE User_ID = :user_id AND Product_ID = :product_id");
        $stmt->execute(['quantity' => $quantity, 'user_id' => $userId, 'product_id' => $productId]);
    } else {
        // Insert a new item into the cart
        $stmt = $pdo->prepare("INSERT INTO tbl_cart (User_ID, Product_ID, Quantity, Date_Added) VALUES (:user_id, :product_id, :quantity, NOW())");
        $stmt->execute(['user_id' => $userId, 'product_id' => $productId, 'quantity' => $quantity]);
    }

    echo json_encode(['success' => true, 'message' => 'Item added to cart']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>