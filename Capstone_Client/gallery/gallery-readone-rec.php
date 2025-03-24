<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include the database connection
include("../config/database.php");

// Function to save to cart
function saveToCart($pdo, $userId, $productId, $quantity, $price, $orderType) {
    try {
        $totalPrice = $quantity * $price;
        $stmt = $pdo->prepare("INSERT INTO tbl_cart (User_ID, Product_ID, Quantity, Price, Total_Price, Order_Type) VALUES (:userId, :productId, :quantity, :price, :totalPrice, :orderType)");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
        $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);
        $stmt->bindParam(':orderType', $orderType, PDO::PARAM_STR);
        $stmt->execute();
        error_log("Cart saved successfully for User ID: $userId, Product ID: $productId");
        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Error in saveToCart: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Function to save order request
function saveOrderRequest($pdo, $userId, $productId, $quantity, $orderType, $totalPrice) {
    try {
        $stmt = $pdo->prepare("INSERT INTO tbl_order_request (User_ID, Product_ID, Quantity, Order_Type, Total_Price) VALUES (:userId, :productId, :quantity, :orderType, :totalPrice)");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
        $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':orderType', $orderType, PDO::PARAM_STR);
        $stmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);
        $stmt->execute();
        error_log("Order request saved successfully for User ID: $userId, Product ID: $productId");
        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Error in saveOrderRequest: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Function to update stock
function updateStock($pdo, $productId, $quantity) {
    try {
        $stmt = $pdo->prepare("UPDATE tbl_prod_info SET Stock = Stock - :quantity WHERE Product_ID = :productId");
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
        $stmt->execute();
        error_log("Stock updated successfully for Product ID: $productId");
        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Error in updateStock: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION["user_id"];
    $productId = $_POST['productId'];
    $quantity = $_POST['quantity'];
    $orderType = $_POST['orderType'] ?? 'ready_made'; // Default to 'ready_made' if not provided

    // Debugging statements
    error_log("Received AJAX request");
    error_log("User ID: $userId");
    error_log("Product ID: $productId");
    error_log("Quantity: $quantity");
    error_log("Order Type: $orderType");

    // Fetch product price from the database
    $stmt = $pdo->prepare("SELECT Price, Stock FROM tbl_prod_info WHERE Product_ID = :productId");
    $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $price = $product['Price'];
        $stock = $product['Stock'];
        $totalPrice = $quantity * $price;

        if ($quantity > $stock) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
            exit;
        }

        if ($orderType === 'pre_order' || $orderType === 'ready_made') {
            $response = saveOrderRequest($pdo, $userId, $productId, $quantity, $orderType, $totalPrice);
        } else {
            $response = saveToCart($pdo, $userId, $productId, $quantity, $price, $orderType);
        }

        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
}
?>