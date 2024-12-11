<?php   
require '../../connection/connection.php'; 

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $ordersCollection = $client->GADGETHUB->orders;
    
    // Fetch the orders sorted by 'order_date' in descending order
    $orders = iterator_to_array($ordersCollection->find([], [
        'sort' => ['order_date' => -1]  // -1 for descending order
    ]));  
    
    // Handle status update
    if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
        $orderId = new MongoDB\BSON\ObjectId($_POST['order_id']);
        $newStatus = $_POST['status'];
        
        // Update the order status in the database
        $ordersCollection->updateOne(
            ['_id' => $orderId],
            ['$set' => ['status' => $newStatus]]
        );
        
        // Refresh the order list after updating
        header("Location: order-list.php");
        exit();
    }
} catch (Exception $e) {
    die("Error connecting to MongoDB: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order List</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/order-list.css">
    <link rel="stylesheet" href="../css/navbarside.css">
</head>
<body>

<div class="Container">
    <!-- Top Navigation -->
    <nav class="nav-top">
        <div class="menu-toggle" id="menu-toggle-button">
            <img src="../image/Icons/navbarside.png" alt="Menu">
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
        <img src="../image/Logo-Admin.png" alt="Logo">
        <ul>
            <li><a href="./dashboard.php">Dashboards</a></li>
            <li><a href="./update.php">Add Product</a></li>
            <li class="active"><a href="./order-list.php">Order List</a></li>
        </ul>
    </nav>

    <div class="content">
        <div class="Header-Container">
            <h1>Order Status</h1>
            <div class="directory">
                <p>Home  >  Order list</p>
                <div class="calendar">
                    <img src="../image/Icons/calendar-icon.png" alt="Calendar">
                    <p>Oct 11, 2023 - Nov 11, 2023</p>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="Order-Container">
                <div class="Recent-Orders">
                    <h1>Recent Purchase</h1>
                    <img src="../image/Icons/3Dots-icon.png" alt="Menu">
                </div>
                <span></span>
                <div class="Order-Labels">
                    <p>Product</p>
                    <p id="order" >Order ID</p>
                    <p >Date</p>
                    <p id="customer">Customer name</p>
                    <p  id="status">Status</p>
                    <p id="amount">Method of Payment</p>
                    <p>Action</p>  <!-- New column for action buttons -->
                </div>
                <span></span>
                <?php 
                foreach ($orders as $order): 
                    // Check if 'cart_items' exists and is iterable
                    $cartItems = isset($order['cart_items']) && is_iterable($order['cart_items']) ? iterator_to_array($order['cart_items']) : [];
                    // Map product names from the cart_items array
                    $products = array_map(fn($item) => $item['name'] ?? 'Unknown', $cartItems);

                    // Check if 'order_date' exists and is a valid BSONDateTime object
                    $orderDate = isset($order['order_date']) && $order['order_date'] instanceof MongoDB\BSON\UTCDateTime 
                                 ? $order['order_date']->toDateTime()->format('M jS, Y') 
                                 : 'Unknown Date';

                    // Check if 'total_price' exists
                    $totalPrice = isset($order['total_price']) ? '
₱' . number_format($order['total_price'], 2) : '
₱0';

                    // Check if 'status' exists
                    $status = isset($order['status']) ? ucfirst($order['status']) : 'Unknown';
                    
                    // Order ID for updating status
                    $orderId = (string)$order['_id']; 
                ?>
                    <div class="Order-Info">
                        <p>
                            <?php echo implode(', ', $products); ?>
                        </p>
                        <p>#<?php echo $orderId; ?></p>
                        <p><?php echo $orderDate; ?></p>
                        <p><?php echo $order['shipping_address'] ?? 'Unknown Address'; ?></p>
                        <p><?php echo $status; ?></p>
                        <p><?php echo $order['payment_method'] ?? 'Unknown Method'; ?></p>
                        <p><?php echo $totalPrice; ?></p>  <!-- Safely output the total amount -->
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                            <select name="status" required>
                                <option value="paid" <?php echo ($status == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                <option value="shipped" <?php echo ($status == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo ($status == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                            </select>
                            <button type="submit" name="update_status">Update Status</button>
                        </form>
                    </div>
                    <span></span>
                <?php endforeach; ?>
            </div>
        </div>
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
