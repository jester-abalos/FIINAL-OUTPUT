<?php
require '../../connection/connection.php';
session_start();

use MongoDB\Client;

// Function to fetch cart items for a user
function getCartItems($userId) {
    try {
        // Connect to MongoDB
        $client = new Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('GADGETHUB'); // Replace with your database name
        $cartCollection = $db->selectCollection('carts'); // Replace with your collection name

        // Fetch cart items for the specified user and convert the cursor to an array
        return iterator_to_array($cartCollection->find(['user_id' => $userId]));
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

// Check if the user is logged in
$userLoggedIn = isset($_SESSION['user_id']); // Assume that user_id is stored in session when logged in
$userId = $userLoggedIn ? $_SESSION['user_id'] : null;

$cartItems = [];

if ($userLoggedIn && $userId) {
    // Fetch cart items for logged-in user
    $cartItems = getCartItems($userId);
} else {
    // For guest users, retrieve cart items stored in the session
    $cartItems = $_SESSION['cart'] ?? [];
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

    // Redirect back to the cart page after removal
    header("Location: addtocar.php");
    exit;
}

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
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
    <form method="POST" action="addtocar.php" id="cartForm">
    <div class="container"> 

        <!-- Display cart items -->
        <div class="cart-summary">
            <h3>Your Cart</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>₱<?php echo htmlspecialchars($item['price']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>₱<?php echo htmlspecialchars($item['price'] * $item['quantity']); ?></td>
                            <td>
                                <form method="POST" action="addtocar.php" style="display:inline;">
                                    <input type="hidden" name="item_id" value="<?php echo $item['_id']; ?>">
                                    <button type="submit" name="remove_item" class="remove-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p><strong>Total Price: ₱<?php echo htmlspecialchars($total); ?></strong></p>
        </div>

        <div class="bottomoptions">
            <!-- Add additional options like Proceed to Checkout -->
            <?php if (count($cartItems) > 0): ?>
                <!-- Checkout Button -->
                <a href="checkout.php" class="btn checkout-btn">Proceed to Checkout</a>
            <?php else: ?>
                <p>Your cart is empty. Add some products!</p>
            <?php endif; ?>
        </div>
    </div>
    </form>
</body>
</html>
