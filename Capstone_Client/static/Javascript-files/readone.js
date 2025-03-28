document.addEventListener('DOMContentLoaded', function () {
    // Get the product ID from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('product_id');

    // Initialize quantity to 1
    let quantity = 1;
    document.getElementById('quantity').textContent = quantity;

    // Plus button click event
    document.getElementById('plus-btn').addEventListener('click', () => {
        quantity++;
        document.getElementById('quantity').textContent = quantity;
    });

    // Minus button click event
    document.getElementById('minus-btn').addEventListener('click', () => {
        if (quantity > 1) {
            quantity--;
            document.getElementById('quantity').textContent = quantity;
        }
    });

    // Function to send order data to the server
    async function sendOrder(orderType) {
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

            // Log raw response for debugging
            const rawResponse = await response.text();
            console.log('Raw server response:', rawResponse);

            const result = JSON.parse(rawResponse);
            if (result.success) {
                showOrderConfirmationModal(); // Show confirmation modal
            } else {
                throw new Error(result.message || `Failed to process ${orderType}.`);
            }
        } catch (error) {
            showModal('Error', error.message || 'An unexpected error occurred.');
        }
    }

    // Buy Now Button Handler
    document.getElementById('buy-now')?.addEventListener('click', () => {
        if (quantity > 0) {
            sendOrder('ready_made');
        } else {
            showModal('Error', 'Please select a valid quantity.');
        }
    });

    // Add to Cart Button Handler
    document.getElementById('add-to-cart')?.addEventListener('click', () => {
        if (quantity > 0) {
            sendOrder('cart');
        } else {
            showModal('Error', 'Please select a valid quantity.');
        }
    });

    // Pre-order Button Handler
    document.getElementById('pre-order')?.addEventListener('click', () => {
        if (quantity > 0) {
            sendOrder('pre_order');
        } else {
            showModal('Error', 'Please select a valid quantity.');
        }
    });

    // Image slider functionality remains unchanged
    const sliderContainer = document.querySelector('.image-slider');
    const sliderImages = document.querySelectorAll('.product-image');
    const prevButton = document.querySelector('.prev-btn');
    const nextButton = document.querySelector('.next-btn');
    let currentIndex = 0;

    function showImage(index) {
        sliderImages.forEach(img => img.classList.remove('active'));
        sliderImages[index].classList.add('active');
    }

    function prevImage() {
        currentIndex = (currentIndex - 1 + sliderImages.length) % sliderImages.length;
        showImage(currentIndex);
    }

    function nextImage() {
        currentIndex = (currentIndex + 1) % sliderImages.length;
        showImage(currentIndex);
    }

    prevButton.addEventListener('click', prevImage);
    nextButton.addEventListener('click', nextImage);

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

    // Function to show the confirmation modal
    function showOrderConfirmationModal() {
        const confirmationModal = document.getElementById('order-confirmation-modal');
        if (!confirmationModal) {
            console.error('Confirmation modal not found!');
            return;
        }
        confirmationModal.style.display = 'block';

        // Handle OK button in confirmation modal
        document.getElementById('confirm-ok-button').onclick = () => {
            confirmationModal.style.display = 'none'; // Hide confirmation modal

            // Reload the current page instead of redirecting to gallery.php
            window.location.reload(); // Stay on gallery-readone.php
        };
    }
});