<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

// Include the database connection
include("../config/database.php");

// Function to upload an image and return the file path
function uploadImage($file, $uploadDir) {
    $targetDir = $uploadDir;
    $targetFile = $targetDir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($targetFile)) {
        $baseName = pathinfo($file["name"], PATHINFO_FILENAME);
        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $i = 1;
        while (file_exists($targetDir . $baseName . "_" . $i . "." . $extension)) {
            $i++;
        }
        $targetFile = $targetDir . $baseName . "_" . $i . "." . $extension;
    }

    // Check file size
    if ($file["size"] > 5000000) { // 5MB limit
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        return null;
    } else {
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $targetFile;
        } else {
            return null;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION["user_id"];
    $furnitureType = $_POST['furniture'] ?? null;
    $furnitureInfo = $_POST['furniture-info'] ?? null;
    $standardSize = $_POST['sizes'] ?? null;
    $desiredSize = $_POST['sizes-info'] ?? null;
    $color = $_POST['color'] ?? null;
    $colorInfo = $_POST['color-info'] ?? null;
    $texture = $_POST['texture'] ?? null;
    $textureInfo = $_POST['texture-info'] ?? null;
    $woodType = $_POST['woods'] ?? null;
    $woodInfo = $_POST['wood-info'] ?? null;
    $foamType = $_POST['foam'] ?? null;
    $foamInfo = $_POST['foam-info'] ?? null;
    $coverType = $_POST['cover'] ?? null;
    $coverInfo = $_POST['cover-info'] ?? null;
    $design = $_POST['design'] ?? null;
    $designInfo = $_POST['design-info'] ?? null;
    $tileType = $_POST['tile'] ?? null;
    $tileInfo = $_POST['tile-info'] ?? null;
    $metalType = $_POST['metal'] ?? null;
    $metalInfo = $_POST['metal-info'] ?? null;

    // Upload images
    $colorImageURL = isset($_FILES['color-file-upload']) && $_FILES['color-file-upload']['error'] === UPLOAD_ERR_OK ? uploadImage($_FILES['color-file-upload'], '../uploads/custom/') : null;
    $textureImageURL = isset($_FILES['texture-file-upload']) && $_FILES['texture-file-upload']['error'] === UPLOAD_ERR_OK ? uploadImage($_FILES['texture-file-upload'], '../uploads/custom/') : null;
    $woodImageURL = isset($_FILES['wood-file-upload']) && $_FILES['wood-file-upload']['error'] === UPLOAD_ERR_OK ? uploadImage($_FILES['wood-file-upload'], '../uploads/custom/') : null;
    $foamImageURL = isset($_FILES['foam-file-upload']) && $_FILES['foam-file-upload']['error'] === UPLOAD_ERR_OK ? uploadImage($_FILES['foam-file-upload'], '../uploads/custom/') : null;
    $coverImageURL = isset($_FILES['cover-file-upload']) && $_FILES['cover-file-upload']['error'] === UPLOAD_ERR_OK ? uploadImage($_FILES['cover-file-upload'], '../uploads/custom/') : null;
    $designImageURL = isset($_FILES['design-file-upload']) && $_FILES['design-file-upload']['error'] === UPLOAD_ERR_OK ? uploadImage($_FILES['design-file-upload'], '../uploads/custom/') : null;
    $tileImageURL = isset($_FILES['tile-file-upload']) && $_FILES['tile-file-upload']['error'] === UPLOAD_ERR_OK ? uploadImage($_FILES['tile-file-upload'], '../uploads/custom/') : null;
    $metalImageURL = isset($_FILES['metal-file-upload']) && $_FILES['metal-file-upload']['error'] === UPLOAD_ERR_OK ? uploadImage($_FILES['metal-file-upload'], '../uploads/custom/') : null;

    // Insert into tbl_customizations_temp
    try {
        $stmt = $pdo->prepare("INSERT INTO tbl_customizations_temp (User_ID, Furniture_Type, Furniture_Type_Additional_Info, Standard_Size, Desired_Size, Color, Color_Image_URL, Color_Additional_Info, Texture, Texture_Image_URL, Texture_Additional_Info, Wood_Type, Wood_Image_URL, Wood_Additional_Info, Foam_Type, Foam_Image_URL, Foam_Additional_Info, Cover_Type, Cover_Image_URL, Cover_Additional_Info, Design, Design_Image_URL, Design_Additional_Info, Tile_Type, Tile_Image_URL, Tile_Additional_Info, Metal_Type, Metal_Image_URL, Metal_Additional_Info) VALUES (:userId, :furnitureType, :furnitureInfo, :standardSize, :desiredSize, :color, :colorImageURL, :colorInfo, :texture, :textureImageURL, :textureInfo, :woodType, :woodImageURL, :woodInfo, :foamType, :foamImageURL, :foamInfo, :coverType, :coverImageURL, :coverInfo, :design, :designImageURL, :designInfo, :tileType, :tileImageURL, :tileInfo, :metalType, :metalImageURL, :metalInfo)");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
        $stmt->bindParam(':furnitureType', $furnitureType, PDO::PARAM_STR);
        $stmt->bindParam(':furnitureInfo', $furnitureInfo, PDO::PARAM_STR);
        $stmt->bindParam(':standardSize', $standardSize, PDO::PARAM_STR);
        $stmt->bindParam(':desiredSize', $desiredSize, PDO::PARAM_STR);
        $stmt->bindParam(':color', $color, PDO::PARAM_STR);
        $stmt->bindParam(':colorImageURL', $colorImageURL, PDO::PARAM_STR);
        $stmt->bindParam(':colorInfo', $colorInfo, PDO::PARAM_STR);
        $stmt->bindParam(':texture', $texture, PDO::PARAM_STR);
        $stmt->bindParam(':textureImageURL', $textureImageURL, PDO::PARAM_STR);
        $stmt->bindParam(':textureInfo', $textureInfo, PDO::PARAM_STR);
        $stmt->bindParam(':woodType', $woodType, PDO::PARAM_STR);
        $stmt->bindParam(':woodImageURL', $woodImageURL, PDO::PARAM_STR);
        $stmt->bindParam(':woodInfo', $woodInfo, PDO::PARAM_STR);
        $stmt->bindParam(':foamType', $foamType, PDO::PARAM_STR);
        $stmt->bindParam(':foamImageURL', $foamImageURL, PDO::PARAM_STR);
        $stmt->bindParam(':foamInfo', $foamInfo, PDO::PARAM_STR);
        $stmt->bindParam(':coverType', $coverType, PDO::PARAM_STR);
        $stmt->bindParam(':coverImageURL', $coverImageURL, PDO::PARAM_STR);
        $stmt->bindParam(':coverInfo', $coverInfo, PDO::PARAM_STR);
        $stmt->bindParam(':design', $design, PDO::PARAM_STR);
        $stmt->bindParam(':designImageURL', $designImageURL, PDO::PARAM_STR);
        $stmt->bindParam(':designInfo', $designInfo, PDO::PARAM_STR);
        $stmt->bindParam(':tileType', $tileType, PDO::PARAM_STR);
        $stmt->bindParam(':tileImageURL', $tileImageURL, PDO::PARAM_STR);
        $stmt->bindParam(':tileInfo', $tileInfo, PDO::PARAM_STR);
        $stmt->bindParam(':metalType', $metalType, PDO::PARAM_STR);
        $stmt->bindParam(':metalImageURL', $metalImageURL, PDO::PARAM_STR);
        $stmt->bindParam(':metalInfo', $metalInfo, PDO::PARAM_STR);
        $stmt->execute();
        $customizationId = $pdo->lastInsertId();
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }

    // Insert into tbl_order_request
    try {
        $stmt = $pdo->prepare("INSERT INTO tbl_order_request (User_ID, Customization_ID, Quantity, Order_Type, Total_Price) VALUES (:userId, :customizationId, 1, 'custom', 0.00)");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
        $stmt->bindParam(':customizationId', $customizationId, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Your customization has been submitted successfully!',
    ]);
}
?>
