// Update cart quantity and price dynamically
function updateQuantity(cartId, action) {
    fetch('updateQuantity.php', {
        method: 'POST',
        body: JSON.stringify({ cartId: cartId, action: action }),
        headers: {
            'Content-Type': 'application/json',
        },
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the quantity and subtotal in the DOM
                const quantityElement = document.querySelector(`#quantity-${cartId}`);
                const subtotalElement = document.querySelector(`#subtotal-${cartId}`);

                quantityElement.textContent = data.newQuantity;
                subtotalElement.textContent = `₱${data.newSubtotal}`;

                // Optionally, update the total amount
                calculateTotal();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Calculate total price dynamically
function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal').forEach(subtotalElement => {
        const value = parseFloat(subtotalElement.textContent.replace('₱', '').replace(',', ''));
        total += isNaN(value) ? 0 : value;
    });

    document.getElementById('totalitem').textContent = `Total: ₱${total.toFixed(2)}`;
}
