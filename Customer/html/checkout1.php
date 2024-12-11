<?php
require '../../connection/connection.php';
session_start();

use MongoDB\Client;

// Function to fetch cart items for a user
function getCartItems($userId) {
    try {
        $client = new Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('GADGETHUB');
        $cartCollection = $db->selectCollection('carts');
        return iterator_to_array($cartCollection->find(['user_id' => $userId]));
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

// Function to fetch user details from MongoDB
function getUserDetails($userId) {
    try {
        $client = new Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('GADGETHUB');
        $usersCollection = $db->selectCollection('users');
        $user = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);
        return $user ? $user : [];
    } catch (Exception $e) {
        echo "Error fetching user details: " . $e->getMessage();
        return [];
    }
}

// Check if the user is logged in
$userLoggedIn = isset($_SESSION['user_id']);
$userId = $userLoggedIn ? $_SESSION['user_id'] : null;

// Fetch user details if logged in
$user = [];
if ($userLoggedIn && $userId) {
    $user = getUserDetails($userId);
    $cartItems = getCartItems($userId);
} else {
    $cartItems = $_SESSION['cart'] ?? [];
}

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $shippingAddress = $_POST['shipping_address'];
    $paymentMethod = $_POST['payment_method'];
    $status = "To Pay"; // Default status

    if (!in_array($paymentMethod, ['paypal', 'cod'])) {
        die('Invalid payment method.');
    }

    // Insert the order data into the orders collection
    $client = new Client("mongodb://localhost:27017");
    $ordersCollection = $client->GADGETHUB->orders;
    $orderData = [
        'user_id' => $userId,
        'cart_items' => $cartItems,  // Store all cart items
        'total_price' => $total + 85, // Add shipping cost
        'shipping_address' => $shippingAddress,
        'payment_method' => $paymentMethod,
        'order_date' => new MongoDB\BSON\UTCDateTime(),
        'status' => $status,
    ];

    // Insert the order into the database
    $insertResult = $ordersCollection->insertOne($orderData);
    $orderId = (string)$insertResult->getInsertedId();

    // Clear the cart after order confirmation
    if ($userLoggedIn) {
        $cartsCollection = $client->GADGETHUB->carts;
        $cartsCollection->deleteMany(['user_id' => $userId]);
    } else {
        unset($_SESSION['cart']);
    }

    // Redirect to the order confirmation page
    header("Location: ordercondev.php?order_id=$orderId");
    exit();
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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../html/navbar.php'; ?>

    <div class="container">
        <h2>Checkout</h2>
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
            <p><strong>Total Price: ₱<?php echo htmlspecialchars($total + 85); ?></strong></p>
        </div>

        <form method="POST" action="checkout.php" id="profileForm">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
            <br>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <br>

            <label for="address">Shipping Address:</label>
            <input type="text" id="shipping_address" name="shipping_address" value="<?php echo htmlspecialchars($user['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <br>

            <div class="section payment-section">
                <h3>Payment Method</h3>
                <label>
                    <input type="radio" name="payment_method" value="cod" id="payment-cod" required>
                    Cash on Delivery
                </label>
                <label>
                    <input type="radio" name="payment_method" value="paypal" id="payment-paypal" required>
                    PayPal
                </label>
            </div>
            <div id="paypal-button-container" style="display: none;"></div>
            <button type="submit" name="confirm_order" class="btn btn-primary">Proceed with Order</button>
        </form>
    </div>

    <script src="https://www.paypal.com/sdk/js?client-id=ASHmrHe3Otqtu4COLTbV4qGmOoTNOKMIsup17wcFFsa_1qK9k88xq5K0Ycm96jjpEhOIy3Rp_DTT4b7R&currency=USD"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const paypalRadioButton = document.getElementById("payment-paypal");
            const codRadioButton = document.getElementById("payment-cod");
            const paypalButtonContainer = document.getElementById("paypal-button-container");
            let paymentStatus = "To Pay"; 

            const totalAmount = <?php echo json_encode($total + 85); ?>;

            function togglePayPalButton() {
                if (paypalRadioButton.checked) {
                    paypalButtonContainer.style.display = "block";
                } else {
                    paypalButtonContainer.style.display = "none";
                    paymentStatus = "To Pay"; 
                }
            }

            paypalRadioButton.addEventListener("change", togglePayPalButton);
            codRadioButton.addEventListener("change", togglePayPalButton);

            paypal.Buttons({
                createOrder: function (data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: { value: totalAmount.toFixed(2) }
                        }]
                    });
                },
                onApprove: function (data, actions) {
                    return actions.order.capture().then(function (details) {
                        paymentStatus = "To Ship"; 
                        alert(`Transaction completed by ${details.payer.name.given_name}.`);
                    });
                },
                onError: function (err) {
                    console.error(err);
                    alert('Something went wrong with PayPal.');
                }
            }).render('#paypal-button-container');
        });
    </script>

</body>
</html>
