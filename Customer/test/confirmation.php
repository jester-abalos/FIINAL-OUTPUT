<?php
session_start();
require '../../connection/connection.php';
use MongoDB\Client;

// Check if the user is logged in
$userLoggedIn = isset($_SESSION['user_id']); // Assume that user_id is stored in session when logged in
$userId = $userLoggedIn ? $_SESSION['user_id'] : null;

if (!$userLoggedIn || !$userId) {
    // Redirect to the login page if the user is not logged in
    header("Location: login.php");
    exit;
}

// Fetch the last order placed by the user from the "orders" collection
$client = new Client("mongodb://localhost:27017");
$db = $client->selectDatabase('GADGETHUB');
$ordersCollection = $db->selectCollection('orders');

// Fetch the latest order for the logged-in user
$order = $ordersCollection->findOne(
    ['user_id' => $userId],
    ['sort' => ['order_date' => -1]] // Sort to get the most recent order
);

if (!$order) {
    // If no order is found, redirect back to cart page
    header("Location: cart_page.php");
    exit;
}

// Extract order details
$orderId = (string) $order['_id'];
$userName = $order['name'];
$userEmail = $order['email'];
$orderDate = $order['order_date']->toDateTime()->format('Y-m-d H:i:s');
$total = $order['total'];
$cartItems = $order['cart_items'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../css/CartPage.css">
    <link rel="stylesheet" href="../css/navbar.css">

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../html/navbar.php'; ?>

    <div class="container">
        <h2>Order Confirmation</h2>

        <div class="order-summary">
            <h3>Thank you for your order, <?php echo htmlspecialchars($userName); ?>!</h3>
            <p>Your order has been successfully placed. Below are the details:</p>

            <h4>Order ID: <?php echo htmlspecialchars($orderId); ?></h4>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($orderDate); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>

            <h5>Order Items:</h5>
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

            <p>We will notify you once your order is processed and shipped. Thank you for shopping with us!</p>
        </div>

        <!-- Optionally, you can add a button to return to the homepage or view orders -->
        <a href="index.php" class="btn btn-primary">Return to Homepage</a>
        <a href="view_orders.php" class="btn btn-secondary">View My Orders</a>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
