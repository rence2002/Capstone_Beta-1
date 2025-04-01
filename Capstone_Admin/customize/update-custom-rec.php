<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: /Capstone/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customizationID = $_POST['txtCustomizationID'] ?? null;

    // Fetch existing values
    $stmt = $pdo->prepare("SELECT * FROM tbl_customizations WHERE Customization_ID = :id");
    $stmt->bindParam(':id', $customizationID);
    $stmt->execute();
    $existingData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingData) {
        echo "Customization record not found.";
        exit();
    }

    // Initialize an array to hold the data to be updated
    $updateData = [];
    $bindParams = [];

    // Function to handle field updates
    function updateField($fieldName, $postName, &$updateData, &$bindParams, $existingData, $pdo) {
        if (isset($_POST[$postName]) && $_POST[$postName] != $existingData[$fieldName]) {
            $updateData[$fieldName] = $_POST[$postName];
            $bindParams[':' . $fieldName] = $_POST[$postName];
        }
    }

    // Directory to store uploaded images
    $uploadDir = "../uploads/customizations/";

    function uploadImage($inputName, $uploadDir, $existingImage, &$updateData, &$bindParams) {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == UPLOAD_ERR_OK) {
            $fileTmp = $_FILES[$inputName]['tmp_name'];
            $fileName = basename($_FILES[$inputName]['name']);
            $targetPath = $uploadDir . time() . "_" . $fileName;

            if (move_uploaded_file($fileTmp, $targetPath)) {
                //remove the existing image if its not empty
                if (!empty($existingImage) && file_exists($existingImage)) {
                    unlink($existingImage);
                }

                $fieldName = str_replace(['Image'], ['_Image'], $inputName) . "_URL";
                $updateData[$fieldName] = $targetPath;
                $bindParams[':' . $fieldName] = $targetPath;
                return;
            }
            else {
                echo "Failed to upload image";
            }
        }
        //if there is no new image dont add to the update data.
    }

    // Update fields only if they have new values
    updateField('Furniture_Type', 'txtFurnitureType', $updateData, $bindParams, $existingData, $pdo);
    updateField('Furniture_Type_Additional_Info', 'txtFurnitureTypeAdditionalInfo', $updateData, $bindParams, $existingData, $pdo);
    updateField('Standard_Size', 'txtStandardSize', $updateData, $bindParams, $existingData, $pdo);
    updateField('Desired_Size', 'txtDesiredSize', $updateData, $bindParams, $existingData, $pdo);
    updateField('Color', 'txtColor', $updateData, $bindParams, $existingData, $pdo);
    updateField('Color_Additional_Info', 'txtColorAdditionalInfo', $updateData, $bindParams, $existingData, $pdo);
    updateField('Texture', 'txtTexture', $updateData, $bindParams, $existingData, $pdo);
    updateField('Texture_Additional_Info', 'txtTextureAdditionalInfo', $updateData, $bindParams, $existingData, $pdo);
    updateField('Wood_Type', 'txtWoodType', $updateData, $bindParams, $existingData, $pdo);
    updateField('Wood_Additional_Info', 'txtWoodAdditionalInfo', $updateData, $bindParams, $existingData, $pdo);
    updateField('Foam_Type', 'txtFoamType', $updateData, $bindParams, $existingData, $pdo);
    updateField('Foam_Additional_Info', 'txtFoamAdditionalInfo', $updateData, $bindParams, $existingData, $pdo);
    updateField('Cover_Type', 'txtCoverType', $updateData, $bindParams, $existingData, $pdo);
    updateField('Cover_Additional_Info', 'txtCoverAdditionalInfo', $updateData, $bindParams, $existingData, $pdo);
    updateField('Design', 'txtDesign', $updateData, $bindParams, $existingData, $pdo);
    updateField('Design_Additional_Info', 'txtDesignAdditionalInfo', $updateData, $bindParams, $existingData, $pdo);
    updateField('Tile_Type', 'txtTileType', $updateData, $bindParams, $existingData, $pdo);
    updateField('Tile_Additional_Info', 'txtTileAdditionalInfo', $updateData, $bindParams, $existingData, $pdo);
    updateField('Metal_Type', 'txtMetalType', $updateData, $bindParams, $existingData, $pdo);
    updateField('Metal_Additional_Info', 'txtMetalAdditionalInfo', $updateData, $bindParams, $existingData, $pdo);
    // Correct the field name to Order_Status
    updateField('Order_Status', 'txtStatus', $updateData, $bindParams, $existingData, $pdo);
    updateField('Product_Status', 'txtProductStatus', $updateData, $bindParams, $existingData, $pdo);

    //upload images
    uploadImage('colorImage', $uploadDir, $existingData['Color_Image_URL'], $updateData, $bindParams);
    uploadImage('woodImage', $uploadDir, $existingData['Wood_Image_URL'], $updateData, $bindParams);
    uploadImage('foamImage', $uploadDir, $existingData['Foam_Image_URL'], $updateData, $bindParams);
    uploadImage('designImage', $uploadDir, $existingData['Design_Image_URL'], $updateData, $bindParams);
    uploadImage('textureImage', $uploadDir, $existingData['Texture_Image_URL'], $updateData, $bindParams);
    uploadImage('coverImage', $uploadDir, $existingData['Cover_Image_URL'], $updateData, $bindParams);
    uploadImage('tileImage', $uploadDir, $existingData['Tile_Image_URL'], $updateData, $bindParams);
    uploadImage('metalImage', $uploadDir, $existingData['Metal_Image_URL'], $updateData, $bindParams);

    
    // Construct the update query dynamically
    $updateQueryParts = [];
    foreach ($updateData as $field => $value) {
        $updateQueryParts[] = "$field = :$field";
    }

    if (!empty($updateQueryParts)) {
        $updateQuery = "UPDATE tbl_customizations SET " . implode(", ", $updateQueryParts) . ", Last_Update = NOW() WHERE Customization_ID = :customizationID";

        $stmt = $pdo->prepare($updateQuery);

        // Bind parameters
        foreach ($bindParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindParam(':customizationID', $customizationID);

        if ($stmt->execute()) {
            // Update tbl_progress with the new statuses
            $progressUpdateQuery = "UPDATE tbl_progress 
                                    SET Order_Status = :orderStatus, Product_Status = :productStatus 
                                    WHERE Product_ID = (SELECT Product_ID FROM tbl_customizations WHERE Customization_ID = :customizationID)";
            $progressStmt = $pdo->prepare($progressUpdateQuery);
            $progressStmt->bindParam(':orderStatus', $bindParams[':Order_Status'], PDO::PARAM_INT);
            $progressStmt->bindParam(':productStatus', $bindParams[':Product_Status'], PDO::PARAM_INT);
            $progressStmt->bindParam(':customizationID', $customizationID, PDO::PARAM_INT);
            $progressStmt->execute();

            // **Check if both statuses in tbl_progress are 100 and delete if so**
            $checkProgressQuery = "SELECT Order_Status, Product_Status FROM tbl_progress WHERE Product_ID = (SELECT Product_ID FROM tbl_customizations WHERE Customization_ID = :customizationID)";
            $checkProgressStmt = $pdo->prepare($checkProgressQuery);
            $checkProgressStmt->bindParam(':customizationID', $customizationID, PDO::PARAM_INT);
            $checkProgressStmt->execute();
            $progressData = $checkProgressStmt->fetch(PDO::FETCH_ASSOC);

            if ($progressData && $progressData['Order_Status'] == 100 && $progressData['Product_Status'] == 100) {
                $deleteProgressQuery = "DELETE FROM tbl_progress WHERE Product_ID = (SELECT Product_ID FROM tbl_customizations WHERE Customization_ID = :customizationID)";
                $deleteProgressStmt = $pdo->prepare($deleteProgressQuery);
                $deleteProgressStmt->bindParam(':customizationID', $customizationID, PDO::PARAM_INT);
                $deleteProgressStmt->execute();
            }

            header("Location: read-all-custom-form.php?message=Record updated successfully");
            exit();
        } else {
            echo "Error updating customization record.";
            print_r($stmt->errorInfo()); // Debugging
        }
    } else {
        header("Location: read-all-custom-form.php?message=No changes were made");
        exit();
    }
} else {
    echo "Invalid request.";
}
?>
