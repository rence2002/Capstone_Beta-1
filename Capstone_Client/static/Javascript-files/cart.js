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
                }
            },
            error: function(xhr) {
                console.error('Server error:', xhr.statusText); // Log error instead of alert
                showModal('Error', 'Server error. Please try again.', 'readone');
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
                showModal('Error', 'Error updating quantity. Reloading...', 'readone');
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

    // Modal Functionality
    function showModal(title, message, type = 'default', callback = null) {
        const modal = document.createElement('div');
        modal.classList.add('modal');

        let modalContentHTML = '';

        if (type === 'readone') {
            modalContentHTML = `
                <div class="modal-content readone-modal">
                    <button class="exit-button close-modal">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="modal-body">
                        <h2 class="modal-title">${title}</h2>
                        <p class="modal-message">${message}</p>
                        <div class="modal-buttons">
                            <button class="modal-confirm-button">Confirm</button>
                        </div>
                    </div>
                </div>
            `;
        } else {
            modalContentHTML = `
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h2>${title}</h2>
                    <p>${message}</p>
                </div>
            `;
        }

        modal.innerHTML = modalContentHTML;
        document.body.appendChild(modal);
        modal.style.display = "block";

        const closeButtons = modal.querySelectorAll('.close-modal');
        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                modal.remove();
            });
        });

        // Handle confirm button click
        const confirmButton = modal.querySelector('.modal-confirm-button');
        if (confirmButton) {
            confirmButton.addEventListener('click', () => {
                if (callback) {
                    callback();
                }
                modal.remove();
            });
        }

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    window.showModal = showModal;

    window.proceedToCheckout = function() {
        console.log("proceedToCheckout() called"); // Debugging log

        if ($('.cart-item').length === 0) {
            console.log("Cart is empty"); // Debugging log
            showModal('Empty Cart', 'Your cart is empty!', 'readone');
            return;
        }

        console.log("Cart is not empty, proceeding with checkout"); // Debugging log

        // Show the confirmation modal
        showModal(
            'Confirm Checkout',
            'Are you sure you want to proceed with the checkout?',
            'readone',
            function() {
                // This function will be called if the user clicks "Confirm"
                console.log("User confirmed checkout");
                // Proceed with the AJAX request
                $.ajax({
                    url: 'cart-rec.php', // Verify path to your PHP handler
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        console.log("AJAX success:", response); // Debugging log
                        if (response.success) {
                            showModal('Success', 'Order placed successfully!', 'readone');
                            location.reload(); // Refresh to show empty cart
                        } else {
                            showModal('Error', 'Error: ' + response.message, 'readone');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", xhr, status, error); // Detailed error log
                        try {
                            const res = JSON.parse(xhr.responseText);
                            showModal('Error', 'Server Error: ' + res.message, 'readone');
                        } catch (e) {
                            showModal('Error', 'Network error. Please try again.', 'readone');
                        }
                    }
                });
            }
        );
    };
});
