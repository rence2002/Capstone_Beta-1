document.addEventListener('DOMContentLoaded', function() {
    const cartContainer = document.getElementById('cart-container');
    const approvalStatus = document.getElementById('approval-status');

    // Check for the approval status in localStorage
    const checkoutStatus = localStorage.getItem('approvalStatus'); // Update to 'approvalStatus'
    if (checkoutStatus) {
        approvalStatus.textContent = checkoutStatus; // Set the text content
        approvalStatus.classList.remove('hidden'); // Show the approval status
        localStorage.removeItem('approvalStatus'); // Optionally remove it after displaying
    }

    if (!cartContainer) {
        console.error("Cart container element not found");
        return;
    }

    // Load cart data from localStorage
    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];
    console.log("Loaded cart data from localStorage:", cartData);

    // Function to render cart items (assumed to be defined elsewhere)
    renderCartItems();

    // Add event listener for checkbox changes
    document.getElementById('cart-items').addEventListener('change', function(event) {
        if (event.target.classList.contains('item-checkbox')) {
            const index = parseInt(event.target.getAttribute('data-index'));
            cartData[index].checked = event.target.checked;
            localStorage.setItem('cartData', JSON.stringify(cartData));
            updateCartTotal(); // Function to update the total (assumed to be defined elsewhere)
        }
    });

    // Initialize first section as open
    const firstSection = document.querySelector('.section-content');
    if (firstSection) {
        firstSection.classList.add('active');
        firstSection.previousElementSibling.querySelector('.toggle-icon').style.transform = 'rotate(180deg)';
    }
});

function toggleSection(sectionId) {
    const content = document.getElementById(sectionId);
    const header = content.previousElementSibling;
    const icon = header.querySelector('.toggle-icon');
    
    // Close all other sections
    document.querySelectorAll('.section-content').forEach(section => {
        if (section.id !== sectionId && section.classList.contains('active')) {
            section.classList.remove('active');
            section.previousElementSibling.querySelector('.toggle-icon').style.transform = 'rotate(0deg)';
        }
    });
    
    // Toggle current section
    content.classList.toggle('active');
    icon.style.transform = content.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0deg)';
}