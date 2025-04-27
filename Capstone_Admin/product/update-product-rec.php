<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Check if the admin's ID is stored in session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

// Fetch admin data from the database
$adminId = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT First_Name, PicPath FROM tbl_admin_info WHERE Admin_ID = ?");
$stmt->execute(array($adminId));
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if admin data is fetched
if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get product input from the web form
    $productID = $_POST['txtID']; 
    $productName = $_POST['txtProdName']; 
    $description = $_POST['txtDescription']; 
    $category = $_POST['txtCategory'];
    $sizes = $_POST['txtSizes'];
    $color = $_POST['txtColor'];
    $stock = $_POST['txtStock'];
    $sold = $_POST['txtSold'];
    $assemblyRequired = $_POST['txtAssemblyRequired'];
    $price = $_POST['txtPrice'];

    // Fetch existing product data
    $stmt = $pdo->prepare("SELECT ImageURL, GLB_File_URL FROM tbl_prod_info WHERE Product_ID = ?");
    $stmt->execute(array($productID));
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle image uploads
    $imageURLs = [];
    if (!empty($_FILES['ImageURLs']['name'][0])) {
        $uploadDir = 'C:/xampp/htdocs/Capstone_Beta/uploads/product/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        foreach ($_FILES['ImageURLs']['name'] as $key => $imageName) {
            $imageTmpName = $_FILES['ImageURLs']['tmp_name'][$key];
            $imagePath = $uploadDir . basename($imageName);
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($imageTmpName);
            
            if (!in_array($fileType, $allowedTypes)) {
                echo "Invalid file type for image: " . $imageName . ". Only JPG, PNG, and GIF files are allowed.<br>";
                continue;
            }
            
            if (move_uploaded_file($imageTmpName, $imagePath)) {
                // Store relative path for database
                $relativePath = 'uploads/product/images/' . basename($imageName);
                $imageURLs[] = $relativePath;
            } else {
                echo "Failed to upload image: " . $imageName . "<br>";
            }
        }
    }

    // If no new images were uploaded, keep the existing ones
    if (empty($imageURLs)) {
        $imageURLs = explode(',', $product['ImageURL']);
    } else {
        // Combine new images with existing ones
        $existingImages = explode(',', $product['ImageURL']);
        $imageURLs = array_merge($existingImages, $imageURLs);
        // Remove any empty values and duplicates
        $imageURLs = array_filter(array_unique($imageURLs));
    }
    $imageURLs = implode(',', $imageURLs);

    // Handle GLB file upload
    $glbFileURL = $product['GLB_File_URL'];
    if (!empty($_FILES['GLB_File_URL']['name'])) {
        $glbUploadDir = 'C:/xampp/htdocs/Capstone_Beta/uploads/product/3d/';
        if (!is_dir($glbUploadDir)) {
            mkdir($glbUploadDir, 0777, true);
        }
        
        $glbFileName = basename($_FILES['GLB_File_URL']['name']);
        $glbFilePath = $glbUploadDir . $glbFileName;
        
        // Validate GLB file type
        $allowedGLBTypes = ['model/gltf-binary', 'application/octet-stream'];
        $fileType = mime_content_type($_FILES['GLB_File_URL']['tmp_name']);
        
        if (!in_array($fileType, $allowedGLBTypes)) {
            echo "Invalid file type for GLB file. Only .glb files are allowed.<br>";
        } else if (move_uploaded_file($_FILES['GLB_File_URL']['tmp_name'], $glbFilePath)) {
            // Store relative path for database
            $glbFileURL = 'uploads/product/3d/' . $glbFileName;
        } else {
            echo "Failed to upload GLB file: " . $glbFileName . "<br>";
        }
    }

    // Update product data in the database
    try {
        $stmt = $pdo->prepare("UPDATE tbl_prod_info SET 
            Product_Name = :productName, 
            Description = :description, 
            Category = :category, 
            Sizes = :sizes, 
            Color = :color, 
            Stock = :stock, 
            Assembly_Required = :assemblyRequired, 
            ImageURL = :imageURLs, 
            Price = :price, 
            Sold = :sold, 
            GLB_File_URL = :glbFileURL,
            LastUpdate = CURRENT_TIMESTAMP
            WHERE Product_ID = :productID");
        
        $stmt->bindParam(':productName', $productName);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':sizes', $sizes);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':assemblyRequired', $assemblyRequired);
        $stmt->bindParam(':imageURLs', $imageURLs);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':sold', $sold);
        $stmt->bindParam(':glbFileURL', $glbFileURL);
        $stmt->bindParam(':productID', $productID);

        if ($stmt->execute()) {
            // Log successful update
            error_log("Product updated successfully: ID " . $productID);
            
            // Redirect to the read-all-product-form.php page after a successful update
            header("Location: read-all-product-form.php");
            exit;
        } else {
            // Log the error
            error_log("Error updating product: " . implode(" ", $stmt->errorInfo()));
            echo "Error updating product. Please try again.";
        }
    } catch (PDOException $e) {
        // Log the exception
        error_log("Database error: " . $e->getMessage());
        echo "Database error occurred. Please try again.";
    }
}
?>
