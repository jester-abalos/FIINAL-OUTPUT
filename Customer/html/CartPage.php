<?php
// Include Composer's autoloader
require '../../connection/connection.php';

// Start the session to track the cart
session_start();

// Set up MongoDB connection
$client = new MongoDB\Client("mongodb://localhost:27017"); // Change to your MongoDB URI
$collection = $client->GADGETHUB->products; // Replace with your database and collection names
$cartsCollection = $client->GADGETHUB->carts; // Collection for storing cart items

// Check if the user is logged in
$userLoggedIn = isset($_SESSION['user_id']);
$userId = $userLoggedIn ? $_SESSION['user_id'] : null;

// Retrieve cart items for logged-in user or session cart for guest users
$cartItems = [];
if ($userLoggedIn) {
    // Get cart items from the database for logged-in user
    $cartItems = iterator_to_array($cartsCollection->find(['user_id' => $userId])); // Convert Cursor to array
} else {
    // For guest users, retrieve cart items stored in the session
    $cartItems = $_SESSION['cart'] ?? [];
}

// Handle cart updates (like updating the quantity of items)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $updatedCart = [];
    foreach ($_POST['product_id'] as $index => $productId) {
        $updatedCart[] = [
            'product_id' => $productId,
            'quantity' => $_POST['quantity'][$index],
        ];
    }

    // For logged-in users, update the cart in MongoDB
    if ($userLoggedIn) {
        foreach ($updatedCart as $item) {
            $cartsCollection->updateOne(
                ['user_id' => $userId, 'product_id' => new MongoDB\BSON\ObjectId($item['product_id'])],
                ['$set' => ['quantity' => $item['quantity']]],
                ['upsert' => true] // If not found, insert the item
            );
        }
    } else {
        // For guest users, store updated cart in session
        $_SESSION['cart'] = $updatedCart;
    }

    // Redirect to prevent form re-submission
    header('Location: cartdev.php');
    exit();
}

// Handle item removal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
    $itemIdToRemove = $_POST['item_id'];

    if ($userLoggedIn && $userId) {
        // Remove the item from the MongoDB cart collection
        $client = new Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('GADGETHUB');
        $cartCollection = $db->selectCollection('carts');
        $cartCollection->deleteOne(['user_id' => $userId, '_id' => new MongoDB\BSON\ObjectId($itemIdToRemove)]);
    } else {
        // For guest users, remove the item from the session cart array
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($itemIdToRemove) {
            return $item['_id'] != $itemIdToRemove;
        });
    }

    // Redirect back to the checkout page after removal
    header("Location: cartpage.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../css/CartPage.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <?php include '../html/navbar.php'; ?>
    <form method="POST" action="checkout.php" id="checkoutForm">
    <div class="container">
        <div class="fieldnames">
            <span></span>
            <p id="productlabel">Product</p>
            <span></span>
            <p id="pricelabel">Unit Price</p>
            <p id="quantitylabel">Quantity</p>
            <p id="actionlabel">Action</p>
        </div>
        <?php
        if (count($cartItems) == 0) {
            echo "<p>Your cart is empty. Add some products!</p>";
        } else {
            foreach ($cartItems as $item) {
                // Fetch the product details from MongoDB using the product_id
                $product = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($item['product_id'])]);

                // If product details are found
                if ($product) {
                    $productImage = isset($product['image']) ? $product['image'] : 'default-product.png'; // Default image if none exists
                    $productName = isset($product['Name']) ? $product['Name'] : 'No name available';  // Assuming 'Name' is the key in your MongoDB collection for product name
                    $productPrice = isset($product['Price']) ? floatval($product['Price']) : 0;  // Assuming 'Price' is the key in your MongoDB collection for product price
                    $productQuantity = isset($item['quantity']) ? intval($item['quantity']) : 1;  // Get the quantity from the cart item

                    echo "
                    <div class='productbox'>
                        <div class='productdetails'>
                            <img src='" . htmlspecialchars("../../assets/products/img" . $productImage, ENT_QUOTES) . "' alt='" . htmlspecialchars($productName, ENT_QUOTES) . "' class='product-image'>
                            <p id='productname'>" . htmlspecialchars($productName, ENT_QUOTES) . "</p>
                            <p id='pricevalue'>₱" . number_format($productPrice, 2) . "</p>
                            <p id='quantity'>" . htmlspecialchars($productQuantity, ENT_QUOTES) . "</p>
                            <form method='POST' action='cartpage.php'>
                                <input type='hidden' name='remove_product_id' value='" . htmlspecialchars($item['product_id'], ENT_QUOTES) . "'>
                                <button type='submit' id='img' name='remove_item' class='delete'><img src='../../assets/img/trash.png' alt='Delete'></button>
                            </form>
                        </div>
                    </div>";
                } else {
                    echo "<p>Product details not available for one or more items in your cart.</p>";
                }
            }
        }
        ?>


        <div class="bottomoptions">
            <input type="checkbox" id="selectall" name="selectall" value="selectall" onclick="toggleSelectAll(this)">
            <p id="selectalllabel">Select All</p>
            <div id="selectedItemsContainer"></div>
            <p id="totalitem">Total: ₱<span id="totalvalue">0.00</span></p>
            <button type="submit" id="add_to_cart" name="add_to_cart" disabled>Proceed to Check Out</button>
        </div>
    </form>
</div>
<script>
    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll("input[name='selected_items[]']");
        let total = 0;

        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;

            if (checkbox.checked) {
                total += parseFloat(checkbox.dataset.price || 0) * parseInt(checkbox.dataset.quantity || 1);
            }
        });

        const formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        document.getElementById("totalvalue").innerText = formattedTotal;
    }

    function updateTotal() {
        const checkboxes = document.querySelectorAll("input[name='selected_items[]']");
        let total = 0;

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const price = parseFloat(checkbox.dataset.price || 0);
                const quantity = parseInt(checkbox.dataset.quantity || 1);
                total += price * quantity;
            }
        });

        const formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        document.getElementById("totalvalue").innerText = formattedTotal;
        const checkoutButton = document.getElementById("add_to_cart");
        if (total > 0) {
            checkoutButton.disabled = false;
            checkoutButton.style.cursor = "pointer";
            checkoutButton.innerText = "Check Out";
        } else {
            checkoutButton.disabled = true;
            checkoutButton.innerText = "Add Products";
        }
    }
</script>

</body>
</html>
