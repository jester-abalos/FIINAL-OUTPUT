<?php
require '../../connection/connection.php';
session_start(); // Start session to access user data

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Fetch the user ID from the session
$userId = $_SESSION['user_id'];

// Fetch user details
$userCollection = $client->GADGETHUB->users;
$user = $userCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);

if (!$user) {
    echo "<p>User not found. Please log in again.</p>";
    exit();
}

// Fetch orders for the user
$ordersCollection = $client->GADGETHUB->orders;
$orders = $ordersCollection->find(['user_id' => $userId]);

// Handle cancel request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = new MongoDB\BSON\ObjectId($_POST['order_id']);
    
    // Update order status to 'Cancelled'
    $result = $ordersCollection->updateOne(
        ['_id' => $orderId],
        ['$set' => ['status' => 'Cancelled']]
    );

    // Check if the update was successful
    if ($result->getModifiedCount() > 0) {
        $message = "Order cancelled successfully.";
    } else {
        $message = "Failed to cancel order.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Page</title>
    <link rel="stylesheet" href="../css/OrdersPage.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>

<body>
<?php include '../html/navbar.php'; ?>
<div class="container">
    <div class="menu">
        <div class="useraccount">
            <div id="profilepic"><img src="../../assets/img/profilepic.png" alt="" /></div>
            <div class="profilename"><?php echo htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <button id="myaccount" onclick="location.href='manageprofile.php'">My Account</button>
        <span></span>
        <button id="myorders" onclick="location.href='orderspage.php'">My Orders</button>
        <span></span>
        <button id="notifications" onclick="location.href='notificationpage.php'">Notifications</button>
        <span></span>
        <button id="logout" onclick="location.href='Logout.php'">Log Out</button>
    </div>

        <div class="ordercontainer">
            <div class="fieldnamebox">
                <div class="fieldnames">
                    <p id="productlabel">Product</p>
                    <p id="pricelabel">Unit Price</p>
                    <p id="quantitylabel">Quantity</p>
                    <p id="totalpricelabel">Total Price</p>
                    <p id="status">Status</p>
                    <p id="actionlabel">Action</p>
                </div>
            </div>
            <div class="productlist">
                <?php 
                foreach ($orders as $order): 
                    // Ensure cart_items exists and is iterable, convert BSONArray to a PHP array if needed
                    $cartItems = isset($order['cart_items']) && is_iterable($order['cart_items']) ? iterator_to_array($order['cart_items']) : [];
                    $totalAmount = 0;  // Initialize total amount for this order
                    $productsOutput = '';  // To accumulate product names for display
                    $unitPrice = 0;  // Initialize unit price to 0

                    // Only proceed if there are items in the order
                    if (count($cartItems) > 0) {
                        // Get unit price from the first item (assuming all items in the cart have the same price)
                        $unitPrice = $cartItems[0]['price'];

                        foreach ($cartItems as $item) {
                            $totalAmount += (int)$item['price'] * (int)$item['quantity'];
                            $productsOutput .= $item['name'] . '<br>';  // Accumulate product names
                        }

                        // Format total price with currency symbol
                        $formattedTotal = '₱' . number_format($totalAmount, 2);

                        // Format unit price if available
                        $formattedUnitPrice = '₱' . number_format((float)$unitPrice, 2);
                    } else {
                        $formattedTotal = '₱0.00';
                        $formattedUnitPrice = '₱0.00';
                    }

                    // Extract order date
                    $orderDate = isset($order['order_date']) && $order['order_date'] instanceof MongoDB\BSON\UTCDateTime 
                                 ? $order['order_date']->toDateTime()->format('M jS, Y') 
                                 : 'Unknown Date';

                    // Get status and shipping address
                    $status = isset($order['status']) ? ucfirst($order['status']) : 'Unknown';
                    $shippingAddress = isset($order['shipping_address']) ? $order['shipping_address'] : 'Unknown Address';
                ?>
                <div class="Order-Info">
                    <div class="productdetails">
                        <p class="product"><?php echo $productsOutput; ?></p>
                        <p class="price"><?php echo $formattedUnitPrice; ?></p>
                        <p class="quantity"><?php echo array_sum(array_map(fn($item) => (int)$item['quantity'], $cartItems)); ?></p>
                        <p class="total-price"><?php echo $formattedTotal; ?></p>
                        <p class="status" id="statusvalue"><?php echo $status; ?></p>
                        <form method="POST" action="orderspage.php">
                            <input type="hidden" name="order_id" value="<?php echo $order['_id']; ?>" />
                            <?php if ($status !== 'Cancelled'): ?>
                                <button type="submit">Cancel Order</button>
                            <?php else: ?>
                                <button type="button" disabled>Order Cancelled</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function cancelOrder(orderId) {
            if (confirm("Are you sure you want to cancel this order?")) {
                fetch('cancelOrder.php', {
                    method: 'POST',
                    body: JSON.stringify({ order_id: orderId }),
                    headers: {
                        'Content-Type': 'application/json',
                    }
                }).then(response => {
                    if (response.ok) {
                        location.reload(); // Reload the page to update the order status
                    } else {
                        alert("Failed to cancel order.");
                    }
                });
            }
        }
    </script>
</body>
</html>
