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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch form data
    $productName = $_POST['Product_Name'];
    $description = $_POST['Description'];
    $category = $_POST['Category'];
    $sizes = $_POST['SizesText']; // Use the text of the selected option
    $color = $_POST['Color'];
    $stock = $_POST['Stock'];
    $assemblyRequired = $_POST['Assembly_Required'];
    $price = $_POST['Price'];
    $sold = $_POST['Sold'];

    // Handle image uploads
    $imageURLs = [];
    if (!empty($_FILES['ImageURLs']['name'][0])) {
        $uploadDir = '../uploads/product/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        foreach ($_FILES['ImageURLs']['name'] as $key => $imageName) {
            $imageTmpName = $_FILES['ImageURLs']['tmp_name'][$key];
            $imagePath = $uploadDir . basename($imageName);
            if (move_uploaded_file($imageTmpName, $imagePath)) {
                $imageURLs[] = $imagePath;
            } else {
                echo "Failed to upload image: " . $imageName . "<br>";
            }
        }
    }
    $imageURLs = implode(',', $imageURLs);

    // Handle GLB file upload
    $glbFileURL = NULL;
    if (!empty($_FILES['GLB_File_URL']['name'])) {
        $glbUploadDir = '../uploads/product/3d/';
        if (!is_dir($glbUploadDir)) {
            mkdir($glbUploadDir, 0777, true);
        }
        $glbFileName = basename($_FILES['GLB_File_URL']['name']);
        $glbFilePath = $glbUploadDir . $glbFileName;
        if (move_uploaded_file($_FILES['GLB_File_URL']['tmp_name'], $glbFilePath)) {
            $glbFileURL = $glbFilePath;
        } else {
            echo "Failed to upload GLB file: " . $glbFileName . "<br>";
        }
    }

    // Insert product data into the database
    $stmt = $pdo->prepare("INSERT INTO tbl_prod_info (Product_Name, Description, Category, Sizes, Color, Stock, Assembly_Required, ImageURL, Price, Sold, GLB_File_URL) VALUES (:productName, :description, :category, :sizes, :color, :stock, :assemblyRequired, :imageURLs, :price, :sold, :glbFileURL)");
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

    if ($stmt->execute()) {
        // Redirect to the read-all-product-form.php page after a successful insertion
        header("Location: read-all-product-form.php");
        exit;
    } else {
        echo "Error adding product: " . implode(" ", $stmt->errorInfo());
    }
}
?>
