<?php
// Include Composer's autoloader
require '../../connection/connection.php';

// Start the session to track the cart
session_start();

// Set up MongoDB connection
$client = new MongoDB\Client("mongodb://localhost:27017"); // Change to your MongoDB URI
$productcollection = $client->GADGETHUB->products; // Replace with your database and collection names
$cartsCollection = $client->GADGETHUB->carts; // Collection for storing cart items

// Get the product ID from the URL (e.g., productdetails.php?id=67553bdb320a7c8f7809b396)
if (!isset($_GET['_id'])) {
    echo "Product not found!";
    exit();
}

// Assuming $client is already connected to MongoDB
$productId = $_GET['_id'];  // Get the product ID from the URL query string
$product = $productcollection->findOne(['_id' => new MongoDB\BSON\ObjectId($productId)]);

if (!$product) {
    // Handle error if product is not found
    echo "Product not found!";
    exit();
}

// Handle Add to Cart form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && $product) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;  // Validate quantity

    if ($quantity > $product['Stock']) {
        echo "Not enough stock!";
        exit();
    }

    // If the user is logged in, add the product to their cart in the database
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        // Check if the product is already in the user's cart
        $existingCartItem = $cartsCollection->findOne([
            'user_id' => $userId,
            'product_id' => new MongoDB\BSON\ObjectId($productId)
        ]);

        if ($existingCartItem) {
            // Update the quantity if the product already exists in the cart
            $cartsCollection->updateOne(
                ['_id' => $existingCartItem['_id']],
                ['$set' => ['quantity' => $existingCartItem['quantity'] + $quantity]]
            );
        } else {
            // Add the product to the cart if it's not already there
            $cartsCollection->insertOne([
                'user_id' => $userId,
                'product_id' => new MongoDB\BSON\ObjectId($productId),
                'name' => $product['Name'], // Store product name
                'price' => $product['Price'], // Store product price
                'quantity' => $quantity,
                'added_at' => new MongoDB\BSON\UTCDateTime()
            ]);
        }

        // Redirect to the cart page
        header("Location: addtocar.php");
        exit();
    } else {
        // If the user is not logged in, store the cart in the session
        $_SESSION['cart'][] = [
            'product_id' => $productId,
            'quantity' => $quantity
        ];

        // Redirect to the cart page
        header("Location: addtocar.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="../css/ProductDetails.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/footer.css">
</head>
<body>
<?php include '../html/navbar.php'?>

<div class="container">
    <div class="product-image">
        <?php if ($product): ?>
            <img src="../../assets/products/img<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>" width="300">
        <?php else: ?>
            <p>Product image not available.</p>
        <?php endif; ?>
    </div>
    <div class="product-info">
            <h1 class="product-name"><?php echo htmlspecialchars($product['Name']); ?></h1>
            <p class="product-price">₱<?php echo number_format($product['Price'], 2); ?></p>
            <p class="product-description"><?php echo nl2br(htmlspecialchars($product['Description'])); ?></p>
            <p class="product-stock"><?php echo $product['Stock']; ?> items in stock</p>
            <form method="post" action="">
                <div id="quantity-container">
                    <button type="button" id="quantity-button-down" onclick="decrementQuantity()">-</button>
                    <input id="quantity" type="number" name="quantity" value="1" min="1" max="<?php echo $product['Stock']; ?>" onchange="updateQuantity()">
                    <button type="button" id="quantity-button-up" onclick="incrementQuantity()">+</button>
                </div>
                <button type="submit" name="add_to_cart" id="add-to-cart">ADD TO CART</button>
                <button type="button" id="add-to-cart" onclick="buyNow()">Buy Now</button>
            </form>
    </div>
</div>

<footer id="footer" class="footer">
    <div class="about">
        <img src="../../assets/img/LOGO1.png" alt="Company Logo">
        <p>"Your Ultimate Destination for Cutting-Edge Technology and Innovation, Where Every Gadget Enthusiast Can Discover, Compare, and Purchase the Latest and Greatest Tech Products, All in One Convenient Place."</p>
        <div class="footer-btn">
            <button>Home</button>
            <button>About</button>
            <button>Contact</button>
            <button>Shop</button>
        </div>
    </div>
</footer>

<script>
function incrementQuantity() {
    var quantity = document.getElementById('quantity');
    var max = quantity.max;
    if (quantity.value < max) {
        quantity.value = parseInt(quantity.value) + 1;
    }
}

function decrementQuantity() {
    var quantity = document.getElementById('quantity');
    if (quantity.value > 1) {
        quantity.value = parseInt(quantity.value) - 1;
    }
}

function updateQuantity() {
    var quantity = document.getElementById('quantity');
    var max = quantity.max;
    if (parseInt(quantity.value) > max) {
        quantity.value = max;
    }
    if (parseInt(quantity.value) < 1) {
        quantity.value = 1;
    }
}

function buyNow() {
    var productId = "<?php echo htmlspecialchars($product['_id']); ?>"; // Get product ID from PHP
    var productName = "<?php echo htmlspecialchars($product['Name']); ?>"; // Get product name from PHP
    var quantity = document.getElementById('quantity').value; // Get the selected quantity
    window.location.href = 'checkout1.php?id=' + productId + '&name=' + encodeURIComponent(productName) + '&quantity=' + quantity; // Redirect to checkout
}
</script>

</body>
</html>
