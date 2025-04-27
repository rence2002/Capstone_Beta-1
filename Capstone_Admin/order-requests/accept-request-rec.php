<?php
session_start();
include '../config/database.php'; // Database connection

// --- Authentication ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// --- Input Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: read-all-request-form.php?error=invalid_method");
    exit();
}

$requestID = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$paymentStatus = isset($_POST['payment_status']) ? trim($_POST['payment_status']) : null;

if (!$requestID) {
    header("Location: read-all-request-form.php?error=missing_id");
    exit();
}

if (empty($paymentStatus)) {
    header("Location: read-all-request-form.php?error=missing_payment&id=" . $requestID);
    exit();
}

// Validate against expected payment statuses (ensure these match your DB constraints/enum)
$validPaymentStatuses = ['downpayment_paid', 'fully_paid'];
if (!in_array($paymentStatus, $validPaymentStatuses)) {
    header("Location: read-all-request-form.php?error=invalid_payment&id=" . $requestID);
    exit();
}

// --- Database Operations (Transaction) ---
try {
    $pdo->beginTransaction();

    // 1. Fetch and Lock the Unprocessed Order Request
    $stmt = $pdo->prepare("SELECT * FROM tbl_order_request WHERE Request_ID = :requestID AND Processed = 0 FOR UPDATE");
    $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
    $stmt->execute();
    $orderRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orderRequest) {
        $pdo->rollBack(); // Release lock
        header("Location: read-all-request-form.php?warning=notfound_or_processed&id=" . $requestID);
        exit();
    }

    // 2. Prepare Common Data
    $userID = $orderRequest['User_ID'];
    $orderType = $orderRequest['Order_Type'];
    $quantity = $orderRequest['Quantity'];
    $totalPrice = $orderRequest['Total_Price'];
    $productIDForProgress = null;
    $productNameForProgress = null;

    // 3. Process Based on Order Type
    if ($orderType == 'custom') {
        // --- Handle Custom Order ---
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

        // Create a placeholder product for the custom item
        $prodStmt = $pdo->prepare("
            INSERT INTO tbl_prod_info (Product_Name, Description, Category, product_type, Price, Stock)
            VALUES (:name, :desc, :category, 'custom', :price, 0)
        ");
        $customCategory = $customization['Furniture_Type'] ?: 'Custom Furniture';
        $customProductName = 'Custom - ' . $customCategory . ' (Req ID: ' . $requestID . ')';
        $customDescription = 'Custom order based on Request ID: ' . $requestID;
        $prodStmt->bindParam(':name', $customProductName, PDO::PARAM_STR);
        $prodStmt->bindParam(':desc', $customDescription, PDO::PARAM_STR);
        $prodStmt->bindParam(':category', $customCategory, PDO::PARAM_STR);
        $prodStmt->bindParam(':price', $totalPrice, PDO::PARAM_STR); // Use total price from request

        if (!$prodStmt->execute()) {
            throw new Exception("Failed to create placeholder product. Error: " . implode(", ", $prodStmt->errorInfo()));
        }
        $productIDForProgress = $pdo->lastInsertId();
        $productNameForProgress = $customProductName;

        // Insert final customization details, linking to the new product
        $insertCustomStmt = $pdo->prepare("
            INSERT INTO tbl_customizations
            (User_ID, Furniture_Type, Furniture_Type_Additional_Info, Standard_Size, Desired_Size, Color, Color_Image_URL,
             Color_Additional_Info, Texture, Texture_Image_URL, Texture_Additional_Info, Wood_Type, Wood_Image_URL,
             Wood_Additional_Info, Foam_Type, Foam_Image_URL, Foam_Additional_Info, Cover_Type, Cover_Image_URL,
             Cover_Additional_Info, Design, Design_Image_URL, Design_Additional_Info, Tile_Type, Tile_Image_URL,
             Tile_Additional_Info, Metal_Type, Metal_Image_URL, Metal_Additional_Info, Product_Status, Request_Date,
             Last_Update, Product_ID) /* Link to product */
            VALUES
            (:userID, :furnitureType, :furnitureTypeInfo, :standardSize, :desiredSize, :color, :colorImageURL,
             :colorInfo, :texture, :textureImageURL, :textureInfo, :woodType, :woodImageURL, :woodInfo, :foamType,
             :foamImageURL, :foamInfo, :coverType, :coverImageURL, :coverInfo, :design, :designImageURL, :designInfo,
             :tileType, :tileImageURL, :tileInfo, :metalType, :metalImageURL, :metalInfo, 0, NOW(), NOW(), :productID) /* Bind product ID */
        ");
        // Bind all customization parameters (ensure these match your table and the fetched $customization array)
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
        $insertCustomStmt->bindParam(':productID', $productIDForProgress, PDO::PARAM_INT); // Link to the new product

        if (!$insertCustomStmt->execute()) {
            throw new Exception("Failed to insert customization details. Error: " . implode(", ", $insertCustomStmt->errorInfo()));
        }

        // Delete the temporary customization record
        $delStmt = $pdo->prepare("DELETE FROM tbl_customizations_temp WHERE Temp_Customization_ID = :tempID");
        $delStmt->bindParam(':tempID', $tempCustomizationID, PDO::PARAM_INT);
        if (!$delStmt->execute()) {
            // Log warning but don't stop the transaction
            error_log("Warning: Failed to delete temporary customization record (Temp ID: $tempCustomizationID). Error: " . implode(", ", $delStmt->errorInfo()));
        }

    } else {
        // --- Handle Ready-Made or Pre-Order ---
        $productIDForProgress = $orderRequest['Product_ID'];
        if (!$productIDForProgress) {
            throw new Exception("Order request (ID: $requestID) is missing Product ID for non-custom order.");
        }

        // Fetch the product details and current stock
        $prodStmt = $pdo->prepare("SELECT Product_Name, Stock FROM tbl_prod_info WHERE Product_ID = :prodID FOR UPDATE");
        $prodStmt->bindParam(':prodID', $productIDForProgress, PDO::PARAM_INT);
        $prodStmt->execute();
        $productResult = $prodStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$productResult) {
            throw new Exception("Product details not found for Product ID: $productIDForProgress (Request ID: $requestID).");
        }

        $currentStock = (int)$productResult['Stock'];
        $productNameForProgress = $productResult['Product_Name'];

        // Check if there's enough stock
        if ($currentStock < $quantity) {
            throw new Exception("Insufficient stock for Product ID: $productIDForProgress. Available: $currentStock, Requested: $quantity");
        }

        // Update the product stock
        $newStock = $currentStock - $quantity;
        $updateStockStmt = $pdo->prepare("UPDATE tbl_prod_info SET Stock = :newStock WHERE Product_ID = :prodID");
        $updateStockStmt->bindParam(':newStock', $newStock, PDO::PARAM_INT);
        $updateStockStmt->bindParam(':prodID', $productIDForProgress, PDO::PARAM_INT);
        
        if (!$updateStockStmt->execute()) {
            throw new Exception("Failed to update product stock. Error: " . implode(", ", $updateStockStmt->errorInfo()));
        }

        // Insert into the dedicated table for ready-made orders
        $readyMadeStmt = $pdo->prepare("
            INSERT INTO tbl_ready_made_orders
            (User_ID, Product_ID, Quantity, Total_Price, Payment_Status, Order_Date)
            VALUES (:userID, :prodID, :quantity, :totalPrice, :paymentStatus, NOW())
        ");
        $readyMadeStmt->bindParam(':userID', $userID, PDO::PARAM_STR);
        $readyMadeStmt->bindParam(':prodID', $productIDForProgress, PDO::PARAM_INT);
        $readyMadeStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $readyMadeStmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);
        $readyMadeStmt->bindParam(':paymentStatus', $paymentStatus, PDO::PARAM_STR); // Status from form
        // REMOVED: Binding for :requestID is no longer needed.
        // $readyMadeStmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);

        if (!$readyMadeStmt->execute()) {
            // The error message will now be more accurate if it fails for other reasons
            throw new Exception("Failed to insert into tbl_ready_made_orders. Error: " . implode(", ", $readyMadeStmt->errorInfo()));
        }
    }

    // 4. Insert into tbl_progress (Tracks all active orders being processed/prepared)
    // Initial status '0' might mean 'Pending' or 'Processing Started' - adjust if needed
    $progressStmt = $pdo->prepare("
        INSERT INTO tbl_progress
        (User_ID, Product_ID, Product_Name, Order_Type, Product_Status, Quantity, Total_Price, Date_Added, LastUpdate)
        VALUES (:userID, :prodID, :prodName, :orderType, 0, :quantity, :totalPrice, NOW(), NOW())
    ");
    $progressStmt->bindParam(':userID', $userID, PDO::PARAM_STR);
    $progressStmt->bindParam(':prodID', $productIDForProgress, PDO::PARAM_INT); // Set in both branches above
    $progressStmt->bindParam(':prodName', $productNameForProgress, PDO::PARAM_STR); // Set in both branches above
    $progressStmt->bindParam(':orderType', $orderType, PDO::PARAM_STR);
    $progressStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $progressStmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);

    if (!$progressStmt->execute()) {
        throw new Exception("Failed to insert into tbl_progress. Error: " . implode(", ", $progressStmt->errorInfo()));
    }

    // 5. Update Original Order Request: Mark as Processed, Set Final Payment Status
    $updateStmt = $pdo->prepare("
        UPDATE tbl_order_request
        SET Payment_Status = :paymentStatus, Processed = 1
        WHERE Request_ID = :requestID
    ");
    $updateStmt->bindParam(':paymentStatus', $paymentStatus, PDO::PARAM_STR); // Status from form
    $updateStmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);

    if (!$updateStmt->execute()) {
         throw new Exception("Failed to update original order request status. Error: " . implode(", ", $updateStmt->errorInfo()));
    }
    // Optional: Check rowCount, but the FOR UPDATE lock should prevent issues here if the initial fetch succeeded.
    // if ($updateStmt->rowCount() == 0) {
    //      throw new Exception("Failed to update original order request status (ID: $requestID) - Row count was zero unexpectedly.");
    // }

    // 6. Commit Transaction
    $pdo->commit();

    // --- Success Redirect ---
    header("Location: read-all-request-form.php?success=accepted&id=" . $requestID);
    exit();

} catch (PDOException $e) {
    // --- Database Error Handling ---
    $pdo->rollBack();
    error_log("Database Error processing request ID $requestID: " . $e->getMessage());
    header("Location: read-all-request-form.php?error=db_error&id=" . $requestID);
    exit();
} catch (Exception $e) {
    // --- General Error Handling ---
    $pdo->rollBack();
    error_log("Error processing request ID $requestID: " . $e->getMessage());
    $errorMessage = urlencode($e->getMessage()); // Encode for URL safety if needed
    header("Location: read-all-request-form.php?error=processing_failed&msg=" . $errorMessage . "&id=" . $requestID);
    exit();
}
?>
