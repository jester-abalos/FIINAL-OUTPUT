
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

    <form method="POST" action="checkout.php">
        <div class="container">
            <div class="fieldnames">
                <span></span>
                <p id="productlabel">Product</p>
                <span></span>
                <p id="pricelabel">Unit Price</p>
                <p id="quantitylabel">Quantity</p>
                <p id="actionlabel">Action</p>
            </div>

            <?php if (!empty($cartItems)): ?>
                <?php foreach ($cartItems as $item): ?>
                    <?php
                    // Ensure ObjectId is converted to string
                    $productId = (string)$item['product_id'];
                    $productName = htmlspecialchars($item['name'] ?? 'Unknown Product');
                    $productPrice = (float)($item['price'] ?? 0);
                    $quantity = (int)($item['quantity'] ?? 1);
                    $totalPrice += $productPrice * $quantity;
                    ?>
                    <div class="cart-item">
                        <input type="checkbox" name="selected_products[]" value="<?php echo $productId; ?>" class="product-checkbox">
                        <span></span>
                        <p class="product-name"><?php echo $productName; ?></p>
                        <span></span>
                        <p class="product-price">₱<?php echo number_format($productPrice, 2); ?></p>
                        <p class="product-quantity"><?php echo $quantity; ?></p>
                        <select name="select_option[<?php echo $productId; ?>]" class="select-option">
                        </select>
                        <button type= "button"  name="add_to_cart" class="remove-item" data-product-id="<?php echo $productId; ?>">Remove</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>

            <div class="bottomoptions">
                <input type="checkbox" id="selectall" name="selectall" value="selectall" onclick="toggleSelectAll(this)">
                <p id="selectalllabel">Select All</p>
                <div id="selectedItemsContainer"></div>
                <p id="totalitem">Total: ₱<span id="totalvalue"><?php echo number_format($totalPrice, 2); ?></span></p>
                <button type="submit" id="add_to_cart" name="add_to_cart" <?php echo empty($cartItems) ? 'disabled' : ''; ?>>Proceed to Check Out</button>
            </div>
        </div>
    </form>

    <script>
        const selectAllCheckbox = document.getElementById('selectall');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const selectedItemsContainer = document.getElementById('selectedItemsContainer');

        selectAllCheckbox.addEventListener('change', () => {
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateSelectedItems();
        });

        function updateSelectedItems() {
            let selectedProductIds = [];
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedProductIds.push(checkbox.value);
                }
            });
            updateTotalPrice(selectedProductIds);
        }

        function updateTotalPrice(selectedProductIds) {
            let totalPrice = 0;
            selectedProductIds.forEach(productId => {
                const cartItem = document.querySelector(`.cart-item input[value="${productId}"]`).closest('.cart-item');
                const price = parseFloat(cartItem.querySelector('.product-price').textContent.replace('₱', ''));
                const quantity = parseInt(cartItem.querySelector('.product-quantity').textContent);
                totalPrice += price * quantity;
            });
            document.getElementById('totalvalue').textContent = totalPrice.toFixed(2);
        }
    </script>
</body>
</html>
