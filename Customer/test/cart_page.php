<?php
require '../../connection/connection.php';
session_start();

use MongoDB\Client;

// Initialize MongoDB client and select the database
$client = new Client("mongodb://localhost:27017");
$db = $client->selectDatabase('GADGETHUB'); // Replace with your actual database name

// Function to fetch cart items for a user
function getCartItems($userId) {
    global $db;
    try {
        $cartCollection = $db->selectCollection('carts'); // Replace with your collection name
        return $cartCollection->find(['user_id' => $userId]);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

// Check if the user is logged in
$userLoggedIn = isset($_SESSION['user_id']);
$userId = $userLoggedIn ? $_SESSION['user_id'] : null;

$cartItems = [];

if ($userLoggedIn && $userId) {
    // Fetch cart items for logged-in user
    $cartItems = getCartItems($userId);
} else {
    // For guest users, retrieve cart items stored in the session
    $cartItems = $_SESSION['cart'] ?? [];
}

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user details from the form (ensure the fields are set)
    $userName = isset($_POST['name']) ? $_POST['name'] : null;
    $userEmail = isset($_POST['email']) ? $_POST['email'] : null;
    $userAddress = isset($_POST['address']) ? $_POST['address'] : null;
    $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : null;

    // Check if any required fields are missing
    if (!$userName || !$userEmail || !$userAddress || !$paymentMethod) {
        echo "Please fill in all required fields.";
        exit;
    }

    // Save the order to the database
    $order = [
        'user_id' => $userId,
        'cart_items' => $cartItems,
        'total' => $total,
        'name' => $userName,
        'email' => $userEmail,
        'address' => $userAddress,
        'payment_method' => $paymentMethod,
        'order_date' => new MongoDB\BSON\UTCDateTime((new DateTime())->getTimestamp() * 1000)
    ];

    // Insert the order into the 'orders' collection
    $ordersCollection = $db->selectCollection('orders');
    $ordersCollection->insertOne($order);

    // Clear the cart
    $_SESSION['cart'] = [];

    // Redirect to the confirmation page
    header("Location: confirmation.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../css/CartPage.css">
    <link rel="stylesheet" href="../css/navbar.css">

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../html/navbar.php'; ?>

    <div class="container">
        <h2>Checkout</h2>

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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>₱<?php echo htmlspecialchars($item['price']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>₱<?php echo htmlspecialchars($item['price'] * $item['quantity']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p><strong>Total Price: ₱<?php echo htmlspecialchars($total); ?></strong></p>
        </div>

        <!-- Checkout form -->
        <form method="POST" action="checkout.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="address">Shipping Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
            </div>

            <!-- Payment options -->
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select class="form-control" id="payment_method" name="payment_method" required>
                    <option value="COD">Cash on Delivery</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="PayPal">PayPal</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Proceed with Order</button>
        </form>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
