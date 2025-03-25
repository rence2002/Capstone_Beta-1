$(document).ready(function() {
    // Quantity buttons
    $(document).on('click', '.qty-btn.plus', function() {
        const input = $(this).siblings('.quantity');
        let qty = parseInt(input.val()) || 0;
        input.val(qty + 1).trigger('change');
    });

    $(document).on('click', '.qty-btn.minus', function() {
        const input = $(this).siblings('.quantity');
        let qty = parseInt(input.val()) || 0;
        if (qty > 1) input.val(qty - 1).trigger('change');
    });

    // Quantity input change handler
    $(document).on('change', '.quantity', function() {
        let input = $(this);
        let qty = parseInt(input.val());
        if (isNaN(qty) || qty < 1) {
            qty = 1;
            input.val(qty);
        }
        updateCartItem(input);
    });

    // Remove button handler
    $(document).on('click', '.remove-btn', function() {
        if (!confirm('Are you sure you want to remove this item?')) return;
    
        const cartItem = $(this).closest('.cart-item');
        const cartId = cartItem.data('id');
    
        $.ajax({
            url: 'remove-from-cart.php', // Ensure filename matches your actual file
            method: 'POST',
            data: { cart_id: cartId },
            success: function(response) {
                if (response.success) {
                    // Remove item from DOM
                    cartItem.remove();
                    
                    // Check if cart is empty
                    if ($('.cart-item').length === 0) {
                        location.reload(); // Full reload for empty cart
                    } else {
                        updateCartTotals(); // Update totals without reload
                    }
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function(xhr) {
                alert('Server error: ' + xhr.statusText);
            }
        });
    });

    function updateCartItem(input) {
        const cartItem = input.closest('.cart-item');
        const cartId = cartItem.data('id');
        const price = parseFloat(cartItem.data('price')) || 0;
        const quantity = parseInt(input.val());
        const itemTotal = (price * quantity).toFixed(2);

        // Update UI
        cartItem.find('.item-total').text(
            " " + itemTotal.replace(/\B(?=(\d{3})+(?!\d))/g, ",")
        );

        // Update server
        $.ajax({
            url: 'update-cart.php',
            method: 'POST',
            data: { cart_id: cartId, quantity: quantity },
            success: updateCartTotals,
            error: function() {
                alert('Error updating quantity. Reloading...');
                location.reload();
            }
        });
    }

    function updateCartTotals() {
        let subtotal = 0;
        $('.item-total').each(function() {
            const amount = parseFloat($(this).text().replace(/[^0-9.]/g, '')) || 0;
            subtotal += amount;
        });

        const tax = subtotal * 0.08;
        const total = subtotal + tax;

        $('#subtotal').text('₱' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2 }));
        $('#tax').text('₱' + tax.toLocaleString('en-US', { minimumFractionDigits: 2 }));
        $('#total').text('₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2 }));
    }
});

function proceedToCheckout() {
    if ($('.cart-item').length === 0) {
        alert('Your cart is empty!');
        return;
    }

    if (!confirm('Confirm checkout?')) return;

    $.ajax({
        url: 'cart-rec.php', // Verify path to your PHP handler
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Order placed successfully!');
                location.reload(); // Refresh to show empty cart
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            try {
                const res = JSON.parse(xhr.responseText);
                alert('Server Error: ' + res.message);
            } catch (e) {
                alert('Network error. Please try again.');
            }
        }
    });
}

