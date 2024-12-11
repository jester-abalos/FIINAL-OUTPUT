<?php
require '../../connection/connection.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $ordersCollection = $client->GADGETHUB->orders;

    // Fetch the orders where status is "delivered"
    $deliveredOrders = $ordersCollection->find(['status' => 'delivered']);

} catch (Exception $e) {
    die("Error connecting to MongoDB: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../css/notification.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/orderspage.css">
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
        <div class="notificationcontainer">
            <?php
            // Check if there are any delivered orders and display notifications
            foreach ($deliveredOrders as $order):
                // Check if 'cart_items' exists and is iterable
                $cartItems = isset($order['cart_items']) && is_iterable($order['cart_items']) ? iterator_to_array($order['cart_items']) : [];
                // Map product names from the cart_items array
                $products = array_map(fn($item) => $item['name'] ?? 'Unknown', $cartItems);
                // Get the first product's name to display in the notification
                $productName = $products[0] ?? 'Unknown Product';
            ?>
                <div class="notification1">
                    <img src="../img/shoppingbag.png" alt="">
                    <div class="notificationinfo">
                        <h3>Package delivered</h3>
                        <p>Your package (<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>) is delivered</p>
                        <img src="../img/cartproduct.png" alt="">
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

    <script>
    </script>
</body>
</html>
