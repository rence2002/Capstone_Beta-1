<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include the database connection
include("../config/database.php");

// Get the user ID from the session
$userId = $_SESSION["user_id"];

function saveToCart($pdo, $userId, $productId, $quantity, $price, $orderType) {
    try {
        // Log input data for debugging
        error_log("Saving to cart: User ID: $userId, Product ID: $productId, Quantity: $quantity, Price: $price, Order Type: $orderType");

        // Ensure Order_Type is not empty
        $orderType = trim($orderType);
        if (empty($orderType) || !in_array($orderType, ['pre_order', 'ready_made', 'cart'])) {
            error_log("Error: Invalid or empty Order_Type: $orderType");
            return ['success' => false, 'message' => 'Invalid or empty Order_Type'];
        }

        // Map 'cart' to 'ready_made' for database compatibility
        if ($orderType === 'cart') {
            $orderType = 'ready_made';
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
            return ['success' => true, 'message' => 'Cart updated successfully'];
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
            return ['success' => true, 'message' => 'Cart saved successfully'];
        }
    } catch (PDOException $e) {
        error_log("Error in saveToCart: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error saving to cart'];
    }
}

function saveOrderRequest($pdo, $userId, $productId, $quantity, $orderType, $totalPrice) {
    try {
        // Log input data for debugging
        error_log("Saving order request: User ID: $userId, Product ID: $productId, Quantity: $quantity, Order Type: $orderType, Total Price: $totalPrice");

        // Insert into tbl_order_request
        $stmt = $pdo->prepare("INSERT INTO tbl_order_request (User_ID, Product_ID, Quantity, Order_Type, Total_Price) VALUES (:userId, :productId, :quantity, :orderType, :totalPrice)");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
        $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':orderType', $orderType, PDO::PARAM_STR);
        $stmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);
        $stmt->execute();

        error_log("Order request saved successfully for User ID: $userId, Product ID: $productId");
        return ['success' => true, 'message' => 'Order request saved successfully'];
    } catch (PDOException $e) {
        error_log("Error in saveOrderRequest: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error saving order request'];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data from the request body
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    // Check if JSON decoding was successful
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        // Handle JSON decoding error
        error_log('JSON decoding error: ' . json_last_error_msg());
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    // Extract data from the JSON object
    $productId = isset($data['productId']) ? intval($data['productId']) : 0;
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;
    $orderType = isset($data['orderType']) ? trim($data['orderType']) : '';

    // Validate data
    if ($productId <= 0 || $quantity <= 0 || empty($orderType)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data received']);
        exit;
    }

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

        if ($orderType === 'ready_made' || $orderType === 'pre_order') {
            // Save to tbl_order_request for pre-orders and ready-made orders
            $response = saveOrderRequest($pdo, $userId, $productId, $quantity, $orderType, $totalPrice);
            // Deduct stock only for ready-made orders
            if ($orderType === 'ready_made') {
                //updateStock($pdo, $productId, $quantity); // You need to define this function
            }
        } elseif ($orderType === 'cart') {
            // Save to tbl_cart for add-to-cart actions
            $response = saveToCart($pdo, $userId, $productId, $quantity, $price, $orderType);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid order type']); // Added message
            exit;
        }

        // Ensure the response is sent back to the client
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
}
?>