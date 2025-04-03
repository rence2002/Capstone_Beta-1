<?php
session_start();
header('Content-Type: application/json'); // Ensure JSON response

// Initialize $transactionStarted before the try block
$transactionStarted = false;

// Validate session
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    return;
}

include("../config/database.php");

// Fetch user details
$userID = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT First_Name, Last_Name FROM tbl_user_info WHERE User_ID = :userID"); // Corrected query
$stmt->execute([':userID' => $userID]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
$userName = ($userData['First_Name'] ?? '') . ' ' . ($userData['Last_Name'] ?? ''); // Concatenate first and last name
$userName = trim($userName) !== '' ? $userName : 'N/A';

// Validation rules
$requiredFields = ['furniture' => 'Furniture Type', 'sizes' => 'Size'];
foreach ($requiredFields as $field => $label) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "$label is required"]);
        return;
    }
}

// File upload handler
function handleFileUpload($fileKey, $uploadDir = '../uploads/custom/') {
    if (empty($_FILES[$fileKey]['name'])) return null;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($_FILES[$fileKey]['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        return null;
    }

    $maxFileSize = 5 * 1024 * 1024; // 5 MB
    if ($_FILES[$fileKey]['size'] > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds the limit']);
        return null;
    }

    $filename = uniqid() . '_' . basename($_FILES[$fileKey]['name']);
    $targetFilePath = $uploadDir . $filename;

    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Directory creation failed']);
            return null;
        }
    }

    if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetFilePath)) {
        echo json_encode(['success' => false, 'message' => 'File upload failed']);
        return null;
    }

    // Return the web-accessible path
    return '/Capstone_Beta/Capstone_Client/uploads/custom/' . $filename;
}

// Collect form data
$furnitureType = $_POST['furniture'] ?? null;
$furnitureInfo = $_POST['furniture-info'] ?? null;
$standardSize = $_POST['sizes'] ?? null;
$desiredSize = $_POST['sizes-info'] ?? null;

// Process all file uploads
$colorImage = handleFileUpload('fileColorImage');
$textureImage = handleFileUpload('fileTextureImage');
$woodImage = handleFileUpload('fileWoodImage');
$foamImage = handleFileUpload('fileFoamImage');
$coverImage = handleFileUpload('fileCoverImage');
$designImage = handleFileUpload('fileDesignImage');
$tilesImage = handleFileUpload('fileTileImage');
$metalImage = handleFileUpload('fileMetalImage');

// Start transaction
try {
    $pdo->beginTransaction();
    $transactionStarted = true;

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
            :colorAdditionalInfo, :texture, :textureImage,
            :textureAdditionalInfo, :wood, :woodImage,
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
        ':colorAdditionalInfo' => $_POST['color-info'] ?? null,
        ':texture' => $_POST['texture'] ?? null,
        ':textureImage' => $textureImage,
        ':textureAdditionalInfo' => $_POST['texture-info'] ?? null,
        ':wood' => $_POST['wood'] ?? null,
        ':woodImage' => $woodImage,
        ':woodInfo' => $_POST['wood-info'] ?? null,
        ':foam' => $_POST['foam'] ?? null,
        ':foamImage' => $foamImage,
        ':foamInfo' => $_POST['foam-info'] ?? null,
        ':cover' => $_POST['cover'] ?? null,
        ':coverImage' => $coverImage,
        ':coverInfo' => $_POST['cover-info'] ?? null,
        ':design' => $_POST['design'] ?? null,
        ':designImage' => $designImage,
        ':designInfo' => $_POST['design-info'] ?? null,
        ':tiles' => $_POST['tiles'] ?? null,
        ':tilesImage' => $tilesImage,
        ':tilesInfo' => $_POST['tiles-info'] ?? null,
        ':metal' => $_POST['metal'] ?? null,
        ':metalImage' => $metalImage,
        ':metalInfo' => $_POST['metal-info'] ?? null
    ]);

    $customizationID = $pdo->lastInsertId();

    // Create a new "custom" product in tbl_prod_info
    $productName = "Custom " . ucfirst($furnitureType) . " Order";
    $productDescription = "Custom order from request #" . $customizationID;
    $productStmt = $pdo->prepare("
        INSERT INTO tbl_prod_info (
            Product_Name, Description, product_type, Price
        ) VALUES (
            :productName, :productDescription, 'custom', 0.00
        )
    ");
    $productStmt->execute([
        ':productName' => $productName,
        ':productDescription' => $productDescription
    ]);

    $productID = $pdo->lastInsertId();

    // Insert into order request
    $orderStmt = $pdo->prepare("
        INSERT INTO tbl_order_request (
            User_ID, Customization_ID, Product_ID, Quantity, 
            Order_Type, Total_Price
        ) VALUES (
            :userID, :customizationID, :productID, 1, 
            'custom', 0.00
        )
    ");

    $orderStmt->execute([
        ':userID' => $userID,
        ':customizationID' => $customizationID,
        ':productID' => $productID
    ]);

    $pdo->commit();

    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => [
            'customization_id' => $customizationID,
            'product_id' => $productID,
            'user_id' => $userID,
            'user_name' => $userName,
            'furniture' => $furnitureType,
            'size' => $standardSize === 'custom' ? $desiredSize : $standardSize,
            'color' => $_POST['color'] ?? 'N/A',
            'color_image' => $colorImage,
            'color_info' => $_POST['color-info'] ?? 'N/A',
            'texture' => $_POST['texture'] ?? 'N/A',
            'texture_image' => $textureImage,
            'texture_info' => $_POST['texture-info'] ?? 'N/A',
            'wood' => $_POST['wood'] ?? 'N/A',
            'wood_image' => $woodImage,
            'wood_info' => $_POST['wood-info'] ?? 'N/A',
            'foam' => $_POST['foam'] ?? 'N/A',
            'foam_image' => $foamImage,
            'foam_info' => $_POST['foam-info'] ?? 'N/A',
            'cover' => $_POST['cover'] ?? 'N/A',
            'cover_image' => $coverImage,
            'cover_info' => $_POST['cover-info'] ?? 'N/A',
            'design' => $_POST['design'] ?? 'N/A',
            'design_image' => $designImage,
            'design_info' => $_POST['design-info'] ?? 'N/A',
            'tiles' => $_POST['tiles'] ?? 'N/A',
            'tiles_image' => $tilesImage,
            'tiles_info' => $_POST['tiles-info'] ?? 'N/A',
            'metal' => $_POST['metal'] ?? 'N/A',
            'metal_image' => $metalImage,
            'metal_info' => $_POST['metal-info'] ?? 'N/A',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
    return;
} catch (PDOException $e) {
    if ($transactionStarted) {
        $pdo->rollBack();
    }
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    return;
} catch (Exception $e) {
    if ($transactionStarted) {
        $pdo->rollBack();
    }
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
    return;
} finally {
    // No need to rollback here, it's already done in catch blocks
}
?>
