<?php
require '../../connection/connection.php';
session_start(); // Ensure the user session is active

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($_SESSION['user_id'], $input['cart_id'], $input['action'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit();
    }

    $userId = $_SESSION['user_id'];
    $cartId = $input['cart_id'];
    $action = $input['action'];

    $client = new MongoDB\Client("mongodb://localhost:27017");
    $cartCollection = $client->GADGETHUB->carts;
    $productCollection = $client->GADGETHUB->products;

    // Find the cart item
    $cartItem = $cartCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($cartId), 'user_id' => $userId]);

    if (!$cartItem) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
        exit();
    }

    // Update the quantity
    $newQuantity = $cartItem['quantity'] + ($action === 'increase' ? 1 : -1);

    if ($newQuantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Quantity cannot be less than 1.']);
        exit();
    }

    // Update in the database
    $cartCollection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($cartId)],
        ['$set' => ['quantity' => $newQuantity]]
    );

    // Recalculate the total amount
    $cartItems = iterator_to_array($cartCollection->find(['user_id' => $userId]));
    $totalAmount = 0;

    foreach ($cartItems as $item) {
        $product = $productCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($item['product_id'])]);
        if ($product) {
            $totalAmount += $product['price']['amount'] * $item['quantity'];
        }
    }

    echo json_encode([
        'success' => true,
        'newQuantity' => $newQuantity,
        'newTotal' => $totalAmount,
    ]);
    exit();
}
?>
