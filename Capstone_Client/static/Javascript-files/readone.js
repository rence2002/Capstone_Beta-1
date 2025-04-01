document.addEventListener('DOMContentLoaded', function () {
    // Get the product ID from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('product_id');
    const stock = parseInt(document.getElementById("stock").textContent);

    // Initialize quantity to 1
    let quantity = 1;
    document.getElementById('quantity').textContent = quantity;

    // Plus button click event
    document.getElementById('plus-btn')?.addEventListener('click', () => {
        if (quantity < stock) {
            quantity++;
            document.getElementById('quantity').textContent = quantity;
        }
    });

    // Minus button click event
    document.getElementById('minus-btn')?.addEventListener('click', () => {
        if (quantity > 1) {
            quantity--;
            document.getElementById('quantity').textContent = quantity;
        }
    });

    // Function to send order data to the server
    async function sendOrder(orderType) {
        if (quantity === 0) {
            showModal("Error", "Please select a quantity.");
            return;
        }
        try {
            // Prepare data to send to the server
            const data = {
                productId: productId,
                quantity: quantity,
                orderType: orderType
            };
            console.log(`${orderType.charAt(0).toUpperCase() + orderType.slice(1)} clicked`);
            console.log('Data being sent:', data);

            // Send AJAX request
            const response = await fetch('gallery-readone-rec.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            // Parse the response
            const result = await response.json();
            console.log('Server response:', result);

            if (result.success) {
                // Show confirmation modal or notification
                showOrderConfirmation(orderType);
            } else {
                throw new Error(result.message || `Failed to process ${orderType}.`);
            }
        } catch (error) {
            showModal('Error', error.message || 'An unexpected error occurred.');
        }
    }

    // Add to Cart Button
    document.getElementById('add-to-cart')?.addEventListener('click', function () {
        sendOrder('cart');
    });

    // Pre-Order Button
    document.getElementById('pre-order')?.addEventListener('click', function () {
        sendOrder('pre_order');
    });

    // Buy Now Button
    document.getElementById('buy-now')?.addEventListener('click', function () {
        sendOrder('ready_made');
    });

    // Close modals when OK buttons are clicked
    document.getElementById('pre-order-ok-button')?.addEventListener('click', function () {
        document.getElementById('pre-order-modal').style.display = 'none';
    });

    document.getElementById('buy-now-ok-button')?.addEventListener('click', function () {
        document.getElementById('buy-now-modal').style.display = 'none';
    });

    // Close modals if the user clicks outside the modal content
    window.addEventListener('click', function (event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Function to show the confirmation modal or notification
    function showOrderConfirmation(orderType) {
        if (orderType === 'cart') {
            // Create a notification element for "Add to Cart"
            const notification = document.createElement('div');
            notification.classList.add('notification');
            notification.textContent = 'The product has been successfully added to your cart.';
            document.body.appendChild(notification);
            // Automatically remove the notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        } else {
            // Show modal for "Pre-Order" or "Buy Now"
            let modalId = '';
            let modalMessage = '';
            if (orderType === 'ready_made') {
                modalId = 'buy-now-modal';
                modalMessage = 'Your order has been submitted and is now under review.';
            } else if (orderType === 'pre_order') {
                modalId = 'pre-order-modal';
                modalMessage = 'Your pre-order request has been submitted and is now under review.';
            }
    
            const confirmationModal = document.getElementById(modalId);
            console.log(`Modal ID: ${modalId}, Modal Element:`, confirmationModal); // Debugging statement
    
            if (!confirmationModal) {
                console.error('Confirmation modal not found!');
                return;
            }
    
            // Update modal content dynamically
            confirmationModal.querySelector('p').textContent = modalMessage;
    
            // Display the modal
            confirmationModal.style.display = 'block';
            console.log(`Modal displayed: ${modalId}`); // Debugging statement
        }
    
    }

    // Modal functionality
    function showModal(title, message) {
        const modal = document.createElement('div');
        modal.classList.add('modal');
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>${title}</h2>
                <p>${message}</p>
            </div>
        `;
        document.body.appendChild(modal);

        // Close modal on clicking the close button or outside the modal content
        const closeModal = modal.querySelector('.close-modal');
        closeModal.addEventListener('click', () => {
            modal.remove();
        });
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
});
