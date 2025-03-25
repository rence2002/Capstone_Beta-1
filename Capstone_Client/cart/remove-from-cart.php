<?php
session_start();
require __DIR__ . '/../config/database.php';

$response = ['success' => false, 'message' => ''];

try {
    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $cartId = $_POST['cart_id'] ?? null;
    if (!$cartId) {
        throw new Exception('Invalid cart ID');
    }

    // Verify ownership
    $stmt = $pdo->prepare("SELECT * FROM tbl_cart WHERE Cart_ID = ?");
    $stmt->execute([$cartId]);
    $item = $stmt->fetch();

    if (!$item || $item['User_ID'] !== $_SESSION['user_id']) {
        throw new Exception('Unauthorized access');
    }

    // Delete item
    $deleteStmt = $pdo->prepare("DELETE FROM tbl_cart WHERE Cart_ID = ?");
    $deleteStmt->execute([$cartId]);

    $response['success'] = true;
    $response['message'] = 'Item removed successfully';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    // Send proper JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>