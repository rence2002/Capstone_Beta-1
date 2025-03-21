<?php
session_start();
include("../config/database.php");

// Sanitize inputs using same pattern as admin
$userID = $_SESSION["user_id"];
$furnitureType = $_POST['furniture'] ?? null;
$furnitureInfo = $_POST['furniture-info'] ?? null;
$standardSize = $_POST['sizes'] ?? null;
$desiredSize = $_POST['sizes-info'] ?? null; // Now matches input name
$color = $_POST['color'] ?? null;
$colorInfo = $_POST['color-info'] ?? null;
// Repeat for all fields...

// Use admin's file handling pattern
function handleFileUpload($fileKey, $uploadDir = '../uploads/custom/') {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES[$fileKey]['name']);
        $targetFilePath = $uploadDir . $filename;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetFilePath)) {
            return $targetFilePath;
        }
    }
    return null;
}

// Process files like admin
$colorImage = handleFileUpload('color-file-upload');
$textureImage = handleFileUpload('texture-file-upload');
// Repeat for all file inputs...

// Insert using admin's parameter binding pattern
$stmt = $pdo->prepare("
    INSERT INTO tbl_customizations_temp (
        User_ID, Furniture_Type, Furniture_Type_Additional_Info, 
        Standard_Size, Desired_Size, Color, Color_Image_URL, 
        Color_Additional_Info, Texture, Texture_Image_URL, 
        Texture_Additional_Info
    ) VALUES (
        :userID, :furnitureType, :furnitureInfo, 
        :standardSize, :desiredSize, :color, :colorImage, 
        :colorInfo, :texture, :textureImage, 
        :textureInfo
    )
");

$stmt->execute([
    ':userID' => $userID,
    ':furnitureType' => $furnitureType,
    ':furnitureInfo' => $furnitureInfo,
    ':standardSize' => $standardSize,
    ':desiredSize' => $desiredSize,
    ':color' => $color,
    ':colorImage' => $colorImage,
    ':colorInfo' => $colorInfo,
    ':texture' => $texture,
    ':textureImage' => $textureImage,
    ':textureInfo' => $textureInfo
]);

// Get customization ID
$customizationID = $pdo->lastInsertId();

// Insert into order request like admin
$orderStmt = $pdo->prepare("
    INSERT INTO tbl_order_request (
        User_ID, Customization_ID, Quantity, Order_Type, Total_Price
    ) VALUES (
        :userID, :customizationID, 1, 'custom', 0.00
    )
");
$orderStmt->execute([':userID' => $userID, ':customizationID' => $customizationID]);

echo json_encode(['success' => true]);
?>