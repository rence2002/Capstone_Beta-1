<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include the database connection
include("../config/database.php");

function saveToCart($pdo, $userId, $productId, $quantity, $price, $orderType) {
    try {
        // Log input data for debugging
        error_log("Saving to cart: User ID: $userId, Product ID: $productId, Quantity: $quantity, Price: $price, Order Type: $orderType");

        // Ensure Order_Type is not empty
        if (empty($orderType)) {
            error_log("Error: Order_Type is empty");
            return ['success' => false, 'message' => 'Order_Type is required'];
        }

        // Check if the product already exists in the cart for the user
        $stmt = $pdo->prepare("SELECT * FROM tbl_cart WHERE User_ID = :userId AND Product_ID = :productId AND Order_Type = :orderType");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
        $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':orderType', $orderType, PDO::PARAM_STR);
        $stmt->execute();
        $existingCartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingCartItem) {
            // Update the existing cart item by incrementing the quantity
            $newQuantity = $existingCartItem['Quantity'] + $quantity;
            $newTotalPrice = $newQuantity * $price;

            $updateStmt = $pdo->prepare("UPDATE tbl_cart SET Quantity = :newQuantity, Total_Price = :newTotalPrice WHERE Cart_ID = :cartId");
            $updateStmt->bindParam(':newQuantity', $newQuantity, PDO::PARAM_INT);
            $updateStmt->bindParam(':newTotalPrice', $newTotalPrice, PDO::PARAM_STR);
            $updateStmt->bindParam(':cartId', $existingCartItem['Cart_ID'], PDO::PARAM_INT);
            $updateStmt->execute();

            error_log("Cart updated successfully for User ID: $userId, Product ID: $productId");
            return ['success' => true];
        } else {
            // Insert a new cart item
            $totalPrice = $quantity * $price;
            $insertStmt = $pdo->prepare("INSERT INTO tbl_cart (User_ID, Product_ID, Quantity, Price, Total_Price, Order_Type) VALUES (:userId, :productId, :quantity, :price, :totalPrice, :orderType)");
            $insertStmt->bindParam(':userId', $userId, PDO::PARAM_STR);
            $insertStmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            $insertStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $insertStmt->bindParam(':price', $price, PDO::PARAM_STR);
            $insertStmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);
            $insertStmt->bindParam(':orderType', $orderType, PDO::PARAM_STR);
            $insertStmt->execute();

            error_log("Cart saved successfully for User ID: $userId, Product ID: $productId");
            return ['success' => true];
        }
    } catch (PDOException $e) {
        error_log("Error in saveToCart: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION["user_id"];
    $productId = $_POST['productId'];
    $quantity = $_POST['quantity'];
    $orderType = $_POST['orderType'] ?? 'ready_made'; // Default to 'ready_made'

    // Debugging statements
    error_log("Received AJAX request");
    error_log("User ID: $userId");
    error_log("Product ID: $productId");
    error_log("Quantity: $quantity");
    error_log("Order Type: $orderType");

    // Fetch product details
    $stmt = $pdo->prepare("SELECT Price, Stock FROM tbl_prod_info WHERE Product_ID = :productId");
    $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $price = $product['Price'];
        $stock = $product['Stock'];
        $totalPrice = $quantity * $price;

        // Perform stock check for ready-made orders
        if ($orderType === 'ready_made' && $quantity > $stock) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
            exit;
        }

        // Save the order request or cart entry based on the order type
        if ($orderType === 'pre_order' || $orderType === 'ready_made') {
            $response = saveOrderRequest($pdo, $userId, $productId, $quantity, $orderType, $totalPrice);

            // Deduct stock only for ready-made orders
            if ($orderType === 'ready_made') {
                updateStock($pdo, $productId, $quantity);
            }
        } elseif ($orderType === 'cart') {
            $response = saveToCart($pdo, $userId, $productId, $quantity, $price, $orderType);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid order type']);
            exit;
        }

        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
}
?>