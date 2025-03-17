<?php
session_start();
include '../config/database.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: /Capstone/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input
    $userId = isset($_POST['txtUserId']) ? htmlspecialchars(trim($_POST['txtUserId'])) : null;
    $productId = isset($_POST['txtProductId']) ? htmlspecialchars(trim($_POST['txtProductId'])) : null;
    $quantity = isset($_POST['txtQuantity']) ? intval($_POST['txtQuantity']) : 0;
    $orderType = isset($_POST['txtOrderType']) ? htmlspecialchars(trim($_POST['txtOrderType'])) : null;
    $dateAdded = date('Y-m-d H:i:s');

    // Validate inputs
    if (empty($userId) || empty($productId) || $quantity <= 0 || empty($orderType)) {
        echo "Please fill in all fields with valid values.";
        exit();
    }

    if (!in_array($orderType, ['pre_order', 'ready_made'], true)) {
        echo "Invalid order type.";
        exit();
    }

    // Verify User_ID existence
    $userCheck = $pdo->prepare("SELECT 1 FROM tbl_user_info WHERE User_ID = :user_id");
    $userCheck->bindParam(':user_id', $userId, PDO::PARAM_STR);
    $userCheck->execute();
    if (!$userCheck->fetch()) {
        echo "Error: User not found.";
        exit();
    }

    // Fetch Product details
    $productCheck = $pdo->prepare("SELECT Product_Name, Price FROM tbl_prod_info WHERE Product_ID = :product_id");
    $productCheck->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $productCheck->execute();
    $product = $productCheck->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Error: Product not found.";
        exit();
    }

    $price = (float)$product['Price'];

    // Calculate Total Price
    $totalPrice = $quantity * $price;

    try {
        // Insert data into tbl_cart
        $cartQuery = "INSERT INTO tbl_cart (User_ID, Product_ID, Quantity, Price, Total_Price, Order_Type, Date_Added) 
                      VALUES (:user_id, :product_id, :quantity, :price, :total_price, :order_type, :date_added)";
        $cartStmt = $pdo->prepare($cartQuery);

        // Bind parameters for cart
        $cartStmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
        $cartStmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $cartStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $cartStmt->bindParam(':price', $price, PDO::PARAM_STR);
        $cartStmt->bindParam(':total_price', $totalPrice, PDO::PARAM_STR);
        $cartStmt->bindParam(':order_type', $orderType, PDO::PARAM_STR);
        $cartStmt->bindParam(':date_added', $dateAdded, PDO::PARAM_STR);

        // Execute cart insert
        $cartStmt->execute();

        // Redirect to cart page
        header("Location: read-all-cart-form.php");
        exit();
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
}
?>
