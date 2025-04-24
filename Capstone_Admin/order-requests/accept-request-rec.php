<?php
session_start();
include '../config/database.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php"); // Corrected redirect path
    exit();
}

// --- Validate Input (Using POST now) ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$requestID = isset($_POST['id']) ? (int)$_POST['id'] : null;
$paymentStatus = isset($_POST['payment_status']) ? $_POST['payment_status'] : null; // From the dropdown

if (!$requestID) {
    die("Invalid or missing request ID.");
}

if (!$paymentStatus) {
    die("Invalid or missing payment status selection.");
}

// Validate payment status value against allowed enum values in tbl_order_request
$validPaymentStatuses = ['downpayment_paid', 'fully_paid']; // Only these are selectable in the form
if (!in_array($paymentStatus, $validPaymentStatuses)) {
    die("Invalid payment status value provided.");
}

try {
    // Start Transaction
    $pdo->beginTransaction();

    // --- 1. Fetch the Order Request ---
    $stmt = $pdo->prepare("SELECT * FROM tbl_order_request WHERE Request_ID = :requestID AND Processed = 0"); // Ensure it's not already processed
    $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
    $stmt->execute();
    $orderRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orderRequest) {
        // Maybe already processed or doesn't exist
        throw new Exception("Order request not found or already processed (ID: $requestID).");
    }

    // --- 2. Prepare Data for tbl_progress ---
    $userID = $orderRequest['User_ID'];
    $orderType = $orderRequest['Order_Type'];
    $quantity = $orderRequest['Quantity'];
    $totalPrice = $orderRequest['Total_Price'];
    $productIDForProgress = null; // Will be set below
    $productNameForProgress = null; // Will be set below

    if ($orderType == 'custom') {
        // --- 2a. Handle Custom Order ---
        $tempCustomizationID = $orderRequest['Customization_ID'];
        if (!$tempCustomizationID) {
            throw new Exception("Custom order request (ID: $requestID) is missing the temporary customization link.");
        }

        // Fetch temporary customization details
        $custStmt = $pdo->prepare("SELECT * FROM tbl_customizations_temp WHERE Temp_Customization_ID = :tempID");
        $custStmt->bindParam(':tempID', $tempCustomizationID, PDO::PARAM_INT);
        $custStmt->execute();
        $customization = $custStmt->fetch(PDO::FETCH_ASSOC);

        if (!$customization) {
            throw new Exception("Temporary customization details not found (Temp ID: $tempCustomizationID).");
        }

        // Create a placeholder product in tbl_prod_info for the custom item
        $prodStmt = $pdo->prepare("
            INSERT INTO tbl_prod_info
            (Product_Name, Description, Category, product_type, Price, Stock)
            VALUES (:name, :desc, 'Custom Furniture', 'custom', :price, 0)
        ");
        $customProductName = 'Custom - ' . ($customization['Furniture_Type'] ?: 'Item'); // Use Furniture_Type or fallback
        $customDescription = 'Custom order based on Request ID: ' . $requestID;
        $prodStmt->bindParam(':name', $customProductName, PDO::PARAM_STR);
        $prodStmt->bindParam(':desc', $customDescription, PDO::PARAM_STR);
        $prodStmt->bindParam(':price', $totalPrice, PDO::PARAM_STR); // Use total price from request as placeholder price

        if (!$prodStmt->execute()) {
            throw new Exception("Failed to create placeholder product for custom order. Error: " . implode(", ", $prodStmt->errorInfo()));
        }

        $productIDForProgress = $pdo->lastInsertId(); // Get the ID of the newly created product
        $productNameForProgress = $customProductName; // Use the name we just created

        // Insert customization details into tbl_customizations
        $insertCustomStmt = $pdo->prepare("
            INSERT INTO tbl_customizations
            (User_ID, Furniture_Type, Furniture_Type_Additional_Info, Standard_Size, Desired_Size, Color, Color_Image_URL,
             Color_Additional_Info, Texture, Texture_Image_URL, Texture_Additional_Info, Wood_Type, Wood_Image_URL,
             Wood_Additional_Info, Foam_Type, Foam_Image_URL, Foam_Additional_Info, Cover_Type, Cover_Image_URL,
             Cover_Additional_Info, Design, Design_Image_URL, Design_Additional_Info, Tile_Type, Tile_Image_URL,
             Tile_Additional_Info, Metal_Type, Metal_Image_URL, Metal_Additional_Info, Product_Status, Request_Date,
             Last_Update, Product_ID)
            VALUES
            (:userID, :furnitureType, :furnitureTypeInfo, :standardSize, :desiredSize, :color, :colorImageURL,
             :colorInfo, :texture, :textureImageURL, :textureInfo, :woodType, :woodImageURL, :woodInfo, :foamType,
             :foamImageURL, :foamInfo, :coverType, :coverImageURL, :coverInfo, :design, :designImageURL, :designInfo,
             :tileType, :tileImageURL, :tileInfo, :metalType, :metalImageURL, :metalInfo, 0, NOW(), NOW(), :productID)
        ");
        $insertCustomStmt->bindParam(':userID', $userID, PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':furnitureType', $customization['Furniture_Type'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':furnitureTypeInfo', $customization['Furniture_Type_Additional_Info'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':standardSize', $customization['Standard_Size'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':desiredSize', $customization['Desired_Size'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':color', $customization['Color'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':colorImageURL', $customization['Color_Image_URL'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':colorInfo', $customization['Color_Additional_Info'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':texture', $customization['Texture'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':textureImageURL', $customization['Texture_Image_URL'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':textureInfo', $customization['Texture_Additional_Info'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':woodType', $customization['Wood_Type'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':woodImageURL', $customization['Wood_Image_URL'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':woodInfo', $customization['Wood_Additional_Info'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':foamType', $customization['Foam_Type'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':foamImageURL', $customization['Foam_Image_URL'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':foamInfo', $customization['Foam_Additional_Info'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':coverType', $customization['Cover_Type'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':coverImageURL', $customization['Cover_Image_URL'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':coverInfo', $customization['Cover_Additional_Info'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':design', $customization['Design'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':designImageURL', $customization['Design_Image_URL'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':designInfo', $customization['Design_Additional_Info'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':tileType', $customization['Tile_Type'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':tileImageURL', $customization['Tile_Image_URL'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':tileInfo', $customization['Tile_Additional_Info'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':metalType', $customization['Metal_Type'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':metalImageURL', $customization['Metal_Image_URL'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':metalInfo', $customization['Metal_Additional_Info'], PDO::PARAM_STR);
        $insertCustomStmt->bindParam(':productID', $productIDForProgress, PDO::PARAM_INT);

        if (!$insertCustomStmt->execute()) {
            throw new Exception("Failed to insert customization details into tbl_customizations. Error: " . implode(", ", $insertCustomStmt->errorInfo()));
        }

        // Delete the temporary customization record now that it's processed
        $delStmt = $pdo->prepare("DELETE FROM tbl_customizations_temp WHERE Temp_Customization_ID = :tempID");
        $delStmt->bindParam(':tempID', $tempCustomizationID, PDO::PARAM_INT);
        if (!$delStmt->execute()) {
            error_log("Warning: Failed to delete temporary customization record (Temp ID: $tempCustomizationID). Error: " . implode(", ", $delStmt->errorInfo()));
        }
    } else {
        // --- 2b. Handle Ready-Made or Pre-Order ---
        $productIDForProgress = $orderRequest['Product_ID'];
        if (!$productIDForProgress) {
            throw new Exception("Order request (ID: $requestID) is missing the Product ID for a non-custom order.");
        }

        // Fetch the actual product name
        $nameStmt = $pdo->prepare("SELECT Product_Name FROM tbl_prod_info WHERE Product_ID = :prodID");
        $nameStmt->bindParam(':prodID', $productIDForProgress, PDO::PARAM_INT);
        $nameStmt->execute();
        $productResult = $nameStmt->fetch(PDO::FETCH_ASSOC);
        $productNameForProgress = $productResult['Product_Name'] ?? 'Unknown Product'; // Fallback name
    }

    // --- 3. Insert into tbl_progress ---
    $progressStmt = $pdo->prepare("
        INSERT INTO tbl_progress
        (User_ID, Product_ID, Product_Name, Order_Type, Product_Status, Quantity, Total_Price, Date_Added, LastUpdate)
        VALUES (:userID, :prodID, :prodName, :orderType, 0, :quantity, :totalPrice, NOW(), NOW())
    ");
    $progressStmt->bindParam(':userID', $userID, PDO::PARAM_STR);
    $progressStmt->bindParam(':prodID', $productIDForProgress, PDO::PARAM_INT);
    $progressStmt->bindParam(':prodName', $productNameForProgress, PDO::PARAM_STR);
    $progressStmt->bindParam(':orderType', $orderType, PDO::PARAM_STR);
    $progressStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $progressStmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR); // Bind as string for decimal

    if (!$progressStmt->execute()) {
        throw new Exception("Failed to insert record into tbl_progress. Error: " . implode(", ", $progressStmt->errorInfo()));
    }

    // --- 4. Update tbl_order_request: Mark as Processed and set Payment Status ---
    $updateStmt = $pdo->prepare("
        UPDATE tbl_order_request
        SET Payment_Status = :paymentStatus, Processed = 1
        WHERE Request_ID = :requestID
    ");
    $updateStmt->bindParam(':paymentStatus', $paymentStatus, PDO::PARAM_STR); // The status selected in the form
    $updateStmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);

    if (!$updateStmt->execute()) {
        throw new Exception("Failed to update original order request status. Error: " . implode(", ", $updateStmt->errorInfo()));
    }

    // --- 5. Commit Transaction ---
    $pdo->commit();

    // --- 6. Redirect on Success ---
    header("Location: read-all-request-form.php?success=accepted&id=" . $requestID); // Add success message
    exit();
} catch (PDOException $e) {
    // Handle potential database errors during the transaction
    $pdo->rollBack();
    error_log("Database Error processing request ID $requestID: " . $e->getMessage()); // Log detailed error
    die("Database Error: Failed to process the request. Please check logs or contact support."); // User-friendly message
} catch (Exception $e) {
    // Handle other exceptions (e.g., data not found)
    $pdo->rollBack();
    error_log("Error processing request ID $requestID: " . $e->getMessage()); // Log detailed error
    die("Error processing request: " . $e->getMessage()); // Show specific error
}
?>