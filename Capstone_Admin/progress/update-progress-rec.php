<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request.";
    exit;
}

// Collect and validate required fields
if (!isset($_POST['Progress_ID']) || !isset($_POST['Order_Type'])) {
    echo "Progress ID and Order Type are required.";
    exit;
}

$progressID = $_POST['Progress_ID'];
$orderType = $_POST['Order_Type'];

// Collect optional fields
$orderStatus = $_POST['Order_Status'] ?? null;
$productStatus = $_POST['Product_Status'] ?? null;
$totalPrice = $_POST['Total_Price'] ?? null;
$stopReason = $_POST['Stop_Reason'] ?? null;
$productName = $_POST['Product_Name'] ?? '';
$userID = $_POST['User_ID'] ?? '';

// Handle file uploads
$uploadDir = '../uploads/progress_pics/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$progressPics = [];
foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $percentage) {
    $fileKey = "Progress_Pic_$percentage";
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$fileKey]['tmp_name'];
        $fileName = basename($_FILES[$fileKey]['name']);
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniqueIdentifier = time() . '_' . bin2hex(random_bytes(5));
        $newFileName = strtolower($orderType) . '_' . str_replace(' ', '_', $productName) . '_' . $percentage . '_' . $uniqueIdentifier . '.' . $fileExtension;
        $filePath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $filePath)) {
            $progressPics[$fileKey] = $filePath;
        } else {
            echo "Failed to upload file: $fileName";
        }
    }
}

// Prepare update query
$query = "";
$params = [
    ':progress_id' => $progressID,
];

switch ($orderType) {
    case 'custom':
        $query = "UPDATE tbl_customizations SET Last_Update = NOW()";
        break;
    case 'pre_order':
        $query = "UPDATE tbl_preorder SET ";
        break;
    case 'ready_made':
        $query = "UPDATE tbl_ready_made_orders SET ";
        break;
    default:
        echo "Invalid order type.";
        exit;
}

// Add optional fields to the query
$fields = [];
if ($orderStatus !== null) {
    $fields[] = "Order_Status = :order_status";
    $params[':order_status'] = $orderStatus;
}
if ($productStatus !== null) {
    $fields[] = "Product_Status = :product_status";
    $params[':product_status'] = $productStatus;
}
if ($totalPrice !== null) {
    $fields[] = "Total_Price = :total_price";
    $params[':total_price'] = $totalPrice;
}
if ($stopReason !== null) {
    $fields[] = "Stop_Reason = :stop_reason";
    $params[':stop_reason'] = $stopReason;
}

// Append progress picture updates
foreach ($progressPics as $key => $path) {
    $fields[] = "$key = :$key";
    $params[":$key"] = $path;
}

// Combine fields into the query
if (!empty($fields)) {
    $query .= implode(", ", $fields);
} else {
    $query .= "Last_Update = NOW()"; // Default update if no fields are provided
}

// Add WHERE clause
$query .= " WHERE " . ($orderType === 'custom' ? "Customization_ID" : ($orderType === 'pre_order' ? "Preorder_ID" : "ReadyMadeOrder_ID")) . " = :progress_id";

// Execute the query
try {
    $stmt = $pdo->prepare($query);
    if ($stmt->execute($params)) {
        // Insert into tbl_purchase_history if order is completed
        if ($orderStatus == 100 && $productStatus == 100) {
            $insertHistoryQuery = "
                INSERT INTO tbl_purchase_history (User_ID, Product_ID, Product_Name, Quantity, Total_Price, Order_Type, Order_Status, Product_Status)
                VALUES (:user_id, :product_id, :product_name, 1, :total_price, :order_type, 100, 100)
            ";
            $insertHistoryStmt = $pdo->prepare($insertHistoryQuery);
            $insertHistoryStmt->execute([
                ':user_id' => $userID,
                ':product_id' => isset($params[':product_id']) ? $params[':product_id'] : null,
                ':product_name' => $productName,
                ':total_price' => $totalPrice,
                ':order_type' => $orderType
            ]);
        }

        header("Location: read-all-progress-form.php?message=update_success");
        exit;
    } else {
        echo "Failed to update the record.";
        print_r($stmt->errorInfo());
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>