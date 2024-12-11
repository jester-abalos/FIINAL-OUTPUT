<?php
require '../../connection/connection.php';
session_start(); // Start the session to access user data

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Fetch the user ID from the session
$userId = $_SESSION['user_id'];

// Fetch the order details from the database
$ordersCollection = $client->GADGETHUB->orders;
$order = $ordersCollection->findOne(['user_id' => $userId, 'status' => 'pending']); // Get the latest pending order

if (!$order) {
    echo "<p>No order found. Please place an order first.</p>";
    exit();
}

// Extract order details
$orderNumber = (string)$order->_id; // MongoDB ObjectId as a string
$items = $order['items']; // Array of items
$totalCost = $order['total_amount']; // Total amount including shipping
$estimatedDelivery = date('F j, Y', strtotime('+7 days')); // Example estimated delivery date (7 days after order)

// Update the order status to 'confirmed' after the user views the confirmation page
$ordersCollection->updateOne(
    ['_id' => $order->_id],
    ['$set' => ['status' => 'confirmed']] // Mark as confirmed after viewing
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="../css/confirmation.css">
</head>
<body>
    <div class="confirmation-container">
        <h1>Thank You for Your Order!</h1>
        <p>Your order has been successfully placed.</p>
        <img src="../../assets/img/barcode.png" alt="Barcode" class="barcode">
        <div class="order-summary">
            <h2>Order Summary</h2>
            <p><strong>Order Number:</strong> <?php echo htmlspecialchars($orderNumber); ?></p>
            <p><strong>Estimated Delivery:</strong> <?php echo htmlspecialchars($estimatedDelivery); ?></p>
        </div>
        <div class="item-list">
            <h3>Purchased Items:</h3>
            <ul>
                <?php foreach ($items as $item): ?>
                    <li>
                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                        <span class="item-quantity">x<?php echo htmlspecialchars($item['quantity']); ?></span>
                        <span class="item-price">₱<?php echo number_format($item['price'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="total-cost">
            <h3>Total Cost: ₱<?php echo number_format($totalCost, 2); ?></h3>
        </div>
        <button onclick="window.location.href='dashboard.php'" class="btn-continue">Continue Shopping</button>
    </div>
</body>
</html>
