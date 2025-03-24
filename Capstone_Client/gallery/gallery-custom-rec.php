<?php
session_start();
include("../config/database.php");

// Validate session
if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Validation rules
$requiredFields = [
    'furniture' => 'Furniture Type',
    'sizes' => 'Size'
];

foreach ($requiredFields as $field => $label) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => "$label is required"]));
    }
}

// File validation
$customFields = ['color', 'texture', 'wood', 'foam', 'cover', 'design', 'tiles', 'metal'];
foreach ($customFields as $field) {
    if (($_POST[$field] ?? null) === 'custom') {
        $fileKey = 'file' . ucfirst($field) . 'Image';
        if (empty($_FILES[$fileKey]['name'])) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => "Image required for custom $field"]));
        }
    }
}

// File upload handler
function handleFileUpload($fileKey, $uploadDir = '../uploads/custom/') {
    if (empty($_FILES[$fileKey]['name'])) return null;
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($_FILES[$fileKey]['type'], $allowedTypes)) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'Invalid file type']));
    }
    
    $filename = uniqid() . '_' . basename($_FILES[$fileKey]['name']);
    $targetFilePath = $uploadDir . $filename;
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Directory creation failed']));
        }
    }
    
    if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetFilePath)) {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'File upload failed']));
    }
    
    return $targetFilePath;
}

// Collect form data
$userID = $_SESSION["user_id"];
$furnitureType = $_POST['furniture'] ?? null;
$furnitureInfo = $_POST['furniture_info'] ?? null;
$standardSize = $_POST['sizes'] ?? null;
$desiredSize = $_POST['sizes_info'] ?? null;

// Process all file uploads
$colorImage = handleFileUpload('fileColorImage');
$textureImage = handleFileUpload('fileTextureImage');
$woodImage = handleFileUpload('fileWoodImage');
$foamImage = handleFileUpload('fileFoamImage');
$coverImage = handleFileUpload('fileCoverImage');
$designImage = handleFileUpload('fileDesignImage');
$tilesImage = handleFileUpload('fileTileImage');
$metalImage = handleFileUpload('fileMetalImage');

// Database transaction
try {
    $pdo->beginTransaction();
    
    // Insert into customizations_temp
    $stmt = $pdo->prepare("
        INSERT INTO tbl_customizations_temp (
            User_ID, Furniture_Type, Furniture_Type_Additional_Info,
            Standard_Size, Desired_Size, Color, Color_Image_URL,
            Color_Additional_Info, Texture, Texture_Image_URL,
            Texture_Additional_Info, Wood_Type, Wood_Image_URL,
            Wood_Additional_Info, Foam_Type, Foam_Image_URL,
            Foam_Additional_Info, Cover_Type, Cover_Image_URL,
            Cover_Additional_Info, Design, Design_Image_URL,
            Design_Additional_Info, Tile_Type, Tile_Image_URL,
            Tile_Additional_Info, Metal_Type, Metal_Image_URL,
            Metal_Additional_Info
        ) VALUES (
            :userID, :furnitureType, :furnitureInfo,
            :standardSize, :desiredSize, :color, :colorImage,
            :colorInfo, :texture, :textureImage,
            :textureInfo, :wood, :woodImage,
            :woodInfo, :foam, :foamImage,
            :foamInfo, :cover, :coverImage,
            :coverInfo, :design, :designImage,
            :designInfo, :tiles, :tilesImage,
            :tilesInfo, :metal, :metalImage,
            :metalInfo
        )
    ");
    
    $stmt->execute([
        ':userID' => $userID,
        ':furnitureType' => $furnitureType,
        ':furnitureInfo' => $furnitureInfo,
        ':standardSize' => $standardSize,
        ':desiredSize' => $desiredSize,
        ':color' => $_POST['color'] ?? null,
        ':colorImage' => $colorImage,
        ':colorInfo' => $_POST['color_info'] ?? null,
        ':texture' => $_POST['texture'] ?? null,
        ':textureImage' => $textureImage,
        ':textureInfo' => $_POST['texture_info'] ?? null,
        ':wood' => $_POST['wood'] ?? null,
        ':woodImage' => $woodImage,
        ':woodInfo' => $_POST['wood_info'] ?? null,
        ':foam' => $_POST['foam'] ?? null,
        ':foamImage' => $foamImage,
        ':foamInfo' => $_POST['foam_info'] ?? null,
        ':cover' => $_POST['cover'] ?? null,
        ':coverImage' => $coverImage,
        ':coverInfo' => $_POST['cover_info'] ?? null,
        ':design' => $_POST['design'] ?? null,
        ':designImage' => $designImage,
        ':designInfo' => $_POST['design_info'] ?? null,
        ':tiles' => $_POST['tiles'] ?? null,
        ':tilesImage' => $tilesImage,
        ':tilesInfo' => $_POST['tiles_info'] ?? null,
        ':metal' => $_POST['metal'] ?? null,
        ':metalImage' => $metalImage,
        ':metalInfo' => $_POST['metal_info'] ?? null
    ]);
    
    $customizationID = $pdo->lastInsertId();
    
    // Insert into order request
    $orderStmt = $pdo->prepare("
        INSERT INTO tbl_order_request (
            User_ID, Customization_ID, Quantity, 
            Order_Type, Total_Price
        ) VALUES (
            :userID, :customizationID, 1, 
            'custom', 0.00
        )
    ");
    
    $orderStmt->execute([
        ':userID' => $userID,
        ':customizationID' => $customizationID
    ]);
    
    $pdo->commit();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => [
            'customization_id' => $customizationID,
            'user_id' => $userID,
            'furniture' => $furnitureType,
            'size' => $standardSize === 'custom' ? $desiredSize : $standardSize,
            'color' => $_POST['color'] ?? 'N/A',
            'color_image' => $colorImage
        ]
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>