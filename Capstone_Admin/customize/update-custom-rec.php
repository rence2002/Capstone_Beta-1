<?php
session_start();
include '../config/database.php'; // Provides $pdo

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Use a more robust relative path or absolute path if needed
    header("Location: ../login.php");
    exit();
}

// Ensure this script is accessed via POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method.";
    exit();
}

// --- Configuration ---
// Define the base directory for uploads
$uploadBaseDir = 'C:/xampp/htdocs/Capstone_Beta/uploads/customizations/'; // Absolute server path for file operations
$dbPathPrefix = 'uploads/customizations/'; // Relative path to store in DB

// Create upload directory if it doesn't exist
if (!is_dir($uploadBaseDir)) {
    if (!mkdir($uploadBaseDir, 0777, true)) {
        die("Failed to create upload directory: " . $uploadBaseDir);
    }
}

// --- Form Data Processing ---
$customizationID = $_POST['txtCustomizationID'] ?? null;

if (!$customizationID) {
    echo "Customization ID is missing.";
    exit();
}

try {
    // Fetch existing customization data to compare and get old image paths
    $stmt = $pdo->prepare("SELECT * FROM tbl_customizations WHERE Customization_ID = :id");
    $stmt->bindParam(':id', $customizationID, PDO::PARAM_INT);
    $stmt->execute();
    $existingData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingData) {
        echo "Customization record not found for ID: " . htmlspecialchars($customizationID);
        exit();
    }

    // --- Prepare Update ---
    $updateFields = []; // Stores "FieldName = :placeholder" strings
    $bindParams = [':customizationID' => $customizationID]; // Stores parameters to bind

    // Helper function to add fields to update if changed
    function addFieldToUpdate($fieldName, $postName, &$updateFields, &$bindParams, $existingValue) {
        if (isset($_POST[$postName]) && $_POST[$postName] !== $existingValue) {
            $placeholder = ':' . $fieldName;
            $updateFields[] = "`" . $fieldName . "` = " . $placeholder; // Use backticks for field names
            $bindParams[$placeholder] = $_POST[$postName];
            return true; // Indicate that the field was added for update
        }
        return false; // Indicate no change
    }

    // Helper function to handle image processing (removal and upload)
    function processImageField($fieldNamePrefix, $inputName, $existingDbPath, $uploadBaseDir, $dbPathPrefix, &$updateFields, &$bindParams) {
        $dbFieldName = $fieldNamePrefix . '_Image_URL';
        $removeCheckboxName = 'remove_' . $inputName;
        $placeholder = ':' . $dbFieldName;
        $imageRemoved = false;

        // 1. Check for Removal Request
        if (isset($_POST[$removeCheckboxName]) && $_POST[$removeCheckboxName] == '1') {
            // If removal is checked, mark for DB update to NULL and delete file
            $updateFields[] = "`" . $dbFieldName . "` = " . $placeholder;
            $bindParams[$placeholder] = null; // Set DB field to NULL
            $imageRemoved = true;

            // Construct absolute server path for deletion
            if (!empty($existingDbPath)) {
                $existingServerPath = $uploadBaseDir . basename($existingDbPath);
                if (file_exists($existingServerPath)) {
                    if (!unlink($existingServerPath)) {
                        error_log("Failed to delete image: " . $existingServerPath);
                    }
                }
            }
        }

        // 2. Check for New Upload (only if not removed)
        if (!$imageRemoved && isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == UPLOAD_ERR_OK) {
            $fileTmp = $_FILES[$inputName]['tmp_name'];
            // Sanitize filename
            $fileName = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", basename($_FILES[$inputName]['name']));
            $uniqueFileName = time() . "_" . $fileName;
            $targetServerPath = $uploadBaseDir . $uniqueFileName; // Absolute path for move_uploaded_file
            $targetDbPath = $dbPathPrefix . $uniqueFileName; // Relative path for DB storage

            if (move_uploaded_file($fileTmp, $targetServerPath)) {
                // Successfully uploaded new file
                $updateFields[] = "`" . $dbFieldName . "` = " . $placeholder;
                $bindParams[$placeholder] = $targetDbPath; // Store relative path in DB

                // Delete the old image if a new one was successfully uploaded
                if (!empty($existingDbPath)) {
                    $existingServerPath = $uploadBaseDir . basename($existingDbPath);
                    if (file_exists($existingServerPath)) {
                        if (!unlink($existingServerPath)) {
                            error_log("Failed to delete old image after new upload: " . $existingServerPath);
                        }
                    }
                }
            } else {
                error_log("Failed to move uploaded file to: " . $targetServerPath);
                echo "Error uploading " . htmlspecialchars($inputName) . ". Changes might not be fully saved.";
            }
        }
    }


    // --- Process Text/Select Fields ---
    addFieldToUpdate('Furniture_Type', 'txtFurnitureType', $updateFields, $bindParams, $existingData['Furniture_Type']);
    addFieldToUpdate('Furniture_Type_Additional_Info', 'txtFurnitureTypeAdditionalInfo', $updateFields, $bindParams, $existingData['Furniture_Type_Additional_Info']);
    addFieldToUpdate('Standard_Size', 'txtStandardSize', $updateFields, $bindParams, $existingData['Standard_Size']);
    addFieldToUpdate('Desired_Size', 'txtDesiredSize', $updateFields, $bindParams, $existingData['Desired_Size']);
    addFieldToUpdate('Color', 'txtColor', $updateFields, $bindParams, $existingData['Color']);
    addFieldToUpdate('Color_Additional_Info', 'txtColorAdditionalInfo', $updateFields, $bindParams, $existingData['Color_Additional_Info']);
    addFieldToUpdate('Texture', 'txtTexture', $updateFields, $bindParams, $existingData['Texture']);
    addFieldToUpdate('Texture_Additional_Info', 'txtTextureAdditionalInfo', $updateFields, $bindParams, $existingData['Texture_Additional_Info']);
    addFieldToUpdate('Wood_Type', 'txtWoodType', $updateFields, $bindParams, $existingData['Wood_Type']);
    addFieldToUpdate('Wood_Additional_Info', 'txtWoodAdditionalInfo', $updateFields, $bindParams, $existingData['Wood_Additional_Info']);
    addFieldToUpdate('Foam_Type', 'txtFoamType', $updateFields, $bindParams, $existingData['Foam_Type']);
    addFieldToUpdate('Foam_Additional_Info', 'txtFoamAdditionalInfo', $updateFields, $bindParams, $existingData['Foam_Additional_Info']);
    addFieldToUpdate('Cover_Type', 'txtCoverType', $updateFields, $bindParams, $existingData['Cover_Type']);
    addFieldToUpdate('Cover_Additional_Info', 'txtCoverAdditionalInfo', $updateFields, $bindParams, $existingData['Cover_Additional_Info']);
    addFieldToUpdate('Design', 'txtDesign', $updateFields, $bindParams, $existingData['Design']);
    addFieldToUpdate('Design_Additional_Info', 'txtDesignAdditionalInfo', $updateFields, $bindParams, $existingData['Design_Additional_Info']);
    addFieldToUpdate('Tile_Type', 'txtTileType', $updateFields, $bindParams, $existingData['Tile_Type']);
    addFieldToUpdate('Tile_Additional_Info', 'txtTileAdditionalInfo', $updateFields, $bindParams, $existingData['Tile_Additional_Info']);
    addFieldToUpdate('Metal_Type', 'txtMetalType', $updateFields, $bindParams, $existingData['Metal_Type']);
    addFieldToUpdate('Metal_Additional_Info', 'txtMetalAdditionalInfo', $updateFields, $bindParams, $existingData['Metal_Additional_Info']);

    // Process Product Status - Important for tbl_progress sync
    $productStatusChanged = addFieldToUpdate('Product_Status', 'txtProductStatus', $updateFields, $bindParams, $existingData['Product_Status']);

    // --- Process Image Fields ---
    processImageField('Color', 'colorImage', $existingData['Color_Image_URL'], $uploadBaseDir, $dbPathPrefix, $updateFields, $bindParams);
    processImageField('Texture', 'textureImage', $existingData['Texture_Image_URL'], $uploadBaseDir, $dbPathPrefix, $updateFields, $bindParams);
    processImageField('Wood', 'woodImage', $existingData['Wood_Image_URL'], $uploadBaseDir, $dbPathPrefix, $updateFields, $bindParams);
    processImageField('Foam', 'foamImage', $existingData['Foam_Image_URL'], $uploadBaseDir, $dbPathPrefix, $updateFields, $bindParams);
    processImageField('Cover', 'coverImage', $existingData['Cover_Image_URL'], $uploadBaseDir, $dbPathPrefix, $updateFields, $bindParams);
    processImageField('Design', 'designImage', $existingData['Design_Image_URL'], $uploadBaseDir, $dbPathPrefix, $updateFields, $bindParams);
    processImageField('Tile', 'tileImage', $existingData['Tile_Image_URL'], $uploadBaseDir, $dbPathPrefix, $updateFields, $bindParams);
    processImageField('Metal', 'metalImage', $existingData['Metal_Image_URL'], $uploadBaseDir, $dbPathPrefix, $updateFields, $bindParams);


    // --- Execute Update Query if Changes Were Made ---
    if (!empty($updateFields)) {
        // Add Last_Update timestamp
        $updateFields[] = "`Last_Update` = NOW()";

        $updateQuery = "UPDATE `tbl_customizations` SET " . implode(", ", $updateFields) . " WHERE `Customization_ID` = :customizationID";

        $stmt = $pdo->prepare($updateQuery);

        // Bind all parameters collected
        if ($stmt->execute($bindParams)) {
            $message = "Record updated successfully.";

            // --- Sync with tbl_progress if Product_Status changed and Product_ID exists ---
            if ($productStatusChanged && !empty($existingData['Product_ID'])) {
                $newProductStatus = $bindParams[':Product_Status']; // Get the new status value

                $progressUpdateQuery = "UPDATE `tbl_progress`
                                        SET `Product_Status` = :productStatus, `LastUpdate` = NOW()
                                        WHERE `Product_ID` = :productID AND `Order_Type` = 'custom'";
                $progressStmt = $pdo->prepare($progressUpdateQuery);
                $progressStmt->bindParam(':productStatus', $newProductStatus, PDO::PARAM_INT);
                $progressStmt->bindParam(':productID', $existingData['Product_ID'], PDO::PARAM_INT);

                if ($progressStmt->execute()) {
                     $message .= " Progress status synced.";

                    // --- Check if Product_Status reached 100 (or final state) and delete progress record ---
                    // Assuming 100 means fully completed and the progress entry is no longer needed
                    if ($newProductStatus == 100) {
                        $deleteProgressQuery = "DELETE FROM `tbl_progress`
                                                WHERE `Product_ID` = :productID AND `Order_Type` = 'custom'";
                        $deleteProgressStmt = $pdo->prepare($deleteProgressQuery);
                        $deleteProgressStmt->bindParam(':productID', $existingData['Product_ID'], PDO::PARAM_INT);
                        if ($deleteProgressStmt->execute()) {
                            $message .= " Completed progress record removed.";
                        } else {
                             $message .= " Failed to remove completed progress record.";
                             error_log("Failed to delete progress record for Product_ID: " . $existingData['Product_ID']);
                        }
                    }
                } else {
                    $message .= " Failed to sync progress status.";
                    error_log("Failed to update progress status for Product_ID: " . $existingData['Product_ID']);
                    // Log error: print_r($progressStmt->errorInfo());
                }
            } elseif ($productStatusChanged && empty($existingData['Product_ID'])) {
                 $message .= " Product_Status updated, but no associated Product_ID found to sync progress.";
            }

            // Redirect after successful update
            header("Location: read-all-custom-form.php?message=" . urlencode($message));
            exit();

        } else {
            // Handle update failure
            echo "Error updating customization record.";
            // Log error: print_r($stmt->errorInfo());
            exit();
        }
    } else {
        // No changes detected
        header("Location: read-all-custom-form.php?message=" . urlencode("No changes were made."));
        exit();
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    // Log error: error_log("Database Error: " . $e->getMessage());
    exit();
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
    // Log error: error_log("General Error: " . $e->getMessage());
    exit();
}
?>
