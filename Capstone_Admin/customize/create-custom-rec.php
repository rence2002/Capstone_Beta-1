<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /Capstone/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs and handle optional fields
    $userID = isset($_POST['txtUserID']) ? htmlspecialchars(trim($_POST['txtUserID'])) : null;
    $furnitureType = isset($_POST['txtFurnitureType']) ? htmlspecialchars(trim($_POST['txtFurnitureType'])) : null;
    $furnitureTypeInfo = isset($_POST['txtFurnitureTypeInfo']) ? htmlspecialchars(trim($_POST['txtFurnitureTypeInfo'])) : null;
    $standardSize = isset($_POST['txtStandardSize']) ? htmlspecialchars(trim($_POST['txtStandardSize'])) : null;
    $desiredSize = isset($_POST['txtDesiredSize']) ? htmlspecialchars(trim($_POST['txtDesiredSize'])) : null;
    $color = isset($_POST['txtColor']) ? htmlspecialchars(trim($_POST['txtColor'])) : null;
    $colorInfo = isset($_POST['txtColorInfo']) ? htmlspecialchars(trim($_POST['txtColorInfo'])) : null;
    $texture = isset($_POST['txtTexture']) ? htmlspecialchars(trim($_POST['txtTexture'])) : null;
    $textureInfo = isset($_POST['txtTextureInfo']) ? htmlspecialchars(trim($_POST['txtTextureInfo'])) : null;
    $woodType = isset($_POST['txtWoodType']) ? htmlspecialchars(trim($_POST['txtWoodType'])) : null;
    $woodInfo = isset($_POST['txtWoodInfo']) ? htmlspecialchars(trim($_POST['txtWoodInfo'])) : null;
    $foamType = isset($_POST['txtFoamType']) ? htmlspecialchars(trim($_POST['txtFoamType'])) : null;
    $foamInfo = isset($_POST['txtFoamInfo']) ? htmlspecialchars(trim($_POST['txtFoamInfo'])) : null;
    $coverType = isset($_POST['txtCoverType']) ? htmlspecialchars(trim($_POST['txtCoverType'])) : null;
    $coverInfo = isset($_POST['txtCoverInfo']) ? htmlspecialchars(trim($_POST['txtCoverInfo'])) : null;
    $design = isset($_POST['txtDesign']) ? htmlspecialchars(trim($_POST['txtDesign'])) : null;
    $designInfo = isset($_POST['txtDesignInfo']) ? htmlspecialchars(trim($_POST['txtDesignInfo'])) : null;
    $tileType = isset($_POST['txtTileType']) ? htmlspecialchars(trim($_POST['txtTileType'])) : null;
    $tileInfo = isset($_POST['txtTileInfo']) ? htmlspecialchars(trim($_POST['txtTileInfo'])) : null;
    $metalType = isset($_POST['txtMetalType']) ? htmlspecialchars(trim($_POST['txtMetalType'])) : null;
    $metalInfo = isset($_POST['txtMetalInfo']) ? htmlspecialchars(trim($_POST['txtMetalInfo'])) : null;

    $quantity = isset($_POST['txtQuantity']) ? intval($_POST['txtQuantity']) : 1; // Default to 1 if not provided
    $totalPrice = isset($_POST['txtTotalPrice']) ? floatval($_POST['txtTotalPrice']) : 0.00; // Default to 0.00 if not provided

    // Function to handle file uploads
    function handleFileUpload($fileKey, $uploadDir) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
            $filename = basename($_FILES[$fileKey]['name']);
            $targetFilePath = $uploadDir . $filename;

            // Ensure directory exists
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetFilePath)) {
                return $targetFilePath;
            }
        }
        return null;
    }

    $uploadDir = '../uploads/customizations/';
    $colorImage = handleFileUpload('fileColorImage', $uploadDir);
    $textureImage = handleFileUpload('fileTextureImage', $uploadDir);
    $woodImage = handleFileUpload('fileWoodImage', $uploadDir);
    $foamImage = handleFileUpload('fileFoamImage', $uploadDir);
    $coverImage = handleFileUpload('fileCoverImage', $uploadDir);
    $designImage = handleFileUpload('fileDesignImage', $uploadDir);
    $tileImage = handleFileUpload('fileTileImage', $uploadDir);
    $metalImage = handleFileUpload('fileMetalImage', $uploadDir);

    try {
        // Begin transaction
        $pdo->beginTransaction();
    
        // Insert into tbl_customizations_temp
        $customizationQuery = "
            INSERT INTO tbl_customizations_temp (
                User_ID, Furniture_Type, Furniture_Type_Additional_Info, Standard_Size, Desired_Size,
                Color, Color_Image_URL, Color_Additional_Info, Texture, Texture_Image_URL, Texture_Additional_Info,
                Wood_Type, Wood_Image_URL, Wood_Additional_Info, Foam_Type, Foam_Image_URL, Foam_Additional_Info,
                Cover_Type, Cover_Image_URL, Cover_Additional_Info, Design, Design_Image_URL, Design_Additional_Info,
                Tile_Type, Tile_Image_URL, Tile_Additional_Info, Metal_Type, Metal_Image_URL, Metal_Additional_Info
            ) VALUES (
                :userID, :furnitureType, :furnitureTypeInfo, :standardSize, :desiredSize,
                :color, :colorImage, :colorInfo, :texture, :textureImage, :textureInfo,
                :woodType, :woodImage, :woodInfo, :foamType, :foamImage, :foamInfo,
                :coverType, :coverImage, :coverInfo, :design, :designImage, :designInfo,
                :tileType, :tileImage, :tileInfo, :metalType, :metalImage, :metalInfo
            )
        ";
    
        $stmt = $pdo->prepare($customizationQuery);
    
        // Bind parameters
        $stmt->execute([
            ':userID' => $userID,
            ':furnitureType' => $furnitureType,
            ':furnitureTypeInfo' => $furnitureTypeInfo,
            ':standardSize' => $standardSize,
            ':desiredSize' => $desiredSize,
            ':color' => $color,
            ':colorImage' => $colorImage,
            ':colorInfo' => $colorInfo,
            ':texture' => $texture,
            ':textureImage' => $textureImage,
            ':textureInfo' => $textureInfo,
            ':woodType' => $woodType,
            ':woodImage' => $woodImage,
            ':woodInfo' => $woodInfo,
            ':foamType' => $foamType,
            ':foamImage' => $foamImage,
            ':foamInfo' => $foamInfo,
            ':coverType' => $coverType,
            ':coverImage' => $coverImage,
            ':coverInfo' => $coverInfo,
            ':design' => $design,
            ':designImage' => $designImage,
            ':designInfo' => $designInfo,
            ':tileType' => $tileType,
            ':tileImage' => $tileImage,
            ':tileInfo' => $tileInfo,
            ':metalType' => $metalType,
            ':metalImage' => $metalImage,
            ':metalInfo' => $metalInfo
        ]);
    
        // Get last inserted customization ID
        $customizationID = $pdo->lastInsertId();
    
        // Insert into tbl_order_request
        $orderQuery = "
            INSERT INTO tbl_order_request (
                User_ID, Product_ID, Customization_ID, Quantity, Order_Type, Order_Status, Total_Price
            ) VALUES (
                :userID, NULL, :customizationID, :quantity, 'custom', 0, :totalPrice
            )
        ";
    
        $stmt = $pdo->prepare($orderQuery);
        $stmt->execute([
            ':userID' => $userID,
            ':customizationID' => $customizationID,
            ':quantity' => $quantity,
            ':totalPrice' => $totalPrice
        ]);
    
        // Commit transaction
        $pdo->commit();
    
        // Redirect to read-all-custom-form.php
        header("Location: /Capstone/customize/read-all-custom-form.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>
