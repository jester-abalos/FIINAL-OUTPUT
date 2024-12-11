<?php
require '../../vendor/autoload.php';
$client = new MongoDB\Client("mongodb://localhost:27017");
$collectionproducts = $client->GADGETHUB->products;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $productName = $_POST['productNameInput'];
    $productDescription = $_POST['productDescInput'];
    $productCategory = $_POST['productCategoryInput'];
    $productPrice = $_POST['productPriceInput'];
    $productStock =  100;  
    $productStockQuantity = $productStock;
    $productCount = 0;

    // Handle image upload
    $image = $_FILES['image'];
    if ($image && isset($image['name'])) {
        $imageName = $image['name'];
        $imageTmpName = $image['tmp_name'];
        $imageSize = $image['size'];
        $imageError = $image['error'];
        $imageExt = explode('.', $imageName);
        $imageActualExt = strtolower(end($imageExt));
        $allowed = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($imageActualExt, $allowed)) {
            if ($imageError === 0) {
                if ($imageSize < 5000000) {
                    $imageNameNew = uniqid('', true) . "." . $imageActualExt;
                    $destination = "../../assets/products/img" . $imageNameNew;
                    // Check if the path is correct
                    echo $destination;  // Debug path
                    if (move_uploaded_file($imageTmpName, $destination)) {
                        // Insert the product data into MongoDB
                        $insertResult = $collectionproducts->insertOne([
                            'Name' => $productName,
                            'Description' => $productDescription,
                            'Category' => $productCategory,
                            'Stock' => $productStock, 
                            'Price' => $productPrice,
                            'Quantity' => $productStockQuantity,  
                            'image' => $imageNameNew,
                            'Count' => $productCount
                        ]);

                        if ($insertResult) {
                            echo "<script>alert('Product added successfully!');</script>";
                            echo "<script>window.location.href = 'update.php';</script>";
                            exit;
                        } else {
                            echo "<script>alert('Error inserting product into database.');</script>";
                        }
                    } else {
                        echo "<script>alert('Error uploading image');</script>";
                    }
                } else {
                    echo "<script>alert('Image size should be less than 5MB');</script>";
                }
            } else {
                echo "<script>alert('Error uploading image');</script>";
            }
        } else {
            echo "<script>alert('Invalid image type. Only jpg, jpeg, png, and gif are allowed');</script>";
        }
    } else {
        echo "<script>alert('Please upload an image');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Product</title>
    <!-- Link to your external CSS file -->
    <link rel="stylesheet" href="../css/info.css">
    <link rel="stylesheet" href="../css/navbarside.css">
     
</head>
<body>
    
    
<div class="Container">
    <!-- Top Navigation -->
    <nav class="nav-top">
        <div class="menu-toggle" id="menu-toggle-button">
            <img src="../../admin/image/navbarside.png" alt="Menu">
        </div>
        <div class="search-notification">
            <img src="../image/Icons/search-icon.png" alt="Search">
            <img src="../image/Icons/notifications-icon.png" alt="Notifications">
        </div>
        <div class="Admin">
            <h6>Admin <img src="../image/Icons/arrow_down-icon.png" alt="Dropdown"></h6>
        </div>
    </nav>
    <!-- Sidebar Navigation -->
    <nav class="nav-side" id="sidebar">
        <img src="../../admin/image/Logo-Admin.png" alt="Logo">
        <ul>
            <li ><a href="./dashboard.php">Dashboard</a></li>
            <li class="active"><a href="./update.php">Add Product</a></li>
            <li ><a href="./order-list.php">Order List</a></li>
        </ul>
    </nav>

    <div class="content">
        <div class="Header-Container">
            <h1>Add Product</h1>
            <div class="directory">
                <p>Home  >   Product</p>
            </div>
        </div>


        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="content">
                <div class="Column1">
                    <h4>Product Name</h4>
                    <input type="text" name="productNameInput" placeholder="Product Name">
                    <h4>Product Description</h4>
                    <input type="text" name="productDescInput" placeholder="Product Description" style="height: 150px;"></input>
                    <h4>Category</h4>
                    <input type="text" name="productCategoryInput" placeholder="Category">
                    <h4>Brand Name</h4>
                    <input type="text" name="productStockInput" placeholder="Brand Name">
                    <div class="SKU-Stock">
                        <div class="Stock">
                            <h4>Stock Quantity</h4>
                            <input type="text" name="productStockInput" placeholder="1258">
                        </div>
                    </div>
                    <div class="Reg_Price-Sales_Price">
                        <div class="Reg-Price">
                            <h4>Regular Price</h4>
                            <input type="number" name="productPriceInput" placeholder="₱0,000.00">
                        </div>
                    </div>
                    <h4>Tags</h4>
                    <input type="text" style="height: 150px;">
                </div>

                <div class="Column2">
                    <h4>Product Gallery</h4>
                    <input type="file" name="image">
                    <div class="Buttons">
                        <button type="submit">Add Product</button>
                        <button type="button" style="background-color: #606060; border: none; color: #FFFFFF;">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
 <script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleButton = document.getElementById('menu-toggle-button');
        const sidebar = document.getElementById('sidebar');

        toggleButton.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    });
</script> 
</body>
</html>
